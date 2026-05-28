import 'package:flutter/material.dart';

class CityChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback? onTap;

  const CityChip({Key? key, required this.label, this.selected = false, this.onTap}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ChoiceChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) => onTap?.call(),
    );
  }
}
