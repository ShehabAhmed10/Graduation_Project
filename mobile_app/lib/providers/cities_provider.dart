import 'package:flutter/foundation.dart';

import '../data/services/cities_service.dart';

class CitiesProvider extends ChangeNotifier {
  final CitiesService service;
  CitiesProvider(this.service);

  List<dynamic> cities = [];
  bool isLoading = false;

  Future<void> fetchCities() async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.list();
      final root = resp.data as Map<String, dynamic>? ?? {};
      cities = root['data'] as List<dynamic>? ?? [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
