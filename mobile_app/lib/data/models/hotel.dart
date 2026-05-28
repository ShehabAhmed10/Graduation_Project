class Hotel {
  final int id;
  final String name;
  Hotel({required this.id, required this.name});
  factory Hotel.fromJson(Map<String, dynamic> json) =>
      Hotel(id: json['id'] ?? 0, name: json['name'] ?? '');
  Map<String, dynamic> toJson() => {'id': id, 'name': name};
}
