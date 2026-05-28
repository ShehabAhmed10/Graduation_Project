import 'package:flutter/material.dart';

class RequireLoginDialog extends StatelessWidget {
  final VoidCallback onLogin;

  const RequireLoginDialog({Key? key, required this.onLogin}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Login required'),
      content: const Text('This feature requires login.'),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: onLogin,
          child: const Text('Login'),
        ),
      ],
    );
  }
}
