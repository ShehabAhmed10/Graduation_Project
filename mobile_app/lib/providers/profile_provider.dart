import 'package:flutter/foundation.dart';

import '../data/services/users_service.dart';

class ProfileProvider extends ChangeNotifier {
  final UsersService service;
  ProfileProvider(this.service);

  dynamic profile;
  bool isLoading = false;

  Future<void> fetchProfile() async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.profile();
      profile = resp.data;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> updateProfile(Map<String, dynamic> payload) async {
    await service.updateProfile(payload);
    await fetchProfile();
  }
}
