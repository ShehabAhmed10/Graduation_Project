class AttractionType {
  final int id;
  final String typeName;
  final String? iconName;
  final String? markerColor;

  AttractionType({
    required this.id,
    required this.typeName,
    this.iconName,
    this.markerColor,
  });

  factory AttractionType.fromJson(Map<String, dynamic> json) => AttractionType(
        id: json['id'] ?? 0,
        typeName: json['type_name'] ?? '',
        iconName: json['icon_name'],
        markerColor: json['marker_color'],
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'type_name': typeName,
        'icon_name': iconName,
        'marker_color': markerColor,
      };
}
