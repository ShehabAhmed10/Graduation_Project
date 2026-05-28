class Attraction {
  final int id;
  final String title;
  Attraction({required this.id, required this.title});
  factory Attraction.fromJson(Map<String, dynamic> json) =>
      Attraction(id: json['id'] ?? 0, title: json['title'] ?? '');
  Map<String, dynamic> toJson() => {'id': id, 'title': title};
}
