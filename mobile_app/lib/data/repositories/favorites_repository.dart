import '../services/favorites_service.dart';

class FavoritesRepository {
  final FavoritesService service;
  FavoritesRepository(this.service);

  Future<dynamic> list() => service.list();
  Future<dynamic> toggle(int attractionId) => service.toggle(attractionId);
}
