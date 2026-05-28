import 'package:flutter/material.dart';

import '../home/home_screen.dart';
import '../attractions/list_screen.dart';
import '../attractions/attraction_map_screen.dart';
import '../favorites/favorites_screen.dart';
import '../profile/profile_screen.dart';


class MainLayout extends StatefulWidget {
  const MainLayout({Key? key}) : super(key: key);

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  int _index = 0;

  final _pages = const [
    HomeScreen(),
    AttractionsListScreen(),
    AttractionMapScreen(),
    FavoritesScreen(),
    ProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _pages[_index],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _index,
        onTap: (value) => setState(() => _index = value),
        type: BottomNavigationBarType.fixed,
        selectedItemColor: const Color(0xFF14B8A6),
        unselectedItemColor: const Color(0xFF9CA3AF),
        backgroundColor: Colors.white,
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'الرئيسية'),
          BottomNavigationBarItem(icon: Icon(Icons.place), label: 'المعالم'),
          BottomNavigationBarItem(icon: Icon(Icons.map_outlined), label: 'الخريطة'),
          BottomNavigationBarItem(icon: Icon(Icons.favorite), label: 'المفضلة'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'حسابي'),
        ],
      ),
    );
  }
}
