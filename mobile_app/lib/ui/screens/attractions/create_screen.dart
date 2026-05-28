import 'package:flutter/material.dart';

class AttractionCreateScreen extends StatelessWidget {
  const AttractionCreateScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Create Attraction')),
      body: const Center(child: Text('Create')),
    );
  }
}
