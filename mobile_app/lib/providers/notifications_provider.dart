import 'package:flutter/foundation.dart';

import '../data/services/notifications_service.dart';

class NotificationsProvider extends ChangeNotifier {
  final NotificationsService service;
  NotificationsProvider(this.service);

  List<dynamic> notifications = [];
  bool isLoading = false;

  int get unreadCount {
    return notifications.where((n) => n['is_read'] == 0 || n['is_read'] == false).length;
  }

  Future<void> fetchNotifications() async {
    isLoading = true;
    notifyListeners();
    try {
      final resp = await service.list();
      final root = resp.data as Map<String, dynamic>? ?? {};
      notifications = root['data'] as List<dynamic>? ?? [];
    } catch (_) {
      // Ignore notification failures (e.g., 401 when guest).
      notifications = [];
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
