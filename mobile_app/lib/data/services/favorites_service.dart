import '../../core/network/api_client.dart';
import '../../config/api_endpoints.dart';

class FavoritesService {
  final ApiClient api;
  FavoritesService(this.api);

  Future<dynamic> list() async {
    return api.get(ApiEndpoints.favoritesList);
  }

  Future<dynamic> toggle(int attractionId) async {
    return api.post(ApiEndpoints.toggleFavorite, data: {
      'attraction_id': attractionId,
    });
  }
}
