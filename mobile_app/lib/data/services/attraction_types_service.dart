import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class AttractionTypesService {
  final ApiClient api;
  AttractionTypesService(this.api);

  Future<dynamic> list() async {
    return api.get(ApiEndpoints.attractionTypes);
  }
}
