import '../services/reviews_service.dart';

class ReviewsRepository {
  final ReviewsService service;
  ReviewsRepository(this.service);

  Future<dynamic> addReview({required int attractionId, required int rating, String? comment}) =>
      service.addReview(attractionId: attractionId, rating: rating, comment: comment);
}
