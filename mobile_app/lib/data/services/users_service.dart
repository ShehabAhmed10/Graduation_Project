import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class UsersService {
  final ApiClient api;
  UsersService(this.api);

  Future<dynamic> profile() async {
    return api.get(ApiEndpoints.profile);
  }

  Future<dynamic> updateProfile(Map<String, dynamic> payload) async {
    return api.post(ApiEndpoints.updateProfile, data: payload);
  }
}
