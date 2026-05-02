import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/format/money.dart';
import '../../models/order.dart';
import '../../providers/auth_and_api.dart';

class OrderDetailScreen extends ConsumerStatefulWidget {
  const OrderDetailScreen({super.key, required this.orderId});

  final int orderId;

  @override
  ConsumerState<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends ConsumerState<OrderDetailScreen> {
  Order? _order;
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
      final data = await api.get(
        '/customer/orders/${widget.orderId}',
        auth: true,
      );
      if (data is! Map) throw Exception('Not found');
      if (mounted) {
        setState(() {
          _order = Order.fromJson(Map<String, dynamic>.from(data));
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
    if (_loading) {
      return Scaffold(
        appBar: AppBar(),
        body: const Center(child: CircularProgressIndicator()),
      );
    }
    if (_error != null || _order == null) {
      return Scaffold(
        appBar: AppBar(),
        body: Center(child: Text(_error ?? 'Order not found')),
      );
    }
    final o = _order!;
    return Scaffold(
      appBar: AppBar(title: Text(o.orderNumber)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text('Status: ${o.status}'),
          Text('Payment: ${o.paymentStatus}'),
          if (o.createdAt != null) Text('Placed: ${o.createdAt}'),
          const Divider(),
          Text(o.customerName),
          Text(o.customerEmail),
          if (o.customerPhone != null) Text(o.customerPhone!),
          const SizedBox(height: 8),
          Text(o.shippingAddress),
          if (o.customerLocation?.mapsUrl != null)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: SelectableText('Map: ${o.customerLocation!.mapsUrl}'),
            ),
          const Divider(height: 32),
          Text('Items', style: Theme.of(context).textTheme.titleMedium),
          ...o.items.map((item) {
            return ListTile(
              leading: item.imageUrl != null && item.imageUrl!.isNotEmpty
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: SizedBox(
                        width: 48,
                        height: 48,
                        child: CachedNetworkImage(
                          imageUrl: item.imageUrl!,
                          fit: BoxFit.cover,
                        ),
                      ),
                    )
                  : const Icon(Icons.inventory_2_outlined),
              title: Text(item.productName),
              subtitle: Text(
                '${item.quantity} × ${formatMoney(item.unitPrice)}',
              ),
              trailing: Text(formatMoney(item.subtotal)),
            );
          }),
          const Divider(),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Total'),
              Text(
                formatMoney(o.total),
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
