class OrderItem {
  OrderItem({
    required this.id,
    required this.orderId,
    this.productId,
    required this.productName,
    required this.quantity,
    required this.unitPrice,
    required this.subtotal,
    this.imageUrl,
    this.createdAt,
  });

  final int id;
  final int orderId;
  final int? productId;
  final String productName;
  final int quantity;
  final double unitPrice;
  final double subtotal;
  final String? imageUrl;
  final String? createdAt;

  factory OrderItem.fromJson(Map<String, dynamic> j) {
    return OrderItem(
      id: (j['id'] as num).toInt(),
      orderId: (j['order_id'] as num).toInt(),
      productId: (j['product_id'] as num?)?.toInt(),
      productName: j['product_name'] as String? ?? '',
      quantity: (j['quantity'] as num?)?.toInt() ?? 0,
      unitPrice: (j['unit_price'] as num?)?.toDouble() ?? 0,
      subtotal: (j['subtotal'] as num?)?.toDouble() ?? 0,
      imageUrl: j['image_url'] as String?,
      createdAt: j['created_at'] as String?,
    );
  }
}

class CustomerLocation {
  CustomerLocation({
    this.lat,
    this.lng,
    this.mapsUrl,
    this.source,
  });

  final double? lat;
  final double? lng;
  final String? mapsUrl;
  final String? source;

  factory CustomerLocation.fromJson(Map<String, dynamic> j) {
    return CustomerLocation(
      lat: (j['lat'] as num?)?.toDouble(),
      lng: (j['lng'] as num?)?.toDouble(),
      mapsUrl: j['maps_url'] as String?,
      source: j['source'] as String?,
    );
  }
}

class DeliveryPerson {
  DeliveryPerson({
    required this.id,
    required this.name,
    this.phone,
  });

  final int id;
  final String name;
  final String? phone;

  factory DeliveryPerson.fromJson(Map<String, dynamic> j) {
    return DeliveryPerson(
      id: (j['id'] as num).toInt(),
      name: j['name'] as String? ?? '',
      phone: j['phone'] as String?,
    );
  }
}

class Order {
  Order({
    required this.id,
    required this.orderNumber,
    this.customerId,
    required this.customerName,
    required this.customerEmail,
    this.customerPhone,
    required this.shippingAddress,
    this.customerLocation,
    required this.subtotal,
    required this.total,
    required this.status,
    required this.paymentStatus,
    this.deliveryPerson,
    this.items = const [],
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String orderNumber;
  final int? customerId;
  final String customerName;
  final String customerEmail;
  final String? customerPhone;
  final String shippingAddress;
  final CustomerLocation? customerLocation;
  final double subtotal;
  final double total;
  final String status;
  final String paymentStatus;
  final DeliveryPerson? deliveryPerson;
  final List<OrderItem> items;
  final String? createdAt;
  final String? updatedAt;

  factory Order.fromJson(Map<String, dynamic> j) {
    final loc = j['customer_location'];
    CustomerLocation? cl;
    if (loc is Map) {
      cl = CustomerLocation.fromJson(Map<String, dynamic>.from(loc));
    }
    final dp = j['delivery_person'];
    DeliveryPerson? dpp;
    if (dp is Map) {
      dpp = DeliveryPerson.fromJson(Map<String, dynamic>.from(dp));
    }
    final rawItems = j['items'];
    List<OrderItem> items = [];
    if (rawItems is List) {
      items = rawItems
          .map((e) => OrderItem.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
    }
    return Order(
      id: (j['id'] as num).toInt(),
      orderNumber: j['order_number'] as String? ?? '',
      customerId: (j['customer_id'] as num?)?.toInt(),
      customerName: j['customer_name'] as String? ?? '',
      customerEmail: j['customer_email'] as String? ?? '',
      customerPhone: j['customer_phone'] as String?,
      shippingAddress: j['shipping_address'] as String? ?? '',
      customerLocation: cl,
      subtotal: (j['subtotal'] as num?)?.toDouble() ?? 0,
      total: (j['total'] as num?)?.toDouble() ?? 0,
      status: j['status'] as String? ?? '',
      paymentStatus: j['payment_status'] as String? ?? '',
      deliveryPerson: dpp,
      items: items,
      createdAt: j['created_at'] as String?,
      updatedAt: j['updated_at'] as String?,
    );
  }
}
