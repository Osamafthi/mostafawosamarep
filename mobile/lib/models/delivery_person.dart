/// Courier profile from `GET /delivery/me` or login payload (`delivery_person`).
class DeliveryPersonProfile {
  DeliveryPersonProfile({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    required this.isActive,
    this.lastAssignedAt,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final bool isActive;
  final String? lastAssignedAt;
  final String? createdAt;
  final String? updatedAt;

  factory DeliveryPersonProfile.fromJson(Map<String, dynamic> j) {
    return DeliveryPersonProfile(
      id: (j['id'] as num).toInt(),
      name: j['name'] as String? ?? '',
      email: j['email'] as String? ?? '',
      phone: j['phone'] as String?,
      isActive: j['is_active'] as bool? ?? true,
      lastAssignedAt: j['last_assigned_at'] as String?,
      createdAt: j['created_at'] as String?,
      updatedAt: j['updated_at'] as String?,
    );
  }
}
