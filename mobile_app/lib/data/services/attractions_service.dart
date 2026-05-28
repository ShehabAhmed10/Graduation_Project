import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class AttractionsService {
  final ApiClient api;
  AttractionsService(this.api);

  Future<dynamic> list({int? cityId, int? typeId, String? search, bool? featured}) async {
    return api.get(ApiEndpoints.attractionsList, queryParameters: {
      if (cityId != null) 'city_id': cityId,
      if (typeId != null) 'type_id': typeId,
      if (search != null && search.isNotEmpty) 'search': search,
      if (featured == true) 'featured': 1,
    });
  }

  Future<dynamic> details(int id) async {
    return api.get(ApiEndpoints.attractionDetails, queryParameters: {
      'id': id,
    });
  }
}
