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
  final _scrollController = ScrollController();

  int _page = 1;
  List<Product> _items = [];
  int _lastPage = 1;
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  static const _pageSize = 24;
  static const _loadMoreExtent = 360.0;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _load();
  }

  @override
  void dispose() {
    _scrollController.removeListener(_onScroll);
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (!_scrollController.hasClients) return;
    if (_loadingMore || _loading || _page >= _lastPage || _error != null) {
      return;
    }
    final pos = _scrollController.position;
    if (pos.pixels >= pos.maxScrollExtent - _loadMoreExtent) {
      _loadMore();
    }
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
      _page = 1;
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
          'page': 1,
          'limit': _pageSize,
        },
      );
      if (data is! Map) throw Exception('Bad products');
      final page = PaginatedProducts.fromJson(Map<String, dynamic>.from(data));
      if (mounted) {
        setState(() {
          _category = cat;
          _items = List<Product>.from(page.items);
          _lastPage = page.lastPage;
          _page = 1;
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

  Future<void> _loadMore() async {
    if (_loadingMore || _loading || _page >= _lastPage || _error != null) {
      return;
    }
    final nextPage = _page + 1;
    setState(() => _loadingMore = true);
    try {
      final api = ref.read(apiClientProvider);
      final data = await api.get(
        '/products',
        query: {
          'category_id': widget.categoryId,
          'page': nextPage,
          'limit': _pageSize,
        },
      );
      if (data is! Map) throw Exception('Bad products');
      final page = PaginatedProducts.fromJson(Map<String, dynamic>.from(data));
      if (mounted) {
        setState(() {
          _items.addAll(page.items);
          _page = nextPage;
          _lastPage = page.lastPage;
          _loadingMore = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _loadingMore = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final desc =
        (_category?.description != null && _category!.description!.isNotEmpty)
            ? _category!.description!
            : null;

    return Scaffold(
      appBar: AppBar(
        title: Text(_category?.name ?? 'Category'),
      ),
      body: _loading && _items.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : _error != null && _items.isEmpty
              ? Center(child: Text(_error!))
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Expanded(
                      child: _items.isEmpty
                          ? const Center(child: Text('No products'))
                          : RefreshIndicator(
                              onRefresh: _load,
                              child: CustomScrollView(
                                controller: _scrollController,
                                physics: const AlwaysScrollableScrollPhysics(),
                                slivers: [
                                  if (desc != null)
                                    SliverToBoxAdapter(
                                      child: Padding(
                                        padding: const EdgeInsets.all(12),
                                        child: Text(desc),
                                      ),
                                    ),
                                  SliverPadding(
                                    padding: const EdgeInsets.all(12),
                                    sliver: SliverGrid(
                                      gridDelegate:
                                          const SliverGridDelegateWithFixedCrossAxisCount(
                                        crossAxisCount: 2,
                                        childAspectRatio: 0.58,
                                        crossAxisSpacing: 12,
                                        mainAxisSpacing: 12,
                                      ),
                                      delegate: SliverChildBuilderDelegate(
                                        (context, i) {
                                          final p = _items[i];
                                          return ProductCard(
                                            product: p,
                                            onAdd: () => ref
                                                .read(cartNotifierProvider
                                                    .notifier)
                                                .add(p, 1),
                                          );
                                        },
                                        childCount: _items.length,
                                      ),
                                    ),
                                  ),
                                  if (_loadingMore)
                                    const SliverToBoxAdapter(
                                      child: Padding(
                                        padding: EdgeInsets.only(
                                          top: 8,
                                          bottom: 24,
                                        ),
                                        child: Center(
                                          child: CircularProgressIndicator(),
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            ),
                    ),
                  ],
                ),
    );
  }
}
