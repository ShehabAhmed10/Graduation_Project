import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../config/api_endpoints.dart';
import '../../../core/utils/helpers.dart';
import '../../../providers/hotels_provider.dart';

class HotelDetailsScreen extends StatefulWidget {
  const HotelDetailsScreen({Key? key}) : super(key: key);

  @override
  State<HotelDetailsScreen> createState() => _HotelDetailsScreenState();
}

class _HotelDetailsScreenState extends State<HotelDetailsScreen> {
  Map<String, dynamic>? _hotel;
  int? _hotelId;
  bool _loaded = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_loaded) return;
    _loaded = true;
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    _hotel = args?['hotel'] as Map<String, dynamic>?;
    _hotelId = args?['id'] as int? ?? _hotel?['id'] as int?;
    if (_hotelId != null && _hotel == null) {
      context.read<HotelsProvider>().fetchDetails(_hotelId!);
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<HotelsProvider>();
    final data = _hotel ?? (provider.selectedHotel as Map<String, dynamic>? ?? {});
    final hotel = data['hotel'] as Map<String, dynamic>? ?? data;

    final name = hotel['name'] ?? '';
    final description = hotel['description'] ?? '';
    final phone = hotel['phone']?.toString() ?? '';
    final whatsapp = hotel['whatsapp']?.toString() ?? '';
    final website = hotel['website_url']?.toString() ?? '';
    final imageUrl = _resolveImage(hotel['main_image_url']?.toString());

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: provider.isLoading
            ? const Center(child: CircularProgressIndicator())
            : CustomScrollView(
                slivers: [
                  SliverAppBar(
                    pinned: true,
                    expandedHeight: 240,
                    backgroundColor: const Color(0xFF0B172A),
                    flexibleSpace: FlexibleSpaceBar(
                      background: Stack(
                        fit: StackFit.expand,
                        children: [
                          imageUrl == null
                              ? Container(
                                  color: const Color(0xFF0B172A),
                                  child: const Icon(Icons.hotel, color: Colors.white70, size: 64),
                                )
                              : Image.network(imageUrl, fit: BoxFit.cover),
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
                          const SizedBox(height: 10),
                          Text(description.isEmpty ? 'لا يوجد وصف متاح حالياً.' : description),
                          const SizedBox(height: 20),
                          _InfoRow(label: 'رقم الهاتف', value: phone),
                          _InfoRow(label: 'واتساب', value: whatsapp),
                          _InfoRow(label: 'الموقع الإلكتروني', value: website),
                          const SizedBox(height: 16),
                          Row(
                            children: [
                              Expanded(
                                child: ElevatedButton.icon(
                                  onPressed: phone.isEmpty
                                      ? null
                                      : () => launchExternalUrl('tel:$phone'),
                                  icon: const Icon(Icons.call),
                                  label: const Text('اتصال'),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF0B172A),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: OutlinedButton.icon(
                                  onPressed: whatsapp.isEmpty
                                      ? null
                                      : () {
                                          final digits = whatsapp.replaceAll(RegExp(r'[^0-9]'), '');
                                          launchExternalUrl('https://wa.me/$digits');
                                        },
                                  icon: const Icon(Icons.chat),
                                  label: const Text('واتساب'),
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
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton.icon(
                              onPressed: website.isEmpty ? null : () => launchExternalUrl(website),
                              icon: const Icon(Icons.public),
                              label: const Text('فتح الموقع'),
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
}

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;

  const _InfoRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          Text('$label: ', style: const TextStyle(fontWeight: FontWeight.bold)),
          Expanded(child: Text(value.isEmpty ? 'غير متوفر' : value)),
        ],
      ),
    );
  }
}
