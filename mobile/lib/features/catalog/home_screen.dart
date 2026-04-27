import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../models/category.dart';
import '../../models/product.dart';
import '../../models/pagination.dart';
import '../../providers/auth_and_api.dart';
import '../../providers/cart_notifier.dart';
import '../../shared/widgets/product_card.dart';

const _heroSlides = [
  _HeroSlide(
    kicker: 'Featured',
    title: 'Everyday Essentials',
    desc: 'Top brands across beauty, home and tech.',
    art:
        'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=60',
  ),
  _HeroSlide(
    kicker: 'Smart Home',
    title: 'Upgrade Your Living Room',
    desc: 'Smart TVs, speakers and more.',
    art:
        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1200&q=60',
  ),
  _HeroSlide(
    kicker: 'Beauty & Care',
    title: 'Glow Up Essentials',
    desc: 'Curated skincare and wellness.',
    art:
        'https://images.unsplash.com/photo-1522335789203-aaa6f4d2b46d?auto=format&fit=crop&w=1200&q=60',
  ),
];

class _HeroSlide {
  const _HeroSlide({
    required this.kicker,
    required this.title,
    required this.desc,
    required this.art,
  });
  final String kicker;
  final String title;
  final String desc;
  final String art;
}

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  List<Category>? _categories;
  final Map<int, List<Product>> _strips = {};
  String? _error;
  bool _loading = true;

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
      final raw = await api.get('/categories');
      if (raw is! List) throw Exception('Bad categories response');
      final cats = raw
          .map((e) => Category.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
      final futures = cats.map((c) async {
        final data = await api.get(
          '/products',
          query: {'category_id': c.id, 'limit': 12, 'page': 1},
        );
        if (data is! Map) return MapEntry(c.id, <Product>[]);
        final page = PaginatedProducts.fromJson(Map<String, dynamic>.from(data));
        return MapEntry(c.id, page.items);
      });
      final entries = await Future.wait(futures);
      final strips = <int, List<Product>>{};
      for (final e in entries) {
        strips[e.key] = e.value;
      }
      if (mounted) {
        setState(() {
          _categories = cats;
          _strips
            ..clear()
            ..addAll(strips);
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
        title: const Text('Store'),
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () => context.go('/search'),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    children: [
                      Padding(
                        padding: const EdgeInsets.all(24),
                        child: Text(_error!, textAlign: TextAlign.center),
                      ),
                    ],
                  )
                : ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    children: [
                      SizedBox(
                        height: 200,
                        child: PageView.builder(
                          itemCount: _heroSlides.length,
                          itemBuilder: (context, i) {
                            final s = _heroSlides[i];
                            return Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(16),
                                child: Stack(
                                  fit: StackFit.expand,
                                  children: [
                                    Image.network(
                                      s.art,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) =>
                                          const ColoredBox(color: Color(0xFFEEEEEE)),
                                    ),
                                    Container(
                                      decoration: BoxDecoration(
                                        gradient: LinearGradient(
                                          begin: Alignment.centerLeft,
                                          end: Alignment.centerRight,
                                          colors: [
                                            Colors.black.withValues(alpha: 0.65),
                                            Colors.transparent,
                                          ],
                                        ),
                                      ),
                                    ),
                                    Padding(
                                      padding: const EdgeInsets.all(20),
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Text(
                                            s.kicker,
                                            style: const TextStyle(
                                              color: Colors.white70,
                                              fontSize: 12,
                                            ),
                                          ),
                                          Text(
                                            s.title,
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 22,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                          const SizedBox(height: 8),
                                          FilledButton(
                                            onPressed: () => context.go('/search'),
                                            child: const Text('Shop now'),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                      const SizedBox(height: 12),
                      if (_categories != null && _categories!.isNotEmpty)
                        SizedBox(
                          height: 44,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            itemCount: _categories!.length,
                            separatorBuilder: (_, __) =>
                                const SizedBox(width: 8),
                            itemBuilder: (context, i) {
                              final c = _categories![i];
                              return ActionChip(
                                label: Text(c.name),
                                onPressed: () =>
                                    context.push('/category/${c.id}'),
                              );
                            },
                          ),
                        ),
                      const SizedBox(height: 16),
                      ...(_categories ?? []).map((c) {
                        final items = _strips[c.id] ?? [];
                        if (items.isEmpty) return const SizedBox.shrink();
                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      c.name,
                                      style: Theme.of(context)
                                          .textTheme
                                          .titleMedium
                                          ?.copyWith(fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                  TextButton(
                                    onPressed: () =>
                                        context.push('/category/${c.id}'),
                                    child: const Text('See all'),
                                  ),
                                ],
                              ),
                            ),
                            SizedBox(
                              height: 280,
                              child: ListView.separated(
                                scrollDirection: Axis.horizontal,
                                padding:
                                    const EdgeInsets.symmetric(horizontal: 12),
                                itemCount: items.length,
                                separatorBuilder: (_, __) =>
                                    const SizedBox(width: 12),
                                itemBuilder: (context, i) {
                                  final p = items[i];
                                  return SizedBox(
                                    width: 160,
                                    child: ProductCard(
                                      product: p,
                                      compact: true,
                                      onAdd: () => ref
                                          .read(cartNotifierProvider.notifier)
                                          .add(p, 1),
                                    ),
                                  );
                                },
                              ),
                            ),
                            const SizedBox(height: 24),
                          ],
                        );
                      }),
                    ],
                  ),
      ),
    );
  }
}
