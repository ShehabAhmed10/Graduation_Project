import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/routing/app_routes.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/favorites_provider.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({Key? key}) : super(key: key);

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      if (auth.isLoggedIn) {
        context.read<FavoritesProvider>().fetchFavorites();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final favorites = context.watch<FavoritesProvider>();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(title: const Text('المفضلة')),
        body: !auth.isLoggedIn
            ? _GuestState(onLogin: () => Navigator.of(context).pushNamed(AppRoutes.login))
            : favorites.isLoading
                ? const Center(child: CircularProgressIndicator())
                : favorites.favorites.isEmpty
                    ? const Center(child: Text('لا توجد عناصر مفضلة حالياً'))
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: favorites.favorites.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
                        itemBuilder: (context, index) {
                          final item = favorites.favorites[index] as Map<String, dynamic>;
                          final id = item['id'];
                          return _FavoriteCard(
                            name: item['name'] ?? '',
                            city: item['city_name'] ?? '',
                            type: item['type_name'] ?? '',
                            onRemove: () {
                              final parsed = id == null ? null : int.tryParse(id.toString());
                              if (parsed != null) {
                                favorites.toggleFavorite(parsed);
                              }
                            },
                            onTap: () {
                              final parsed = id == null ? null : int.tryParse(id.toString());
                              if (parsed != null) {
                                Navigator.of(context).pushNamed(
                                  AppRoutes.attractionDetails,
                                  arguments: {'id': parsed, 'attraction': item},
                                );
                              }
                            },
                          );
                        },
                      ),
      ),
    );
  }
}

class _GuestState extends StatelessWidget {
  final VoidCallback onLogin;

  const _GuestState({required this.onLogin});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text('لتتمكن من استخدام المفضلة، قم بتسجيل الدخول أولاً.'),
          const SizedBox(height: 12),
          ElevatedButton(
            onPressed: onLogin,
            child: const Text('تسجيل الدخول'),
          ),
        ],
      ),
    );
  }
}

class _FavoriteCard extends StatelessWidget {
  final String name;
  final String city;
  final String type;
  final VoidCallback onRemove;
  final VoidCallback onTap;

  const _FavoriteCard({
    required this.name,
    required this.city,
    required this.type,
    required this.onRemove,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [
            BoxShadow(color: Color(0x11000000), blurRadius: 12, offset: Offset(0, 6)),
          ],
        ),
        child: Row(
          children: [
            const CircleAvatar(
              radius: 18,
              backgroundColor: Color(0xFF0B172A),
              child: Icon(Icons.favorite, color: Colors.white, size: 16),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name, style: const TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text('$city • $type', style: const TextStyle(color: Color(0xFF6B7280), fontSize: 12)),
                ],
              ),
            ),
            IconButton(
              onPressed: onRemove,
              icon: const Icon(Icons.delete_outline, color: Color(0xFFB91C1C)),
            ),
          ],
        ),
      ),
    );
  }
}
