import '../services/attractions_service.dart';

class AttractionsRepository {
  final AttractionsService service;
  AttractionsRepository(this.service);

  Future<dynamic> list({int? cityId, int? typeId, String? search}) =>
      service.list(cityId: cityId, typeId: typeId, search: search);
  Future<dynamic> details(int id) => service.details(id);
}
