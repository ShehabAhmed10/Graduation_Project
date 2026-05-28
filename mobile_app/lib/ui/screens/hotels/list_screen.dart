import 'package:flutter/material.dart';

class HotelsListScreen extends StatelessWidget {
  const HotelsListScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Hotels')),
      body: const Center(child: Text('Hotels list')),
    );
  }
}
