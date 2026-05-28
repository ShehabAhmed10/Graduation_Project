import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../config/api_endpoints.dart';
import '../../../core/routing/app_routes.dart';
import '../../../core/utils/helpers.dart';
import '../../../providers/attractions_provider.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/favorites_provider.dart';
import '../../../providers/reviews_provider.dart';
import '../../widgets/require_login_dialog.dart';

class AttractionDetailsScreen extends StatefulWidget {
  const AttractionDetailsScreen({Key? key}) : super(key: key);

  @override
  State<AttractionDetailsScreen> createState() => _AttractionDetailsScreenState();
}

class _AttractionDetailsScreenState extends State<AttractionDetailsScreen> {
  int? _attractionId;
  bool _loaded = false;
  String? _loadError;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_loaded) return;
    _loaded = true;
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    _attractionId = _parseId(args?['id']);
    if (_attractionId == null && args?['attraction'] is Map<String, dynamic>) {
      _attractionId = _parseId((args?['attraction'] as Map<String, dynamic>)['id']);
    }
    if (_attractionId != null) {
      context.read<AttractionsProvider>().fetchDetails(_attractionId!);
      if (context.read<AuthProvider>().isLoggedIn) {
        context.read<FavoritesProvider>().fetchFavorites();
      }
    } else {
      _loadError = 'لا يمكن عرض المعلم بدون رقم معرف.';
    }
  }

  @override
  Widget build(BuildContext context) {
    final attractions = context.watch<AttractionsProvider>();
    final auth = context.watch<AuthProvider>();
    final favorites = context.watch<FavoritesProvider>();

    final data = attractions.selectedAttraction as Map<String, dynamic>? ?? {};
    final attraction = data['attraction'] as Map<String, dynamic>? ?? {};
    final images = (data['images'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
    final comments = (data['comments'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
    final nearbyHotels = (data['nearby_hotels'] as List<dynamic>? ?? []).cast<Map<String, dynamic>>();
    final userReview = data['user_review'] as Map<String, dynamic>?;

    final name = attraction['name'] ?? '';
    final city = attraction['city_name'] ?? '';
    final type = attraction['type_name'] ?? '';
    final description = attraction['description'] ?? '';
    final rating = (attraction['avg_rating'] ?? '').toString();
    final mainImage = _resolveImage(attraction['main_image_url']?.toString());
    final isFavorited = _attractionId != null && favorites.isFavorited(_attractionId!);

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: attractions.isLoading
            ? const Center(child: CircularProgressIndicator())
            : _loadError != null
                ? Center(child: Text(_loadError!))
                : attraction.isEmpty
                    ? const Center(child: Text('لا توجد بيانات للمعلم.'))
                    : CustomScrollView(
                        slivers: [
                          SliverAppBar(
                            pinned: true,
                            expandedHeight: 260,
                            backgroundColor: const Color(0xFF0B172A),
                            flexibleSpace: FlexibleSpaceBar(
                              background: Stack(
                                fit: StackFit.expand,
                                children: [
                                  mainImage == null
                                      ? Container(
                                          color: const Color(0xFF0B172A),
                                          child: const Icon(Icons.landscape, color: Colors.white70, size: 64),
                                        )
                                      : Image.network(mainImage, fit: BoxFit.cover),
                                  Container(
                                    decoration: const BoxDecoration(
                                      gradient: LinearGradient(
                                        colors: [Colors.black54, Colors.transparent],
                                        begin: Alignment.bottomCenter,
                                        end: Alignment.topCenter,
                                      ),
                                    ),
                                  ),
                                  Positioned(
                                    top: 16,
                                    right: 16,
                                    child: IconButton(
                                      icon: const Icon(Icons.arrow_back, color: Colors.white),
                                      onPressed: () => Navigator.of(context).pop(),
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
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(name, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 6),
                                  Text('$city • $type', style: const TextStyle(color: Color(0xFF6B7280))),
                                  const SizedBox(height: 10),
                                  Row(
                                    children: [
                                      const Icon(Icons.star, color: Color(0xFFFBBF24), size: 18),
                                      const SizedBox(width: 6),
                                      Text(rating, style: const TextStyle(color: Color(0xFF6B7280))),
                                    ],
                                  ),
                                  const SizedBox(height: 18),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: ElevatedButton.icon(
                                          onPressed: () {
                                            if (!auth.isLoggedIn) {
                                              showDialog(
                                                context: context,
                                                builder: (_) => RequireLoginDialog(
                                                  onLogin: () => Navigator.of(context)
                                                      .pushReplacementNamed(AppRoutes.login),
                                                ),
                                              );
                                              return;
                                            }
                                            if (_attractionId != null) {
                                              context
                                                  .read<FavoritesProvider>()
                                                  .toggleFavorite(_attractionId!)
                                                  .then((ok) {
                                                if (!mounted) return;
                                                if (ok) {
                                                  showAppSnackBar(context, 'تم تحديث المفضلة');
                                                } else {
                                                  final err = context.read<FavoritesProvider>().lastError ??
                                                      'تعذر تحديث المفضلة.';
                                                  showAppSnackBar(context, err);
                                                }
                                              });
                                            }
                                          },
                                          icon: Icon(isFavorited ? Icons.favorite : Icons.favorite_border),
                                          label: const Text('المفضلة'),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: const Color(0xFF0B172A),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: OutlinedButton.icon(
                                          onPressed: () {
                                            if (!auth.isLoggedIn) {
                                              showDialog(
                                                context: context,
                                                builder: (_) => RequireLoginDialog(
                                                  onLogin: () => Navigator.of(context)
                                                      .pushReplacementNamed(AppRoutes.login),
                                                ),
                                              );
                                              return;
                                            }
                                            if (_attractionId != null) {
                                              Navigator.of(context)
                                                  .pushNamed(
                                                    AppRoutes.addReview,
                                                    arguments: {
                                                      'id': _attractionId,
                                                      'rating': userReview?['rating'],
                                                      'isEdit': userReview != null,
                                                    },
                                                  )
                                                  .then((value) {
                                                if (value == true && _attractionId != null) {
                                                  context.read<AttractionsProvider>().fetchDetails(_attractionId!);
                                                }
                                              });
                                            }
                                          },
                                          icon: const Icon(Icons.rate_review),
                                          label: Text(userReview == null ? 'إضافة تقييم' : 'تعديل التقييم'),
                                          style: OutlinedButton.styleFrom(
                                            foregroundColor: const Color(0xFF14B8A6),
                                            side: const BorderSide(color: Color(0xFF14B8A6)),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: OutlinedButton.icon(
                                          onPressed: () {
                                            if (!auth.isLoggedIn) {
                                              showDialog(
                                                context: context,
                                                builder: (_) => RequireLoginDialog(
                                                  onLogin: () => Navigator.of(context)
                                                      .pushReplacementNamed(AppRoutes.login),
                                                ),
                                              );
                                              return;
                                            }
                                            if (_attractionId != null) {
                                              Navigator.of(context)
                                                  .pushNamed(
                                                    AppRoutes.addComment,
                                                    arguments: {'id': _attractionId},
                                                  )
                                                  .then((value) {
                                                if (value == true && _attractionId != null) {
                                                  context.read<AttractionsProvider>().fetchDetails(_attractionId!);
                                                }
                                              });
                                            }
                                          },
                                          icon: const Icon(Icons.chat_bubble_outline),
                                          label: const Text('إضافة تعليق'),
                                        ),
                                      ),
                                      if (userReview != null) ...[
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: OutlinedButton.icon(
                                            onPressed: () {
                                              if (_attractionId == null) return;
                                              context
                                                  .read<ReviewsProvider>()
                                                  .deleteReview(_attractionId!)
                                                  .then((ok) {
                                                if (!mounted) return;
                                                if (ok) {
                                                  showAppSnackBar(context, 'تم حذف التقييم');
                                                  context.read<AttractionsProvider>().fetchDetails(_attractionId!);
                                                } else {
                                                  final err = context.read<ReviewsProvider>().lastError ??
                                                      'تعذر حذف التقييم.';
                                                  showAppSnackBar(context, err);
                                                }
                                              });
                                            },
                                            icon: const Icon(Icons.delete_outline),
                                            label: const Text('حذف التقييم'),
                                            style: OutlinedButton.styleFrom(
                                              foregroundColor: const Color(0xFFEF4444),
                                              side: const BorderSide(color: Color(0xFFEF4444)),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                  const SizedBox(height: 12),
                                  SizedBox(
                                    width: double.infinity,
                                    child: OutlinedButton.icon(
                                      onPressed: () => Navigator.of(context).pushNamed(AppRoutes.attractionMap),
                                      icon: const Icon(Icons.map_outlined),
                                      label: const Text('عرض على الخريطة'),
                                    ),
                                  ),
                                  const SizedBox(height: 20),
                                  const Text('الوصف', style: TextStyle(fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 8),
                                  Text(description.isEmpty ? 'لا يوجد وصف متاح حالياً.' : description),
                                  const SizedBox(height: 20),
                                  const Text('صور المعلم', style: TextStyle(fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 10),
                                  SizedBox(
                                    height: 110,
                                    child: images.isEmpty
                                        ? const Center(child: Text('لا توجد صور إضافية'))
                                        : ListView.separated(
                                            scrollDirection: Axis.horizontal,
                                            itemCount: images.length,
                                            separatorBuilder: (_, __) => const SizedBox(width: 10),
                                            itemBuilder: (context, index) {
                                              final url = _resolveImage(images[index]['image_url']?.toString());
                                              return ClipRRect(
                                                borderRadius: BorderRadius.circular(12),
                                                child: url == null
                                                    ? Container(
                                                        width: 140,
                                                        color: const Color(0xFF0B172A),
                                                        child: const Icon(Icons.photo, color: Colors.white70),
                                                      )
                                                    : Image.network(url, width: 140, fit: BoxFit.cover),
                                              );
                                            },
                                          ),
                                  ),
                                  const SizedBox(height: 20),
                                  const Text('التعليقات', style: TextStyle(fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 8),
                                  if (comments.isEmpty)
                                    const Text('لا توجد تعليقات بعد')
                                  else
                                    Column(
                                      children: comments.map((comment) {
                                        return _CommentTile(data: comment);
                                      }).toList(),
                                    ),
                                  const SizedBox(height: 20),
                                  const Text('فنادق قريبة', style: TextStyle(fontWeight: FontWeight.bold)),
                                  const SizedBox(height: 8),
                                  if (nearbyHotels.isEmpty)
                                    const Text('لا توجد فنادق قريبة')
                                  else
                                    SizedBox(
                                      height: 140,
                                      child: ListView.separated(
                                        scrollDirection: Axis.horizontal,
                                        itemCount: nearbyHotels.length,
                                        separatorBuilder: (_, __) => const SizedBox(width: 12),
                                        itemBuilder: (context, index) {
                                          final hotel = nearbyHotels[index];
                                          return _HotelCard(
                                            data: hotel,
                                            onTap: () => Navigator.of(context).pushNamed(
                                              AppRoutes.hotelDetails,
                                              arguments: {'hotel': hotel},
                                            ),
                                          );
                                        },
                                      ),
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

  int? _parseId(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    return int.tryParse(value.toString());
  }
}

class _CommentTile extends StatelessWidget {
  final Map<String, dynamic> data;

  const _CommentTile({required this.data});

  @override
  Widget build(BuildContext context) {
    final comment = data['comment'] ?? '';
    final user = data['user_name'] ?? '';
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(color: Color(0x11000000), blurRadius: 8, offset: Offset(0, 4)),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: const BoxDecoration(
              color: Color(0xFF0B172A),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.person, color: Colors.white, size: 18),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(user, style: const TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text(comment, maxLines: 3, overflow: TextOverflow.ellipsis),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HotelCard extends StatelessWidget {
  final Map<String, dynamic> data;
  final VoidCallback onTap;

  const _HotelCard({required this.data, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final name = data['name'] ?? '';
    final imagePath = data['main_image_url']?.toString();
    final uploadsBase = ApiEndpoints.baseUrl.replaceFirst('/api', '/uploads');
    final imageUrl = imagePath == null || imagePath.isEmpty
        ? null
        : (imagePath.startsWith('http') ? imagePath : '$uploadsBase/$imagePath');

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        width: 160,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [
            BoxShadow(color: Color(0x11000000), blurRadius: 10, offset: Offset(0, 6)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
              child: SizedBox(
                height: 80,
                width: double.infinity,
                child: imageUrl == null
                    ? Container(
                        color: const Color(0xFF0B172A),
                        child: const Icon(Icons.hotel, color: Colors.white70),
                      )
                    : Image.network(imageUrl, fit: BoxFit.cover),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Text(
                name,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
