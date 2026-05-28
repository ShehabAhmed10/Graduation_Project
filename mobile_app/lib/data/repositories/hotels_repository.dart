import '../services/hotels_service.dart';

class HotelsRepository {
  final HotelsService service;
  HotelsRepository(this.service);

  Future<dynamic> listByCity(int cityId, {int? minStars}) =>
      service.listByCity(cityId, minStars: minStars);
  Future<dynamic> details(int id) => service.details(id);
}
