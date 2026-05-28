import '../services/notifications_service.dart';

class NotificationsRepository {
  final NotificationsService service;
  NotificationsRepository(this.service);

  Future<dynamic> list() => service.list();
  Future<dynamic> markRead(int id) => service.markRead(id);
}
