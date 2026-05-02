import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../models/pagination.dart';
import '../../models/product.dart';
import '../../providers/auth_and_api.dart';
import '../../providers/cart_notifier.dart';
import '../../shared/widgets/product_card.dart';

class SearchScreen extends ConsumerStatefulWidget {
  const SearchScreen({super.key, this.initialQuery});

  final String? initialQuery;

  @override
  ConsumerState<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends ConsumerState<SearchScreen> {
  final _controller = TextEditingController();
  final _scrollController = ScrollController();

  int _page = 1;
  String _q = '';
  List<Product> _items = [];
  int _lastPage = 1;
  bool _loading = false;
  bool _loadingMore = false;
  String? _error;

  static const _pageSize = 24;
  static const _loadMoreExtent = 360.0;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _q = widget.initialQuery?.trim() ?? '';
    _controller.text = _q;
    _reload();
  }

  @override
  void dispose() {
    _scrollController.removeListener(_onScroll);
    _scrollController.dispose();
    _controller.dispose();
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

  Future<void> _reload() async {
    setState(() {
      _page = 1;
      _loading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiClientProvider);
      final query = <String, dynamic>{
        'page': 1,
        'limit': _pageSize,
      };
      if (_q.isNotEmpty) query['q'] = _q;
      final data = await api.get('/products', query: query);
      if (data is! Map) throw Exception('Bad response');
      final page = PaginatedProducts.fromJson(Map<String, dynamic>.from(data));
      if (mounted) {
        setState(() {
          _items = List<Product>.from(page.items);
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

  Future<void> _loadMore() async {
    if (_loadingMore || _loading || _page >= _lastPage || _error != null) {
      return;
    }
    final nextPage = _page + 1;
    setState(() => _loadingMore = true);
    try {
      final api = ref.read(apiClientProvider);
      final query = <String, dynamic>{
        'page': nextPage,
        'limit': _pageSize,
      };
      if (_q.isNotEmpty) query['q'] = _q;
      final data = await api.get('/products', query: query);
      if (data is! Map) throw Exception('Bad response');
      final page =
          PaginatedProducts.fromJson(Map<String, dynamic>.from(data));
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

  void _submit() {
    setState(() => _q = _controller.text.trim());
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: TextField(
          controller: _controller,
          decoration: const InputDecoration(
            hintText: 'Search products',
            border: InputBorder.none,
          ),
          onSubmitted: (_) => _submit(),
        ),
        actions: [
          IconButton(icon: const Icon(Icons.search), onPressed: _submit),
        ],
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Align(
              alignment: Alignment.centerLeft,
              child: Text(
                _q.isEmpty ? 'All products' : 'Results for “$_q”',
                style: Theme.of(context).textTheme.titleMedium,
              ),
            ),
          ),
          Expanded(
            child: _loading && _items.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : _error != null && _items.isEmpty
                    ? Center(child: Text(_error!))
                    : _items.isEmpty
                        ? const Center(child: Text('No products found'))
                        : RefreshIndicator(
                            onRefresh: _reload,
                            child: CustomScrollView(
                              controller: _scrollController,
                              physics: const AlwaysScrollableScrollPhysics(),
                              slivers: [
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
