import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/routing/app_routes.dart';
import '../../../config/api_endpoints.dart';
import '../../../providers/attraction_types_provider.dart';
import '../../../providers/attractions_provider.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/cities_provider.dart';
import '../../../providers/notifications_provider.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _searchController = TextEditingController();
  final _searchFocus = FocusNode();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<CitiesProvider>().fetchCities();
      context.read<AttractionsProvider>().fetchFeatured();
      context.read<AttractionTypesProvider>().fetchTypes();
      if (context.read<AuthProvider>().isLoggedIn) {
        context.read<NotificationsProvider>().fetchNotifications();
      }
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _searchFocus.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final cities = context.watch<CitiesProvider>();
    final attractions = context.watch<AttractionsProvider>();
    final types = context.watch<AttractionTypesProvider>();
    final notifications = context.watch<NotificationsProvider>();
    final auth = context.watch<AuthProvider>();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: CustomScrollView(
          slivers: [
            SliverAppBar(
              pinned: true,
              expandedHeight: 260,
              backgroundColor: const Color(0xFF0B172A),
              flexibleSpace: FlexibleSpaceBar(
                background: Stack(
                  children: [
                    Container(
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xFF0B172A), Color(0xFF1F2937)],
                          begin: Alignment.topRight,
                          end: Alignment.bottomLeft,
                        ),
                      ),
                    ),
                    Positioned(
                      top: -40,
                      right: -30,
                      child: _GlowCircle(size: 160, color: Color(0xFF14B8A6)),
                    ),
                    Positioned(
                      top: 120,
                      left: -50,
                      child: _GlowCircle(size: 220, color: Color(0xFFD4AF37)),
                    ),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 24, 20, 16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Icon(Icons.menu, color: Colors.white),
                              Stack(
                                children: [
                                  const Icon(Icons.notifications_none, color: Colors.white),
                                  if (notifications.unreadCount > 0)
                                    Positioned(
                                      right: 0,
                                      top: 0,
                                      child: Container(
                                        width: 10,
                                        height: 10,
                                        decoration: const BoxDecoration(
                                          color: Color(0xFFFBBF24),
                                          shape: BoxShape.circle,
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            ],
                          ),
                          const Spacer(),
                          const Text(
                            'مرحباً بك',
                            style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 14),
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'استكشف اليمن السياحية',
                            style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 16),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: Colors.white24),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.search, color: Colors.white70),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: TextField(
                                    controller: _searchController,
                                    focusNode: _searchFocus,
                                    style: const TextStyle(color: Colors.white),
                                    decoration: const InputDecoration(
                                      hintText: 'ابحث عن مدينة أو معلم سياحي...',
                                      hintStyle: TextStyle(color: Colors.white70),
                                      border: InputBorder.none,
                                    ),
                                    textInputAction: TextInputAction.search,
                                    onSubmitted: (value) {
                                      Navigator.of(context).pushNamed(
                                        AppRoutes.attractionsList,
                                        arguments: {'search': value.trim()},
                                      );
                                    },
                                  ),
                                ),
                                IconButton(
                                  onPressed: () {
                                    Navigator.of(context).pushNamed(
                                      AppRoutes.attractionsList,
                                      arguments: {'search': _searchController.text.trim()},
                                    );
                                  },
                                  icon: const Icon(Icons.tune, color: Colors.white70),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (!auth.isLoggedIn)
                      Container(
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x11000000),
                              blurRadius: 16,
                              offset: Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'تجربة أرقى مع حسابك',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 8),
                            const Text(
                              'سجّل الدخول لإضافة المفضلة والتقييمات وحفظ تفضيلاتك.',
                              style: TextStyle(color: Color(0xFF6B7280)),
                            ),
                            const SizedBox(height: 16),
                            Row(
                              children: [
                                Expanded(
                                  child: ElevatedButton(
                                    onPressed: () => Navigator.of(context).pushNamed(AppRoutes.login),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF0B172A),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(24),
                                      ),
                                    ),
                                    child: const Text('تسجيل الدخول'),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: OutlinedButton(
                                    onPressed: () => Navigator.of(context).pushNamed(AppRoutes.register),
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: const Color(0xFF14B8A6),
                                      side: const BorderSide(color: Color(0xFF14B8A6)),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(24),
                                      ),
                                    ),
                                    child: const Text('إنشاء حساب'),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x11000000),
                              blurRadius: 16,
                              offset: Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            const CircleAvatar(
                              radius: 18,
                              backgroundColor: Color(0xFF0B172A),
                              child: Icon(Icons.person, color: Colors.white, size: 18),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                'مرحباً ${auth.currentUser?['full_name'] ?? ''}',
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                            ),
                          ],
                        ),
                      ),
                    const SizedBox(height: 24),
                    _SectionHeader(
                      title: 'المدن السياحية',
                      action: 'عرض الكل',
                      onTap: () => Navigator.of(context).pushNamed(AppRoutes.attractionsList),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      height: 120,
                      child: cities.isLoading
                          ? const Center(child: CircularProgressIndicator())
                          : ListView.separated(
                              scrollDirection: Axis.horizontal,
                              itemCount: cities.cities.length,
                              separatorBuilder: (_, __) => const SizedBox(width: 12),
                              itemBuilder: (context, index) {
                                final city = cities.cities[index];
                                return _CityCard(
                                  title: city['name'] ?? '',
                                  onTap: () => Navigator.of(context).pushNamed(
                                    AppRoutes.attractionsList,
                                    arguments: {'cityId': city['id']},
                                  ),
                                );
                              },
                            ),
                    ),
                    const SizedBox(height: 24),
                    _SectionHeader(
                      title: 'معالم مميزة',
                      action: 'المزيد',
                      onTap: () => Navigator.of(context).pushNamed(AppRoutes.attractionsList),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      height: 210,
                      child: attractions.isLoading
                          ? const Center(child: CircularProgressIndicator())
                          : ListView.separated(
                              scrollDirection: Axis.horizontal,
                              itemCount: attractions.featuredAttractions.length,
                              separatorBuilder: (_, __) => const SizedBox(width: 14),
                              itemBuilder: (context, index) {
                                final item = attractions.featuredAttractions[index];
                                return _FeaturedCard(
                                  title: item['name'] ?? '',
                                  city: item['city_name'] ?? '',
                                  rating: (item['avg_rating'] ?? '').toString(),
                                  imageUrl: _resolveImage(item['main_image_url']?.toString()),
                                  onTap: () => Navigator.of(context).pushNamed(
                                    AppRoutes.attractionDetails,
                                    arguments: {'id': item['id'], 'attraction': item},
                                  ),
                                );
                              },
                            ),
                    ),
                    const SizedBox(height: 24),
                    const Text(
                      'استكشف حسب النوع',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 12),
                    GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 3,
                        mainAxisSpacing: 12,
                        crossAxisSpacing: 12,
                        childAspectRatio: 1,
                      ),
                      itemCount: types.types.length,
                      itemBuilder: (context, index) {
                        final type = types.types[index];
                        return _CategoryCard(
                          title: type['type_name'] ?? '',
                          icon: Icons.account_balance,
                          onTap: () => Navigator.of(context).pushNamed(
                            AppRoutes.attractionsList,
                            arguments: {'typeId': type['id']},
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String? _resolveImage(String? path) {
    if (path == null || path.isEmpty) return null;
    if (path.startsWith('http')) return path;
    final uploadsBase = ApiEndpoints.baseUrl.replaceFirst('/api', '/uploads');
    return '$uploadsBase/$path';
  }
}

class _GlowCircle extends StatelessWidget {
  final double size;
  final Color color;

  const _GlowCircle({required this.size, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: color.withOpacity(0.16),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  final String action;
  final VoidCallback? onTap;

  const _SectionHeader({required this.title, required this.action, this.onTap});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        InkWell(
          onTap: onTap,
          child: Text(action, style: const TextStyle(color: Color(0xFF14B8A6))),
        ),
      ],
    );
  }
}

class _CityCard extends StatelessWidget {
  final String title;
  final VoidCallback? onTap;

  const _CityCard({required this.title, this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        width: 130,
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF0B172A), Color(0xFF14B8A6)],
            begin: Alignment.topRight,
            end: Alignment.bottomLeft,
          ),
          borderRadius: BorderRadius.circular(20),
          boxShadow: const [
            BoxShadow(color: Color(0x11000000), blurRadius: 12, offset: Offset(0, 6)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: Colors.white24,
              child: Icon(Icons.location_city, color: Colors.white),
            ),
            Spacer(),
            Text(
              title,
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
            ),
            const Text(
              'معالم متنوعة',
              style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 12),
            ),
          ],
        ),
      ),
    );
  }
}

class _FeaturedCard extends StatelessWidget {
  final String title;
  final String city;
  final String rating;
  final String? imageUrl;
  final VoidCallback? onTap;

  const _FeaturedCard({
    required this.title,
    required this.city,
    required this.rating,
    this.imageUrl,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(22),
      child: Container(
        width: 220,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(22),
          boxShadow: const [
            BoxShadow(color: Color(0x11000000), blurRadius: 14, offset: Offset(0, 8)),
          ],
        ),
        child: Stack(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(22),
              child: imageUrl == null
                  ? Container(
                      color: const Color(0xFF1F2937),
                      child: const Icon(Icons.landscape, color: Colors.white70, size: 40),
                    )
                  : Image.network(imageUrl!, fit: BoxFit.cover),
            ),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(22),
                gradient: const LinearGradient(
                  colors: [Colors.black54, Colors.transparent],
                  begin: Alignment.bottomCenter,
                  end: Alignment.topCenter,
                ),
              ),
            ),
            Positioned(
              top: 12,
              right: 12,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.star, color: Color(0xFFFBBF24), size: 16),
                    const SizedBox(width: 4),
                    Text(rating, style: const TextStyle(color: Colors.white)),
                  ],
                ),
              ),
            ),
            Positioned(
              bottom: 16,
              right: 16,
              left: 16,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(city, style: const TextStyle(color: Color(0xFF9CA3AF))),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CategoryCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final VoidCallback? onTap;

  const _CategoryCard({required this.title, required this.icon, this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: const [
            BoxShadow(color: Color(0x11000000), blurRadius: 12, offset: Offset(0, 6)),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircleAvatar(
              radius: 20,
              backgroundColor: const Color(0xFF0B172A),
              child: Icon(icon, color: const Color(0xFFFBBF24), size: 20),
            ),
            const SizedBox(height: 8),
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}
