class Location {
  final int id;
  final String name;
  final String? street;
  final double? latitude;
  final double? longitude;

  Location({
    required this.id,
    required this.name,
    this.street,
    this.latitude,
    this.longitude,
  });

  factory Location.fromJson(Map<String, dynamic> json) => Location(
        id: json['id'] ?? 0,
        name: json['name'] ?? '',
        street: json['street'],
        latitude: (json['latitude'] as num?)?.toDouble(),
        longitude: (json['longitude'] as num?)?.toDouble(),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'street': street,
        'latitude': latitude,
        'longitude': longitude,
      };
}
