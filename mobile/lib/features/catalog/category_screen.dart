import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../models/category.dart';
import '../../models/pagination.dart';
import '../../models/product.dart';
import '../../providers/auth_and_api.dart';
import '../../providers/cart_notifier.dart';
import '../../shared/widgets/product_card.dart';

class CategoryScreen extends ConsumerStatefulWidget {
  const CategoryScreen({super.key, required this.categoryId});

  final int categoryId;

  @override
  ConsumerState<CategoryScreen> createState() => _CategoryScreenState();
}

class _CategoryScreenState extends ConsumerState<CategoryScreen> {
  Category? _category;
  int _page = 1;
  List<Product> _items = [];
  int _lastPage = 1;
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
      final rawCats = await api.get('/categories');
      if (rawCats is! List) throw Exception('Bad categories');
      final cats = rawCats
          .map((e) => Category.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      Category? cat;
      for (final c in cats) {
        if (c.id == widget.categoryId) {
          cat = c;
          break;
        }
      }
      final data = await api.get(
        '/products',
        query: {
          'category_id': widget.categoryId,
          'page': _page,
          'limit': 24,
        },
      );
      if (data is! Map) throw Exception('Bad products');
      final page = PaginatedProducts.fromJson(Map<String, dynamic>.from(data));
      if (mounted) {
        setState(() {
          _category = cat;
          _items = page.items;
          _lastPage = page.lastPage;
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

  Future<void> _fetchPage() async {
    setState(() => _loading = true);
    try {
      final api = ref.read(apiClientProvider);
      final data = await api.get(
        '/products',
        query: {
          'category_id': widget.categoryId,
          'page': _page,
          'limit': 24,
        },
      );
      if (data is! Map) throw Exception('Bad products');
      final page = PaginatedProducts.fromJson(Map<String, dynamic>.from(data));
      if (mounted) {
        setState(() {
          _items = page.items;
          _lastPage = page.lastPage;
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_category?.name ?? 'Category'),
      ),
      body: _loading && _items.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : Column(
                  children: [
                    if (_category?.description != null &&
                        _category!.description!.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.all(12),
                        child: Text(_category!.description!),
                      ),
                    Expanded(
                      child: _items.isEmpty
                          ? const Center(child: Text('No products'))
                          : RefreshIndicator(
                              onRefresh: () async {
                                _page = 1;
                                await _fetchPage();
                              },
                              child: GridView.builder(
                                padding: const EdgeInsets.all(12),
                                gridDelegate:
                                    const SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: 2,
                                  childAspectRatio: 0.58,
                                  crossAxisSpacing: 12,
                                  mainAxisSpacing: 12,
                                ),
                                itemCount: _items.length,
                                itemBuilder: (context, i) {
                                  final p = _items[i];
                                  return ProductCard(
                                    product: p,
                                    onAdd: () => ref
                                        .read(cartNotifierProvider.notifier)
                                        .add(p, 1),
                                  );
                                },
                              ),
                            ),
                    ),
                    if (_lastPage > 1)
                      Padding(
                        padding: const EdgeInsets.all(8),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            FilledButton.tonal(
                              onPressed: _page > 1 && !_loading
                                  ? () {
                                      setState(() => _page--);
                                      _fetchPage();
                                    }
                                  : null,
                              child: const Text('Previous'),
                            ),
                            Padding(
                              padding:
                                  const EdgeInsets.symmetric(horizontal: 16),
                              child: Text('Page $_page / $_lastPage'),
                            ),
                            FilledButton.tonal(
                              onPressed: _page < _lastPage && !_loading
                                  ? () {
                                      setState(() => _page++);
                                      _fetchPage();
                                    }
                                  : null,
                              child: const Text('Next'),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
    );
  }
}
