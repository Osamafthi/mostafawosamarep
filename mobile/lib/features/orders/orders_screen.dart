import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/format/money.dart';
import '../../models/order.dart';
import '../../models/pagination.dart';
import '../../providers/auth_and_api.dart';

const _statuses = <String?>[
  null,
  'pending',
  'processing',
  'delivered',
  'cancelled',
];

const _statusLabels =
    <String>['All', 'Pending', 'Processing', 'Delivered', 'Cancelled'];

const _windows = ['6m', '1y', 'all'];
const _windowLabels = ['Last 6 months', 'Last year', 'All time'];

class OrdersScreen extends ConsumerStatefulWidget {
  const OrdersScreen({super.key});

  @override
  ConsumerState<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends ConsumerState<OrdersScreen> {
  int _tab = 0;
  String _window = '6m';
  final _scrollController = ScrollController();

  int _page = 1;
  List<Order> _items = [];
  int _lastPage = 1;
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  static const _pageSize = 10;
  static const _loadMoreExtent = 200.0;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _reload();
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

  Future<void> _reload() async {
    setState(() {
      _loading = true;
      _error = null;
      _page = 1;
    });
    try {
      final api = ref.read(apiClientProvider);
      final status = _statuses[_tab];
      final query = <String, dynamic>{
        'page': 1,
        'limit': _pageSize,
        'window': _window,
      };
      if (status != null) query['status'] = status;
      final data = await api.get('/customer/orders', query: query, auth: true);
      if (data is! Map) throw Exception('Bad response');
      final page = PaginatedOrders.fromJson(Map<String, dynamic>.from(data));
      if (mounted) {
        setState(() {
          _items = List<Order>.from(page.items);
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
      final status = _statuses[_tab];
      final query = <String, dynamic>{
        'page': nextPage,
        'limit': _pageSize,
        'window': _window,
      };
      if (status != null) query['status'] = status;
      final data = await api.get('/customer/orders', query: query, auth: true);
      if (data is! Map) throw Exception('Bad response');
      final page = PaginatedOrders.fromJson(Map<String, dynamic>.from(data));
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

  void _onTab(int i) {
    setState(() {
      _tab = i;
      _items = [];
    });
    _reload();
  }

  void _onWindow(String w) {
    setState(() {
      _window = w;
      _items = [];
    });
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My orders')),
      body: Column(
        children: [
          SizedBox(
            height: 48,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 8),
              itemCount: _statusLabels.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, i) {
                final selected = _tab == i;
                return ChoiceChip(
                  label: Text(_statusLabels[i]),
                  selected: selected,
                  onSelected: (_) => _onTab(i),
                );
              },
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                const Text('Period: '),
                Expanded(
                  child: DropdownButtonFormField<String>(
                    isExpanded: true,
                    value: _window,
                    items: List.generate(
                      _windows.length,
                      (i) => DropdownMenuItem(
                        value: _windows[i],
                        child: Text(_windowLabels[i]),
                      ),
                    ),
                    onChanged: (v) {
                      if (v != null) _onWindow(v);
                    },
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: _loading && _items.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : _error != null && _items.isEmpty
                    ? Center(child: Text(_error!))
                    : _items.isEmpty
                        ? const Center(child: Text('No orders yet'))
                        : RefreshIndicator(
                            onRefresh: _reload,
                            child: ListView.builder(
                              controller: _scrollController,
                              physics: const AlwaysScrollableScrollPhysics(),
                              padding: const EdgeInsets.all(12),
                              itemCount:
                                  _items.length + (_loadingMore ? 1 : 0),
                              itemBuilder: (context, i) {
                                if (i >= _items.length) {
                                  return const Padding(
                                    padding: EdgeInsets.only(
                                      top: 8,
                                      bottom: 24,
                                    ),
                                    child: Center(
                                      child: CircularProgressIndicator(),
                                    ),
                                  );
                                }
                                final o = _items[i];
                                return Padding(
                                  padding: const EdgeInsets.only(bottom: 8),
                                  child: Card(
                                    child: ListTile(
                                      title: Text(o.orderNumber),
                                      subtitle: Text(
                                        '${o.status} · ${o.createdAt ?? ''}',
                                      ),
                                      trailing: Text(formatMoney(o.total)),
                                      onTap: () =>
                                          context.push('/orders/${o.id}'),
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }
}
