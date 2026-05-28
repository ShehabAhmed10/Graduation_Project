import 'package:flutter/foundation.dart';

class MapProvider extends ChangeNotifier {
  List<dynamic> mapAttractions = [];
  bool isLoading = false;

  Future<void> loadAttractionsForMap() async {
    // TODO: load filtered attractions for map
    notifyListeners();
  }
}
