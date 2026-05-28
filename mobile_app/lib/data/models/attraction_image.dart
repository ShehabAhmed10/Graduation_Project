class AttractionImage {
  final int id;
  final String imageUrl;
  final int? sortOrder;

  AttractionImage({
    required this.id,
    required this.imageUrl,
    this.sortOrder,
  });

  factory AttractionImage.fromJson(Map<String, dynamic> json) => AttractionImage(
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
