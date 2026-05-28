import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../data/services/comments_service.dart';

class CommentsProvider extends ChangeNotifier {
  final CommentsService service;
  CommentsProvider(this.service);

  String? lastError;
  bool isLoading = false;

  Future<bool> addComment(int attractionId, String comment) async {
    lastError = null;
    isLoading = true;
    notifyListeners();
    try {
      await service.addComment(
        attractionId: attractionId,
        comment: comment,
      );
      return true;
    } on DioException catch (e) {
      final data = e.response?.data;
      if (data is Map<String, dynamic> && data['message'] is String) {
        lastError = data['message'] as String;
      } else {
        lastError = 'تعذر إضافة التعليق.';
      }
      return false;
    } catch (_) {
      lastError = 'تعذر إضافة التعليق.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
