import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/format/money.dart';
import '../../core/network/api_error.dart';
import '../../models/order.dart';
import '../../providers/delivery_auth_and_api.dart';

class DeliveryOrderDetailScreen extends ConsumerStatefulWidget {
  const DeliveryOrderDetailScreen({super.key, required this.orderId});

  final int orderId;

  @override
  ConsumerState<DeliveryOrderDetailScreen> createState() =>
      _DeliveryOrderDetailScreenState();
}

class _DeliveryOrderDetailScreenState
    extends ConsumerState<DeliveryOrderDetailScreen> {
  Order? _order;
  bool _loading = true;
  bool _submitting = false;
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
      final api = ref.read(deliveryApiClientProvider);
      final data = await api.get(
        '/delivery/orders/${widget.orderId}',
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

  List<String> _allowedNextStatuses(String current) {
    switch (current) {
      case 'pending':
        return ['shipped'];
      case 'processing':
        return ['shipped'];
      case 'shipped':
        return ['delivered', 'processing'];
      default:
        return [];
    }
  }

  Future<void> _transition(String status) async {
    setState(() => _submitting = true);
    try {
      final api = ref.read(deliveryApiClientProvider);
      await api.patch(
        '/delivery/orders/${widget.orderId}/status',
        {'status': status},
        auth: true,
      );
      if (!mounted) return;
      await _load();
      if (mounted) {
        setState(() => _submitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Updated to $status')),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _submitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              e is ApiException ? e.message : e.toString(),
            ),
          ),
        );
      }
    }
  }

  Future<void> _confirmProcessingRollback() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Return to processing'),
        content: const Text(
          'Use this after a failed delivery attempt so the store can decide next steps.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
    if (ok == true && mounted) await _transition('processing');
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
    final actions = _allowedNextStatuses(o.status);

    return Scaffold(
      appBar: AppBar(title: Text(o.orderNumber)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text('Status: ${o.status}'),
          Text('Payment: ${o.paymentStatus}'),
          if (o.createdAt != null) Text('Placed: ${o.createdAt}'),
          const Divider(),
          Text(o.customerName, style: Theme.of(context).textTheme.titleSmall),
          Text(o.customerEmail),
          if (o.customerPhone != null) Text(o.customerPhone!),
          const SizedBox(height: 8),
          Text(o.shippingAddress),
          if (o.customerLocation?.mapsUrl != null)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: SelectableText('Map: ${o.customerLocation!.mapsUrl}'),
            ),
          if (actions.isNotEmpty) ...[
            const Divider(height: 32),
            Text('Actions', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                if (actions.contains('shipped'))
                  FilledButton(
                    onPressed: _submitting ? null : () => _transition('shipped'),
                    child: const Text('Mark shipped'),
                  ),
                if (actions.contains('delivered'))
                  FilledButton(
                    onPressed:
                        _submitting ? null : () => _transition('delivered'),
                    child: const Text('Mark delivered'),
                  ),
                if (actions.contains('processing'))
                  OutlinedButton(
                    onPressed: _submitting
                        ? null
                        : _confirmProcessingRollback,
                    child: const Text('Return to processing'),
                  ),
              ],
            ),
          ],
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
