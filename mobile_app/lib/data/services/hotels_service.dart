import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class HotelsService {
  final ApiClient api;
  HotelsService(this.api);

  Future<dynamic> listByCity(int cityId, {int? minStars}) async {
    return api.get(ApiEndpoints.hotelsByCity, queryParameters: {
      'city_id': cityId,
      if (minStars != null) 'min_stars': minStars,
    });
  }

  Future<dynamic> details(int id) async {
    return api.get(ApiEndpoints.hotelDetails, queryParameters: {
      'id': id,
    });
  }
}
