import 'package:flutter/material.dart';

class AppTheme {
  static final ThemeData lightTheme = ThemeData(
    primaryColor: const Color(0xFF0B172A),
    scaffoldBackgroundColor: Colors.white,
    colorScheme: ColorScheme.fromSeed(
      seedColor: const Color(0xFF0B172A),
      primary: const Color(0xFF0B172A),
      secondary: const Color(0xFF14B8A6),
    ),
    visualDensity: VisualDensity.adaptivePlatformDensity,
  );
}
