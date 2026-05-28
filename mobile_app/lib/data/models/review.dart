class Review {
  final int id;
  final int userId;
  final int rating;
  final String comment;
  Review({required this.id, required this.userId, required this.rating, required this.comment});
  factory Review.fromJson(Map<String, dynamic> json) => Review(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      rating: json['rating'] ?? 0,
      comment: json['comment'] ?? '');
}
