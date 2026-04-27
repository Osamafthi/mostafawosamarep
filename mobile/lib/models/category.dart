class Category {
  Category({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String name;
  final String slug;
  final String? description;
  final String? createdAt;
  final String? updatedAt;

  factory Category.fromJson(Map<String, dynamic> j) {
    return Category(
      id: (j['id'] as num).toInt(),
      name: j['name'] as String? ?? '',
      slug: j['slug'] as String? ?? '',
      description: j['description'] as String?,
      createdAt: j['created_at'] as String?,
      updatedAt: j['updated_at'] as String?,
    );
  }
}
