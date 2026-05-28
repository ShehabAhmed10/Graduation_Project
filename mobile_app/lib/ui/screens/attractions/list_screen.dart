import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../config/api_endpoints.dart';
import '../../../core/routing/app_routes.dart';
import '../../../providers/attraction_types_provider.dart';
import '../../../providers/attractions_provider.dart';
import '../../../providers/cities_provider.dart';

class AttractionsListScreen extends StatefulWidget {
  const AttractionsListScreen({Key? key}) : super(key: key);

  @override
  State<AttractionsListScreen> createState() => _AttractionsListScreenState();
}

class _AttractionsListScreenState extends State<AttractionsListScreen> {
  final _searchController = TextEditingController();
  int? _selectedCityId;
  int? _selectedTypeId;
  bool _initialized = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<CitiesProvider>().fetchCities();
      context.read<AttractionTypesProvider>().fetchTypes();
    });
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_initialized) return;
    _initialized = true;
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    if (args != null) {
      _selectedCityId = args['cityId'] as int?;
      _selectedTypeId = args['typeId'] as int?;
      final search = args['search'] as String?;
      if (search != null) _searchController.text = search;
    }
    _applyFilters();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _applyFilters() async {
    await context.read<AttractionsProvider>().fetchAttractions(
          cityId: _selectedCityId,
          typeId: _selectedTypeId,
          search: _searchController.text.trim(),
        );
  }

  @override
  Widget build(BuildContext context) {
    final cities = context.watch<CitiesProvider>();
    final types = context.watch<AttractionTypesProvider>();
    final attractions = context.watch<AttractionsProvider>();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: RefreshIndicator(
          onRefresh: _applyFilters,
          child: CustomScrollView(
            slivers: [
              SliverAppBar(
                pinned: true,
                expandedHeight: 200,
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
                        top: -30,
                        right: -30,
                        child: _GlowCircle(size: 140, color: Color(0xFF14B8A6)),
                      ),
                      Positioned(
                        bottom: -40,
                        left: -40,
                        child: _GlowCircle(size: 160, color: Color(0xFFD4AF37)),
                      ),
                      Padding(
                        padding: const EdgeInsets.fromLTRB(20, 24, 20, 16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: const [
                                Icon(Icons.arrow_back, color: Colors.white),
                                Text(
                                  'المعالم السياحية',
                                  style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                                ),
                                Icon(Icons.filter_list, color: Colors.white),
                              ],
                            ),
                            const Spacer(),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
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
                                      style: const TextStyle(color: Colors.white),
                                      decoration: const InputDecoration(
                                        hintText: 'ابحث عن معلم سياحي...',
                                        hintStyle: TextStyle(color: Colors.white70),
                                        border: InputBorder.none,
                                      ),
                                      textInputAction: TextInputAction.search,
                                      onSubmitted: (_) => _applyFilters(),
                                    ),
                                  ),
                                  IconButton(
                                    onPressed: _applyFilters,
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
                      const Text(
                        'فلترة حسب المدينة',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 10),
                      SizedBox(
                        height: 44,
                        child: cities.isLoading
                            ? const Center(child: CircularProgressIndicator())
                            : ListView.separated(
                                scrollDirection: Axis.horizontal,
                                itemCount: cities.cities.length + 1,
                                separatorBuilder: (_, __) => const SizedBox(width: 8),
                                itemBuilder: (context, index) {
                                  if (index == 0) {
                                    return _FilterChip(
                                      label: 'الكل',
                                      selected: _selectedCityId == null,
                                      onTap: () {
                                        setState(() => _selectedCityId = null);
                                        _applyFilters();
                                      },
                                    );
                                  }
                                  final city = cities.cities[index - 1];
                                  final id = city['id'] as int?;
                                  return _FilterChip(
                                    label: city['name'] ?? '',
                                    selected: _selectedCityId == id,
                                    onTap: () {
                                      setState(() => _selectedCityId = id);
                                      _applyFilters();
                                    },
                                  );
                                },
                              ),
                      ),
                      const SizedBox(height: 18),
                      const Text(
                        'فلترة حسب النوع',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 10),
                      SizedBox(
                        height: 44,
                        child: types.isLoading
                            ? const Center(child: CircularProgressIndicator())
                            : ListView.separated(
                                scrollDirection: Axis.horizontal,
                                itemCount: types.types.length + 1,
                                separatorBuilder: (_, __) => const SizedBox(width: 8),
                                itemBuilder: (context, index) {
                                  if (index == 0) {
                                    return _FilterChip(
                                      label: 'الكل',
                                      selected: _selectedTypeId == null,
                                      onTap: () {
                                        setState(() => _selectedTypeId = null);
                                        _applyFilters();
                                      },
                                    );
                                  }
                                  final type = types.types[index - 1];
                                  final id = type['id'] as int?;
                                  return _FilterChip(
                                    label: type['type_name'] ?? '',
                                    selected: _selectedTypeId == id,
                                    onTap: () {
                                      setState(() => _selectedTypeId = id);
                                      _applyFilters();
                                    },
                                  );
                                },
                              ),
                      ),
                      const SizedBox(height: 18),
                      if (attractions.isLoading)
                        const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator()))
                      else if (attractions.attractions.isEmpty)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.all(24),
                            child: Text('لا توجد معالم مطابقة للبحث'),
                          ),
                        )
                      else
                        Column(
                          children: attractions.attractions.map((item) {
                            return _AttractionTile(
                              data: item,
                              onTap: () {
                                final rawId = (item is Map) ? item['id'] : null;
                                final id = rawId == null ? null : int.tryParse(rawId.toString());
                                if (id == null) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    const SnackBar(content: Text('لا يمكن فتح المعلم بدون رقم معرف.')),
                                  );
                                  return;
                                }
                                debugPrint('Attraction tap -> id=$id raw=$rawId item=$item');
                                Navigator.of(context).pushNamed(
                                  AppRoutes.attractionDetails,
                                  arguments: {'id': id, 'attraction': item},
                                );
                              },
                            );
                          }).toList(),
                        ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
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

class _FilterChip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _FilterChip({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return ChoiceChip(
      label: Text(label),
      selected: selected,
      onSelected: (_) => onTap(),
      selectedColor: const Color(0xFF0B172A),
      labelStyle: TextStyle(color: selected ? Colors.white : const Color(0xFF0B172A)),
      backgroundColor: const Color(0xFFF3F4F6),
    );
  }
}

class _AttractionTile extends StatelessWidget {
  final Map<String, dynamic> data;
  final VoidCallback? onTap;

  const _AttractionTile({required this.data, this.onTap});

  @override
  Widget build(BuildContext context) {
    final name = data['name'] ?? '';
    final city = data['city_name'] ?? '';
    final type = data['type_name'] ?? '';
    final rating = (data['avg_rating'] ?? '').toString();
    final short = data['short_description'] ?? '';
    final imagePath = data['main_image_url']?.toString();
    final uploadsBase = ApiEndpoints.baseUrl.replaceFirst('/api', '/uploads');
    final imageUrl = imagePath == null || imagePath.isEmpty
        ? null
        : (imagePath.startsWith('http') ? imagePath : '$uploadsBase/$imagePath');

    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(22),
      child: Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        boxShadow: const [
          BoxShadow(color: Color(0x11000000), blurRadius: 14, offset: Offset(0, 8)),
        ],
      ),
      child: Row(
        children: [
          ClipRRect(
            borderRadius: const BorderRadius.horizontal(right: Radius.circular(22)),
            child: SizedBox(
              width: 110,
              height: 120,
              child: imageUrl == null
                  ? Container(
                      color: const Color(0xFF0B172A),
                      child: const Icon(Icons.landscape, color: Colors.white70),
                    )
                  : Image.network(imageUrl, fit: BoxFit.cover),
            ),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(name, style: const TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text('$city • $type', style: const TextStyle(color: Color(0xFF6B7280), fontSize: 12)),
                  const SizedBox(height: 6),
                  Text(short, maxLines: 2, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.star, color: Color(0xFFFBBF24), size: 16),
                      const SizedBox(width: 4),
                      Text(rating, style: const TextStyle(color: Color(0xFF6B7280), fontSize: 12)),
                    ],
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
}
