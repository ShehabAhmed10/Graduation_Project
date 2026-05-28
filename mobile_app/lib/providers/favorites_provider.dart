import 'package:flutter/foundation.dart';

import '../data/services/favorites_service.dart';

class FavoritesProvider extends ChangeNotifier {
  final FavoritesService service;
  FavoritesProvider(this.service);

  List<dynamic> favorites = [];
  Set<int> favoriteAttractionIds = {};
  bool isLoading = false;
  String? lastError;

  Future<void> fetchFavorites() async {
    isLoading = true;
    lastError = null;
    notifyListeners();
    try {
      final resp = await service.list();
      final root = resp.data as Map<String, dynamic>? ?? {};
      final data = root['data'] as Map<String, dynamic>? ?? {};
      favorites = data['favorites'] as List<dynamic>? ?? [];
      favoriteAttractionIds = favorites
          .map((item) {
            final rawId = (item is Map) ? item['id'] : null;
            return rawId == null ? null : int.tryParse(rawId.toString());
          })
          .whereType<int>()
          .toSet();
    } catch (_) {
      lastError = 'تعذر تحميل المفضلة.';
      favorites = [];
      favoriteAttractionIds = {};
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  bool isFavorited(int attractionId) => favoriteAttractionIds.contains(attractionId);

  Future<bool> toggleFavorite(int attractionId) async {
    lastError = null;
    try {
      await service.toggle(attractionId);
      await fetchFavorites();
      return true;
    } catch (_) {
      lastError = 'تعذر تحديث المفضلة.';
      return false;
    }
  }
}
