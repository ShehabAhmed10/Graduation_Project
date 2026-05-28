import 'package:flutter/material.dart';

class UploadReviewScreen extends StatelessWidget {
  const UploadReviewScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Upload Review')),
      body: const Center(child: Text('Upload review')),
    );
  }
}
