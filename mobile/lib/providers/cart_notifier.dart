import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/storage/cart_storage.dart';
import '../models/product.dart';

const _maxQty = 99;

final cartNotifierProvider =
    NotifierProvider<CartNotifier, List<CartLine>>(CartNotifier.new);

class CartSnapshot {
  CartSnapshot({
    required this.name,
    required this.price,
    this.discountPrice,
    this.imageUrl,
    this.slug,
  });

  final String name;
  final double price;
  final double? discountPrice;
  final String? imageUrl;
  final String? slug;

  Map<String, dynamic> toJson() => {
        'name': name,
        'price': price,
        'discount_price': discountPrice,
        'image_url': imageUrl,
        'slug': slug,
      };

  factory CartSnapshot.fromJson(Map<String, dynamic> j) {
    return CartSnapshot(
      name: j['name'] as String? ?? '',
      price: (j['price'] as num?)?.toDouble() ?? 0,
      discountPrice: (j['discount_price'] as num?)?.toDouble(),
      imageUrl: j['image_url'] as String?,
      slug: j['slug'] as String?,
    );
  }
}

class CartLine {
  CartLine({
    required this.productId,
    required this.qty,
    required this.snapshot,
  });

  final int productId;
  int qty;
  CartSnapshot snapshot;

  Map<String, dynamic> toJson() => {
        'product_id': productId,
        'qty': qty,
        'snapshot': snapshot.toJson(),
      };

  factory CartLine.fromJson(Map<String, dynamic> j) {
    final snap = j['snapshot'];
    return CartLine(
      productId: (j['product_id'] as num).toInt(),
      qty: (j['qty'] as num?)?.toInt() ?? 1,
      snapshot: snap is Map
          ? CartSnapshot.fromJson(Map<String, dynamic>.from(snap))
          : CartSnapshot(name: '', price: 0),
    );
  }
}

class CartNotifier extends Notifier<List<CartLine>> {
  final _storage = CartStorage();

  @override
  List<CartLine> build() {
    Future.microtask(_hydrate);
    return [];
  }

  Future<void> _hydrate() async {
    final raw = await _storage.loadRaw();
    final lines = raw.map(CartLine.fromJson).toList();
    state = lines;
  }

  Future<void> _persist() async {
    await _storage.saveRaw(state.map((e) => e.toJson()).toList());
  }

  double subtotal() {
    double s = 0;
    for (final line in state) {
      final unit = line.snapshot.discountPrice != null &&
              line.snapshot.discountPrice! > 0
          ? line.snapshot.discountPrice!
          : line.snapshot.price;
      s += unit * line.qty;
    }
    return s;
  }

  int count() => state.fold(0, (a, b) => a + b.qty);

  Future<void> add(Product product, int qty) async {
    final add = qty.clamp(1, _maxQty);
    final id = product.id;
    final lines = [...state];
    final idx = lines.indexWhere((e) => e.productId == id);
    final snap = CartSnapshot(
      name: product.name,
      price: product.price,
      discountPrice: product.discountPrice,
      imageUrl: product.imageUrl,
      slug: product.slug,
    );
    if (idx >= 0) {
      lines[idx].qty =
          (lines[idx].qty + add).clamp(1, _maxQty);
      lines[idx].snapshot = snap;
    } else {
      lines.add(CartLine(productId: id, qty: add, snapshot: snap));
    }
    state = lines;
    await _persist();
  }

  Future<void> setQty(int productId, int qty) async {
    final next = qty.clamp(0, _maxQty);
    final lines = [...state];
    final idx = lines.indexWhere((e) => e.productId == productId);
    if (idx < 0) return;
    if (next <= 0) {
      lines.removeAt(idx);
    } else {
      lines[idx].qty = next;
    }
    state = lines;
    await _persist();
  }

  Future<void> remove(int productId) async {
    state = state.where((e) => e.productId != productId).toList();
    await _persist();
  }

  Future<void> clear() async {
    state = [];
    await _persist();
  }

  List<Map<String, dynamic>> toOrderItems() {
    return state
        .map(
          (e) => {
            'product_id': e.productId,
            'quantity': e.qty,
          },
        )
        .toList();
  }
}
