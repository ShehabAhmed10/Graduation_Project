import 'package:flutter/foundation.dart';

import '../data/services/hotels_service.dart';

class HotelsProvider extends ChangeNotifier {
  final HotelsService service;
  HotelsProvider(this.service);

  List<dynamic> hotels = [];
  dynamic selectedHotel;
  bool isLoading = false;

  Future<void> fetchByCity(int cityId, {int? minStars}) async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.listByCity(cityId, minStars: minStars);
      hotels = resp.data?['hotels'] ?? [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchDetails(int id) async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.details(id);
      final root = resp.data as Map<String, dynamic>? ?? {};
      selectedHotel = root['data'] ?? {};
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
