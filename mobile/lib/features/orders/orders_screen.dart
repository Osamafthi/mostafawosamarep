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

const _statusLabels = <String>['All', 'Pending', 'Processing', 'Delivered', 'Cancelled'];

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
  int _page = 1;
  List<Order> _items = [];
  int _lastPage = 1;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  Future<void> _fetch() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = ref.read(apiClientProvider);
      final status = _statuses[_tab];
      final query = <String, dynamic>{
        'page': _page,
        'limit': 10,
        'window': _window,
      };
      if (status != null) query['status'] = status;
      final data = await api.get('/customer/orders', query: query, auth: true);
      if (data is! Map) throw Exception('Bad response');
      final page = PaginatedOrders.fromJson(Map<String, dynamic>.from(data));
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

  void _onTab(int i) {
    setState(() {
      _tab = i;
      _page = 1;
    });
    _fetch();
  }

  void _onWindow(String w) {
    setState(() {
      _window = w;
      _page = 1;
    });
    _fetch();
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
                : _error != null
                    ? Center(child: Text(_error!))
                    : _items.isEmpty
                        ? const Center(child: Text('No orders yet'))
                        : RefreshIndicator(
                            onRefresh: () async {
                              _page = 1;
                              await _fetch();
                            },
                            child: ListView.separated(
                              padding: const EdgeInsets.all(12),
                              itemCount: _items.length,
                              separatorBuilder: (_, __) =>
                                  const SizedBox(height: 8),
                              itemBuilder: (context, i) {
                                final o = _items[i];
                                return Card(
                                  child: ListTile(
                                    title: Text(o.orderNumber),
                                    subtitle: Text(
                                      '${o.status} · ${o.createdAt ?? ''}',
                                    ),
                                    trailing: Text(formatMoney(o.total)),
                                    onTap: () =>
                                        context.push('/orders/${o.id}'),
                                  ),
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
