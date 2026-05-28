class ReviewImage {
  final int id;
  final int? reviewId;
  final String relativePath;
  final String url;
  final bool isApproved;

  ReviewImage({required this.id, this.reviewId, required this.relativePath, required this.url, required this.isApproved});

  factory ReviewImage.fromJson(Map<String, dynamic> json) {
    return ReviewImage(
      id: json['id'] ?? 0,
      reviewId: json['review_id'],
      relativePath: json['relative_path'] ?? json['image_url'] ?? '',
      url: json['url'] ?? json['image_url'] ?? '',
      isApproved: (json['is_approved'] == 1 || json['is_approved'] == true || json['status'] == 'approved'),
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'review_id': reviewId,
    'relative_path': relativePath,
    'url': url,
    'is_approved': isApproved ? 1 : 0,
  };
}
