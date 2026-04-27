class ProductImage {
  ProductImage({
    required this.id,
    required this.url,
    required this.sortOrder,
  });

  final int id;
  final String url;
  final int sortOrder;

  factory ProductImage.fromJson(Map<String, dynamic> j) {
    return ProductImage(
      id: (j['id'] as num).toInt(),
      url: j['url'] as String? ?? '',
      sortOrder: (j['sort_order'] as num?)?.toInt() ?? 0,
    );
  }
}

class Product {
  Product({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    required this.price,
    this.discountPrice,
    required this.stock,
    required this.categoryId,
    this.categoryName,
    this.categorySlug,
    this.imageUrl,
    required this.status,
    this.images = const [],
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String name;
  final String slug;
  final String? description;
  final double price;
  final double? discountPrice;
  final int stock;
  final int categoryId;
  final String? categoryName;
  final String? categorySlug;
  final String? imageUrl;
  final String status;
  final List<ProductImage> images;
  final String? createdAt;
  final String? updatedAt;

  factory Product.fromJson(Map<String, dynamic> j) {
    final imgs = j['images'];
    List<ProductImage> list = [];
    if (imgs is List) {
      list = imgs
          .map((e) => ProductImage.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList();
    }
    return Product(
      id: (j['id'] as num).toInt(),
      name: j['name'] as String? ?? '',
      slug: j['slug'] as String? ?? '',
      description: j['description'] as String?,
      price: (j['price'] as num?)?.toDouble() ?? 0,
      discountPrice: (j['discount_price'] as num?)?.toDouble(),
      stock: (j['stock'] as num?)?.toInt() ?? 0,
      categoryId: (j['category_id'] as num?)?.toInt() ?? 0,
      categoryName: j['category_name'] as String?,
      categorySlug: j['category_slug'] as String?,
      imageUrl: j['image_url'] as String?,
      status: j['status'] as String? ?? 'active',
      images: list,
      createdAt: j['created_at'] as String?,
      updatedAt: j['updated_at'] as String?,
    );
  }
}
