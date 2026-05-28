import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../data/services/reviews_service.dart';

class ReviewsProvider extends ChangeNotifier {
  final ReviewsService service;
  ReviewsProvider(this.service);

  List<dynamic> reviews = [];
  bool isLoading = false;
  String? lastError;

  Future<void> fetchReviews(int attractionId) async {
    isLoading = true;
    lastError = null;
    notifyListeners();
    try {
      // TODO: implement reviews list endpoint usage
    } catch (_) {
      lastError = 'تعذر تحميل التقييمات.';
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> addReview(int attractionId, int rating, {String? comment}) async {
    lastError = null;
    try {
      await service.addReview(
        attractionId: attractionId,
        rating: rating,
        comment: comment,
      );
      return true;
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map<String, dynamic> && data['message'] is String) {
        lastError = data['message'] as String;
      } else {
        lastError = 'تعذر إرسال التقييم.';
      }
      return false;
    } catch (_) {
      lastError = 'تعذر إرسال التقييم.';
      return false;
    }
  }

  Future<bool> updateReview(int attractionId, int rating, {String? comment}) async {
    lastError = null;
    try {
      await service.updateReview(
        attractionId: attractionId,
        rating: rating,
        comment: comment,
      );
      return true;
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map<String, dynamic> && data['message'] is String) {
        lastError = data['message'] as String;
      } else {
        lastError = 'تعذر تحديث التقييم.';
      }
      return false;
    } catch (_) {
      lastError = 'تعذر تحديث التقييم.';
      return false;
    }
  }

  Future<bool> deleteReview(int attractionId) async {
    lastError = null;
    try {
      await service.deleteReview(attractionId: attractionId);
      return true;
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map<String, dynamic> && data['message'] is String) {
        lastError = data['message'] as String;
      } else {
        lastError = 'تعذر حذف التقييم.';
      }
      return false;
    } catch (_) {
      lastError = 'تعذر حذف التقييم.';
      return false;
    }
  }
}
