import 'package:flutter/material.dart';

class RatingStars extends StatelessWidget {
  final int rating;
  final int max;

  const RatingStars({Key? key, required this.rating, this.max = 5}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Row(
      children: List.generate(
        max,
        (index) => Icon(
          index < rating ? Icons.star : Icons.star_border,
          color: Colors.amber,
          size: 16,
        ),
      ),
    );
  }
}
