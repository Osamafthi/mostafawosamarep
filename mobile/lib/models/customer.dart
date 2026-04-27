class Customer {
  Customer({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.defaultShippingAddress,
    required this.emailVerified,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? defaultShippingAddress;
  final bool emailVerified;
  final String? createdAt;
  final String? updatedAt;

  factory Customer.fromJson(Map<String, dynamic> j) {
    return Customer(
      id: (j['id'] as num).toInt(),
      name: j['name'] as String? ?? '',
      email: j['email'] as String? ?? '',
      phone: j['phone'] as String?,
      defaultShippingAddress: j['default_shipping_address'] as String?,
      emailVerified: j['email_verified'] == true,
      createdAt: j['created_at'] as String?,
      updatedAt: j['updated_at'] as String?,
    );
  }
}
