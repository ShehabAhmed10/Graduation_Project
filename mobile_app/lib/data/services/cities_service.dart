import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class CitiesService {
  final ApiClient api;
  CitiesService(this.api);

  Future<dynamic> list() async {
    return api.get(ApiEndpoints.cities);
  }
}
