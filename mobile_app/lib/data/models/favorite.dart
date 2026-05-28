class Favorite {
  final int attractionId;

  Favorite({required this.attractionId});

  factory Favorite.fromJson(Map<String, dynamic> json) => Favorite(
        attractionId: json['attraction_id'] ?? 0,
      );

  Map<String, dynamic> toJson() => {
        'attraction_id': attractionId,
      };
}
