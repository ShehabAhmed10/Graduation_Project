import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class NotificationsService {
  final ApiClient api;
  NotificationsService(this.api);

  Future<dynamic> list() async {
    return api.get(ApiEndpoints.notificationsList);
  }

  Future<dynamic> markRead(int id) async {
    return api.post(ApiEndpoints.notificationsMarkRead, data: {
      'id': id,
    });
  }
}
