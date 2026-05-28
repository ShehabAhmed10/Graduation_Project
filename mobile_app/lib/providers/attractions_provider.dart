import 'package:flutter/foundation.dart';

import '../data/services/attractions_service.dart';

class AttractionsProvider extends ChangeNotifier {
  final AttractionsService service;
  AttractionsProvider(this.service);

  List<dynamic> attractions = [];
  List<dynamic> featuredAttractions = [];
  dynamic selectedAttraction;
  bool isLoading = false;

  Future<void> fetchAttractions({int? cityId, int? typeId, String? search, bool? featured}) async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.list(
        cityId: cityId,
        typeId: typeId,
        search: search,
        featured: featured,
      );
      final root = resp.data as Map<String, dynamic>? ?? {};
      final data = root['data'] as Map<String, dynamic>? ?? {};
      final list = data['attractions'] as List<dynamic>? ?? [];
      if (featured == true) {
        featuredAttractions = list;
      } else {
        attractions = list;
      }
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchFeatured() async {
    await fetchAttractions(featured: true);
  }

  Future<void> fetchDetails(int id) async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.details(id);
      final root = resp.data as Map<String, dynamic>? ?? {};
      selectedAttraction = root['data'] ?? {};
      final data = selectedAttraction as Map<String, dynamic>;
      final attraction = data['attraction'] as Map<String, dynamic>? ?? {};
      if (attraction.isNotEmpty) {
        final idx = attractions.indexWhere((item) => item is Map && item['id'] == id);
        if (idx >= 0) {
          final updated = Map<String, dynamic>.from(attractions[idx] as Map);
          updated['avg_rating'] = attraction['avg_rating'];
          updated['total_reviews'] = attraction['total_reviews'];
          attractions[idx] = updated;
        }
        final featuredIdx = featuredAttractions.indexWhere((item) => item is Map && item['id'] == id);
        if (featuredIdx >= 0) {
          final updated = Map<String, dynamic>.from(featuredAttractions[featuredIdx] as Map);
          updated['avg_rating'] = attraction['avg_rating'];
          updated['total_reviews'] = attraction['total_reviews'];
          featuredAttractions[featuredIdx] = updated;
        }
        notifyListeners();
      }
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
