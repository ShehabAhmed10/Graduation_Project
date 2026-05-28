import 'dart:io';
import 'package:dio/dio.dart';
import '../../config/api_endpoints.dart';
import '../../core/network/api_client.dart';
import '../models/review_image.dart';

class ReviewsService {
  final ApiClient api;
  ReviewsService(this.api);

  Future<ReviewImage> uploadReviewImage(File file) async {
    final fileName = file.path.split('/').last;
    final formData = FormData.fromMap({
      'image': await MultipartFile.fromFile(file.path, filename: fileName),
    });

    final resp = await api.dio.post(
      ApiEndpoints.uploadReviewImage,
      data: formData,
      options: Options(headers: {'Content-Type': 'multipart/form-data'}),
    );

    return ReviewImage.fromJson(resp.data);
  }

  Future<dynamic> addReview({
    required int attractionId,
    required int rating,
    String? comment,
  }) async {
    return api.post(ApiEndpoints.addReview, data: {
      'attraction_id': attractionId,
      'rating': rating,
      'comment': comment,
    });
  }

  Future<dynamic> updateReview({
    required int attractionId,
    required int rating,
    String? comment,
  }) async {
    return api.post(ApiEndpoints.updateReview, data: {
      'attraction_id': attractionId,
      'rating': rating,
      'comment': comment,
    });
  }

  Future<dynamic> deleteReview({
    required int attractionId,
  }) async {
    return api.post(ApiEndpoints.deleteReview, data: {
      'attraction_id': attractionId,
    });
  }
}
