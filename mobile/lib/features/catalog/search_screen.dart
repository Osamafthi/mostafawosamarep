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
  int _page = 1;
  String _q = '';
  List<Product> _items = [];
  int _lastPage = 1;
  bool _loading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _q = widget.initialQuery?.trim() ?? '';
    _controller.text = _q;
    _fetch(reset: true);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _fetch({bool reset = false}) async {
    if (reset) _page = 1;
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiClientProvider);
      final query = <String, dynamic>{'page': _page, 'limit': 24};
      if (_q.isNotEmpty) query['q'] = _q;
      final data = await api.get('/products', query: query);
      if (data is! Map) throw Exception('Bad response');
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

  void _submit() {
    setState(() => _q = _controller.text.trim());
    _fetch(reset: true);
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
                : _error != null
                    ? Center(child: Text(_error!))
                    : _items.isEmpty
                        ? const Center(child: Text('No products found'))
                        : RefreshIndicator(
                            onRefresh: () => _fetch(reset: true),
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
                            _fetch();
                          }
                        : null,
                    child: const Text('Previous'),
                  ),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Text('Page $_page / $_lastPage'),
                  ),
                  FilledButton.tonal(
                    onPressed: _page < _lastPage && !_loading
                        ? () {
                            setState(() => _page++);
                            _fetch();
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
