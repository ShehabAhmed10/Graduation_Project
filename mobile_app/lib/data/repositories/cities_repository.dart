import '../services/cities_service.dart';

class CitiesRepository {
  final CitiesService service;
  CitiesRepository(this.service);

  Future<dynamic> list() => service.list();
}
