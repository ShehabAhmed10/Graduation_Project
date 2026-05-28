import '../../config/api_endpoints.dart';
import '../../core/network/api_client.dart';

class CommentsService {
  final ApiClient api;
  CommentsService(this.api);

  Future<dynamic> addComment({
    required int attractionId,
    required String comment,
  }) async {
    return api.post(ApiEndpoints.addComment, data: {
      'attraction_id': attractionId,
      'comment': comment,
    });
  }
}
