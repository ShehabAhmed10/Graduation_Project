class AppNotification {
  final int id;
  final String message;
  AppNotification({required this.id, required this.message});
  factory AppNotification.fromJson(Map<String, dynamic> json) =>
      AppNotification(id: json['id'] ?? 0, message: json['message'] ?? '');
}
