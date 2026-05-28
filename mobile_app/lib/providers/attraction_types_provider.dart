import 'package:flutter/foundation.dart';

import '../data/services/attraction_types_service.dart';

class AttractionTypesProvider extends ChangeNotifier {
  final AttractionTypesService service;
  AttractionTypesProvider(this.service);

  List<dynamic> types = [];
  bool isLoading = false;

  Future<void> fetchTypes() async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.list();
      final root = resp.data as Map<String, dynamic>? ?? {};
      types = root['data'] as List<dynamic>? ?? [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
