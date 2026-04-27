import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/format/money.dart';
import '../../models/pagination.dart';
import '../../models/product.dart';
import '../../providers/auth_and_api.dart';
import '../../providers/cart_notifier.dart';
import '../../shared/widgets/product_card.dart';

class ProductDetailScreen extends ConsumerStatefulWidget {
  const ProductDetailScreen({super.key, required this.productId});

  final int productId;

  @override
  ConsumerState<ProductDetailScreen> createState() =>
      _ProductDetailScreenState();
}

class _ProductDetailScreenState extends ConsumerState<ProductDetailScreen> {
  Product? _product;
  List<Product> _related = [];
  int _qty = 1;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiClientProvider);
      final raw = await api.get('/products/${widget.productId}');
      if (raw is! Map) throw Exception('Not found');
      final product = Product.fromJson(Map<String, dynamic>.from(raw));
      List<Product> related = [];
      if (product.categoryId > 0) {
        final listData = await api.get(
          '/products',
          query: {
            'category_id': product.categoryId,
            'limit': 12,
            'page': 1,
          },
        );
        if (listData is Map) {
          final page =
              PaginatedProducts.fromJson(Map<String, dynamic>.from(listData));
          related = page.items.where((p) => p.id != product.id).toList();
        }
      }
      if (mounted) {
        setState(() {
          _product = product;
          _related = related;
          _qty = product.stock > 0 ? 1 : 0;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _loading = false;
        });
      }
    }
  }

  List<String> _galleryUrls(Product p) {
    final urls = <String>[];
    if (p.images.isNotEmpty) {
      for (final img in p.images) {
        if (img.url.isNotEmpty) urls.add(img.url);
      }
    } else if (p.imageUrl != null && p.imageUrl!.isNotEmpty) {
      urls.add(p.imageUrl!);
    }
    return urls;
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    if (_error != null || _product == null) {
      return Scaffold(
        appBar: AppBar(),
        body: Center(child: Text(_error ?? 'Product unavailable')),
      );
    }
    final p = _product!;
    final urls = _galleryUrls(p);
    final pct = discountPercent(p.price, p.discountPrice);

    return Scaffold(
      appBar: AppBar(title: Text(p.name, maxLines: 1, overflow: TextOverflow.ellipsis)),
      body: ListView(
        children: [
          SizedBox(
            height: 280,
            child: urls.isEmpty
                ? const ColoredBox(
                    color: Color(0xFFEEEEEE),
                    child: Center(child: Icon(Icons.image_not_supported_outlined)),
                  )
                : PageView.builder(
                    itemCount: urls.length,
                    itemBuilder: (context, i) {
                      return CachedNetworkImage(
                        imageUrl: urls[i],
                        fit: BoxFit.contain,
                        placeholder: (_, __) =>
                            const ColoredBox(color: Color(0xFFEEEEEE)),
                      );
                    },
                  ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (pct != null)
                  Chip(
                    label: Text('-$pct% off'),
                    visualDensity: VisualDensity.compact,
                  ),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      formatMoney(effectiveUnitPrice(p.price, p.discountPrice)),
                      style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: Theme.of(context).colorScheme.primary,
                          ),
                    ),
                    if (p.discountPrice != null && p.discountPrice! > 0) ...[
                      const SizedBox(width: 8),
                      Text(
                        formatMoney(p.price),
                        style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                              decoration: TextDecoration.lineThrough,
                              color: Theme.of(context).colorScheme.onSurfaceVariant,
                            ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  p.stock > 0
                      ? '${p.stock} in stock'
                      : 'Out of stock',
                  style: TextStyle(
                    color: p.stock > 0 ? Colors.green.shade800 : Theme.of(context).colorScheme.error,
                  ),
                ),
                if (p.description != null && p.description!.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  Text(p.description!),
                ],
                const SizedBox(height: 24),
                if (p.stock > 0) ...[
                  Row(
                    children: [
                      const Text('Quantity'),
                      const SizedBox(width: 16),
                      IconButton(
                        onPressed: _qty > 1
                            ? () => setState(() => _qty--)
                            : null,
                        icon: const Icon(Icons.remove_circle_outline),
                      ),
                      Text('$_qty'),
                      IconButton(
                        onPressed: _qty < p.stock && _qty < 99
                            ? () => setState(() => _qty++)
                            : null,
                        icon: const Icon(Icons.add_circle_outline),
                      ),
                    ],
                  ),
                  FilledButton(
                    onPressed: () {
                      ref.read(cartNotifierProvider.notifier).add(p, _qty);
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Added to cart')),
                      );
                    },
                    child: const Text('Add to cart'),
                  ),
                ],
              ],
            ),
          ),
          if (_related.isNotEmpty) ...[
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
              child: Text(
                'More in this category',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
            ),
            SizedBox(
              height: 280,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: _related.length,
                separatorBuilder: (_, __) => const SizedBox(width: 12),
                itemBuilder: (context, i) {
                  final r = _related[i];
                  return SizedBox(
                    width: 160,
                    child: ProductCard(
                      product: r,
                      compact: true,
                      onAdd: () =>
                          ref.read(cartNotifierProvider.notifier).add(r, 1),
                    ),
                  );
                },
              ),
            ),
          ],
        ],
      ),
    );
  }
}
