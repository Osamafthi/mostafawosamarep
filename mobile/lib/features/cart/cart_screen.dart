import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/format/money.dart';
import '../../providers/cart_notifier.dart';

class CartScreen extends ConsumerWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lines = ref.watch(cartNotifierProvider);
    final notifier = ref.read(cartNotifierProvider.notifier);
    final subtotal = notifier.subtotal();

    return Scaffold(
      appBar: AppBar(title: const Text('Cart')),
      body: lines.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('Your cart is empty'),
                  const SizedBox(height: 16),
                  FilledButton(
                    onPressed: () => context.go('/home'),
                    child: const Text('Continue shopping'),
                  ),
                ],
              ),
            )
          : Column(
              children: [
                Expanded(
                  child: ListView.separated(
                    padding: const EdgeInsets.all(12),
                    itemCount: lines.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (context, i) {
                      final line = lines[i];
                      final snap = line.snapshot;
                      final unit = effectiveUnitPrice(snap.price, snap.discountPrice);
                      final url = snap.imageUrl;
                      return Card(
                        child: Padding(
                          padding: const EdgeInsets.all(8),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: SizedBox(
                                  width: 72,
                                  height: 72,
                                  child: url != null && url.isNotEmpty
                                      ? CachedNetworkImage(
                                          imageUrl: url,
                                          fit: BoxFit.cover,
                                        )
                                      : const ColoredBox(
                                          color: Color(0xFFEEEEEE),
                                          child: Icon(Icons.image_not_supported),
                                        ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      snap.name,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      style: Theme.of(context).textTheme.titleSmall,
                                    ),
                                    Text(formatMoney(unit)),
                                    Row(
                                      children: [
                                        IconButton(
                                          onPressed: line.qty > 1
                                              ? () => notifier.setQty(
                                                    line.productId,
                                                    line.qty - 1,
                                                  )
                                              : null,
                                          icon: const Icon(Icons.remove),
                                        ),
                                        Text('${line.qty}'),
                                        IconButton(
                                          onPressed: line.qty < 99
                                              ? () => notifier.setQty(
                                                    line.productId,
                                                    line.qty + 1,
                                                  )
                                              : null,
                                          icon: const Icon(Icons.add),
                                        ),
                                        IconButton(
                                          onPressed: () =>
                                              notifier.remove(line.productId),
                                          icon: const Icon(Icons.delete_outline),
                                        ),
                                      ],
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
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Subtotal'),
                          Text(
                            formatMoney(subtotal),
                            style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      FilledButton(
                        onPressed: () => context.push('/checkout'),
                        child: const Text('Proceed to checkout'),
                      ),
                      TextButton(
                        onPressed: () async {
                          final ok = await showDialog<bool>(
                            context: context,
                            builder: (c) => AlertDialog(
                              title: const Text('Clear cart?'),
                              actions: [
                                TextButton(
                                  onPressed: () => Navigator.pop(c, false),
                                  child: const Text('Cancel'),
                                ),
                                FilledButton(
                                  onPressed: () => Navigator.pop(c, true),
                                  child: const Text('Clear'),
                                ),
                              ],
                            ),
                          );
                          if (ok == true && context.mounted) {
                            await notifier.clear();
                          }
                        },
                        child: const Text('Clear cart'),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
