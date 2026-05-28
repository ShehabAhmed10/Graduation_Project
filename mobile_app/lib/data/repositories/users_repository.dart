import '../services/users_service.dart';

class UsersRepository {
  final UsersService service;
  UsersRepository(this.service);

  Future<dynamic> profile() => service.profile();
  Future<dynamic> updateProfile(Map<String, dynamic> payload) => service.updateProfile(payload);
}
