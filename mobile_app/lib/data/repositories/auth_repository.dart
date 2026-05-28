import '../services/auth_service.dart';

class AuthRepository {
  final AuthService service;
  AuthRepository(this.service);

  Future<dynamic> login(String email, String password) => service.login(email, password);
  Future<dynamic> register(String fullName, String email, String password, {String? phone}) =>
      service.register(fullName, email, password, phone: phone);
}
