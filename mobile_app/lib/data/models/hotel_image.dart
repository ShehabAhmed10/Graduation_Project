class HotelImage {
  final int id;
  final String imageUrl;
  final int? sortOrder;

  HotelImage({
    required this.id,
    required this.imageUrl,
    this.sortOrder,
  });

  factory HotelImage.fromJson(Map<String, dynamic> json) => HotelImage(
        id: json['id'] ?? 0,
        imageUrl: json['image_url'] ?? '',
        sortOrder: json['sort_order'],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'image_url': imageUrl,
        'sort_order': sortOrder,
      };
}
