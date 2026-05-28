import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class AuthService {
  final ApiClient api;
  AuthService(this.api);

  Future<dynamic> login(String email, String password) async {
    return api.post(ApiEndpoints.login, data: {
      'email': email,
      'password': password,
    });
  }

  Future<dynamic> register(String fullName, String email, String password, {String? phone}) async {
    return api.post(ApiEndpoints.register, data: {
      'full_name': fullName,
      'email': email,
      'password': password,
      'phone': phone,
    });
  }
}
