import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/format/money.dart';
import '../../models/order.dart';
import '../../models/pagination.dart';
import '../../providers/delivery_auth_and_api.dart';

const _filters = ['active', 'delivered', 'all'];
const _filterLabels = ['Active', 'Delivered', 'All'];

class DeliveryOrdersScreen extends ConsumerStatefulWidget {
  const DeliveryOrdersScreen({super.key});

  @override
  ConsumerState<DeliveryOrdersScreen> createState() =>
      _DeliveryOrdersScreenState();
}

class _DeliveryOrdersScreenState extends ConsumerState<DeliveryOrdersScreen> {
  int _tab = 0;
  final _scrollController = ScrollController();

  int _page = 1;
  List<Order> _items = [];
  int _lastPage = 1;
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  static const _pageSize = 20;
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
      final api = ref.read(deliveryApiClientProvider);
      final query = <String, dynamic>{
        'page': 1,
        'limit': _pageSize,
        'filter': _filters[_tab],
      };
      final data = await api.get('/delivery/orders', query: query, auth: true);
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
      final api = ref.read(deliveryApiClientProvider);
      final query = <String, dynamic>{
        'page': nextPage,
        'limit': _pageSize,
        'filter': _filters[_tab],
      };
      final data = await api.get('/delivery/orders', query: query, auth: true);
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My deliveries'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Sign out',
            onPressed: () async {
              await ref.read(deliveryAuthNotifierProvider.notifier).logout();
              if (context.mounted) context.go('/home');
            },
          ),
        ],
      ),
      body: Column(
        children: [
          SizedBox(
            height: 48,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 8),
              itemCount: _filterLabels.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, i) {
                final selected = _tab == i;
                return ChoiceChip(
                  label: Text(_filterLabels[i]),
                  selected: selected,
                  onSelected: (_) => _onTab(i),
                );
              },
            ),
          ),
          Expanded(
            child: _loading && _items.isEmpty
                ? const Center(child: CircularProgressIndicator())
                : _error != null && _items.isEmpty
                    ? Center(child: Text(_error!))
                    : _items.isEmpty
                        ? const Center(child: Text('No assigned orders'))
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
                                        '${o.status} · ${o.customerName}',
                                      ),
                                      trailing: Text(formatMoney(o.total)),
                                      onTap: () => context.push(
                                        '/delivery/orders/${o.id}',
                                      ),
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
