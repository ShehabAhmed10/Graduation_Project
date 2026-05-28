import 'package:flutter/material.dart';

class EmptyState extends StatelessWidget {
  final String message;
  final Widget? action;

  const EmptyState({Key? key, required this.message, this.action}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(message),
          if (action != null) ...[
            const SizedBox(height: 12),
            action!,
          ]
        ],
      ),
    );
  }
}
