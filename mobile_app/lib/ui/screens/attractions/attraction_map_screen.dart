import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';

import '../../../providers/attraction_types_provider.dart';
import '../../../providers/attractions_provider.dart';
import '../../../providers/cities_provider.dart';
import '../../../core/routing/app_routes.dart';

class AttractionMapScreen extends StatefulWidget {
  const AttractionMapScreen({Key? key}) : super(key: key);

  @override
  State<AttractionMapScreen> createState() => _AttractionMapScreenState();
}

class _AttractionMapScreenState extends State<AttractionMapScreen> {
  final MapController _mapController = MapController();
  int? _selectedCityId;
  int? _selectedTypeId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<CitiesProvider>().fetchCities();
      context.read<AttractionTypesProvider>().fetchTypes();
      context.read<AttractionsProvider>().fetchAttractions();
    });
  }

  Future<void> _applyFilters() async {
    await context.read<AttractionsProvider>().fetchAttractions(
          cityId: _selectedCityId,
          typeId: _selectedTypeId,
        );
    _centerOnResults();
  }

  @override
  Widget build(BuildContext context) {
    final cities = context.watch<CitiesProvider>();
    final types = context.watch<AttractionTypesProvider>();
    final attractions = context.watch<AttractionsProvider>();

    final markers = attractions.attractions
        .map((item) => _buildMarker(context, item))
        .whereType<Marker>()
        .toList();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: Stack(
          children: [
            FlutterMap(
              mapController: _mapController,
              options: MapOptions(
                center: LatLng(15.3694, 44.1910),
                zoom: 7.0,
              ),
              children: [
                TileLayer(
                  urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                  userAgentPackageName: 'com.yemen.tourism.app',
                ),
                MarkerLayer(markers: markers),
              ],
            ),
            SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _TopBar(onBack: () => Navigator.of(context).pop()),
                    const SizedBox(height: 12),
                    _FilterPanel(
                      cities: cities.cities,
                      types: types.types,
                      selectedCityId: _selectedCityId,
                      selectedTypeId: _selectedTypeId,
                      isLoading: cities.isLoading || types.isLoading,
                      onCityChanged: (id) {
                        setState(() => _selectedCityId = id);
                        _applyFilters();
                      },
                      onTypeChanged: (id) {
                        setState(() => _selectedTypeId = id);
                        _applyFilters();
                      },
                    ),
                  ],
                ),
              ),
            ),
            if (attractions.isLoading)
              const Positioned(
                bottom: 24,
                right: 24,
                child: CircularProgressIndicator(),
              ),
          ],
        ),
      ),
    );
  }

  Marker? _buildMarker(BuildContext context, Map<String, dynamic> item) {
    final lat = _parseDouble(item['latitude']);
    final lng = _parseDouble(item['longitude']);
    if (lat == null || lng == null) return null;

    return Marker(
      point: LatLng(lat, lng),
      width: 42,
      height: 42,
      builder: (context) => GestureDetector(
        onTap: () => _showAttractionSheet(context, item),
        child: Container(
          decoration: BoxDecoration(
            color: const Color(0xFF0B172A),
            shape: BoxShape.circle,
            border: Border.all(color: const Color(0xFFFBBF24), width: 2),
            boxShadow: const [
              BoxShadow(color: Color(0x33000000), blurRadius: 8, offset: Offset(0, 4)),
            ],
          ),
          child: const Icon(Icons.place, color: Colors.white, size: 20),
        ),
      ),
    );
  }

  double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString());
  }

  void _centerOnResults() {
    final list = context.read<AttractionsProvider>().attractions;
    if (list.isEmpty) return;
    for (final item in list) {
      final lat = _parseDouble((item as Map)['latitude']);
      final lng = _parseDouble(item['longitude']);
      if (lat != null && lng != null) {
        final zoom = _selectedCityId != null ? 10.0 : (_selectedTypeId != null ? 9.0 : 7.0);
        _mapController.move(LatLng(lat, lng), zoom);
        break;
      }
    }
  }

  void _showAttractionSheet(BuildContext context, Map<String, dynamic> item) {
    final name = item['name'] ?? '';
    final city = item['city_name'] ?? '';
    final type = item['type_name'] ?? '';
    final rating = (item['avg_rating'] ?? '').toString();

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) {
        return Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 6),
              Text('$city • $type', style: const TextStyle(color: Color(0xFF6B7280))),
              const SizedBox(height: 10),
              Row(
                children: [
                  const Icon(Icons.star, color: Color(0xFFFBBF24), size: 16),
                  const SizedBox(width: 4),
                  Text(rating, style: const TextStyle(color: Color(0xFF6B7280))),
                ],
              ),
              const SizedBox(height: 14),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    final rawId = (item is Map) ? item['id'] : null;
                    final id = rawId == null ? null : int.tryParse(rawId.toString());
                    if (id == null) {
                      Navigator.of(context).pop();
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('لا يمكن فتح المعلم بدون رقم معرف.')),
                      );
                      return;
                    }
                    debugPrint('Map detail -> id=$id raw=$rawId item=$item');
                    Navigator.of(context).pop();
                    Navigator.of(context).pushNamed(
                      AppRoutes.attractionDetails,
                      arguments: {'id': id, 'attraction': item},
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B172A),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('عرض التفاصيل'),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _TopBar extends StatelessWidget {
  final VoidCallback onBack;

  const _TopBar({required this.onBack});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: const Color(0xFF0B172A).withOpacity(0.9),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          IconButton(
            onPressed: onBack,
            icon: const Icon(Icons.arrow_back, color: Colors.white),
          ),
          const Spacer(),
          const Text(
            'الخريطة السياحية',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
          const Spacer(),
          const Icon(Icons.map_outlined, color: Colors.white),
        ],
      ),
    );
  }
}

class _FilterPanel extends StatelessWidget {
  final List<dynamic> cities;
  final List<dynamic> types;
  final int? selectedCityId;
  final int? selectedTypeId;
  final bool isLoading;
  final ValueChanged<int?> onCityChanged;
  final ValueChanged<int?> onTypeChanged;

  const _FilterPanel({
    required this.cities,
    required this.types,
    required this.selectedCityId,
    required this.selectedTypeId,
    required this.isLoading,
    required this.onCityChanged,
    required this.onTypeChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: const [
          BoxShadow(color: Color(0x11000000), blurRadius: 12, offset: Offset(0, 6)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text('فلترة سريعة', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          if (isLoading) const LinearProgressIndicator(),
          const SizedBox(height: 8),
          _buildChipRow(
            items: cities,
            labelKey: 'name',
            selectedId: selectedCityId,
            onChanged: onCityChanged,
          ),
          const SizedBox(height: 8),
          _buildChipRow(
            items: types,
            labelKey: 'type_name',
            selectedId: selectedTypeId,
            onChanged: onTypeChanged,
          ),
        ],
      ),
    );
  }

  Widget _buildChipRow({
    required List<dynamic> items,
    required String labelKey,
    required int? selectedId,
    required ValueChanged<int?> onChanged,
  }) {
    return SizedBox(
      height: 36,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: items.length + 1,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          if (index == 0) {
            return ChoiceChip(
              label: const Text('الكل'),
              selected: selectedId == null,
              onSelected: (_) => onChanged(null),
              selectedColor: const Color(0xFF0B172A),
              labelStyle: TextStyle(color: selectedId == null ? Colors.white : const Color(0xFF0B172A)),
              backgroundColor: const Color(0xFFF3F4F6),
            );
          }
          final item = items[index - 1];
          final id = item['id'] as int?;
          final label = item[labelKey] ?? '';
          return ChoiceChip(
            label: Text(label.toString()),
            selected: selectedId == id,
            onSelected: (_) => onChanged(id),
            selectedColor: const Color(0xFF0B172A),
            labelStyle: TextStyle(color: selectedId == id ? Colors.white : const Color(0xFF0B172A)),
            backgroundColor: const Color(0xFFF3F4F6),
          );
        },
      ),
    );
  }
}
