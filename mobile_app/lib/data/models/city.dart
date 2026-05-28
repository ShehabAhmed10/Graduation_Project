class City {
  final int id;
  final String name;
  City({required this.id, required this.name});
  factory City.fromJson(Map<String, dynamic> json) =>
      City(id: json['id'] ?? 0, name: json['name'] ?? '');
  Map<String, dynamic> toJson() => {'id': id, 'name': name};
}
