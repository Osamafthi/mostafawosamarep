import 'product.dart';
import 'order.dart';

class PaginatedProducts {
  PaginatedProducts({
    required this.items,
    required this.total,
    required this.page,
    required this.limit,
    required this.lastPage,
  });

  final List<Product> items;
  final int total;
  final int page;
  final int limit;
  final int lastPage;

  factory PaginatedProducts.fromJson(Map<String, dynamic> j) {
    final raw = j['items'];
    final list = raw is List
        ? raw
            .map((e) => Product.fromJson(Map<String, dynamic>.from(e as Map)))
            .toList()
        : <Product>[];
    return PaginatedProducts(
      items: list,
      total: (j['total'] as num?)?.toInt() ?? 0,
      page: (j['page'] as num?)?.toInt() ?? 1,
      limit: (j['limit'] as num?)?.toInt() ?? 20,
      lastPage: (j['last_page'] as num?)?.toInt() ?? 1,
    );
  }
}

class PaginatedOrders {
  PaginatedOrders({
    required this.items,
    required this.total,
    required this.page,
    required this.limit,
    required this.lastPage,
  });

  final List<Order> items;
  final int total;
  final int page;
  final int limit;
  final int lastPage;

  factory PaginatedOrders.fromJson(Map<String, dynamic> j) {
    final raw = j['items'];
    final list = raw is List
        ? raw
            .map((e) => Order.fromJson(Map<String, dynamic>.from(e as Map)))
            .toList()
        : <Order>[];
    return PaginatedOrders(
      items: list,
      total: (j['total'] as num?)?.toInt() ?? 0,
      page: (j['page'] as num?)?.toInt() ?? 1,
      limit: (j['limit'] as num?)?.toInt() ?? 10,
      lastPage: (j['last_page'] as num?)?.toInt() ?? 1,
    );
  }
}
