import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/routing/app_routes.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/profile_provider.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      if (auth.isLoggedIn) {
        context.read<ProfileProvider>().fetchProfile();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final profile = context.watch<ProfileProvider>();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: !auth.isLoggedIn
            ? _GuestState(onLogin: () => Navigator.of(context).pushNamed(AppRoutes.login))
            : profile.isLoading
                ? const Center(child: CircularProgressIndicator())
                : _ProfileContent(
                    data: profile.profile ?? auth.currentUser ?? {},
                    onEdit: () => Navigator.of(context).pushNamed(AppRoutes.editProfile),
                    onLogout: () => auth.logout(),
                    onFavorites: () => Navigator.of(context).pushNamed(AppRoutes.mainLayout),
                    onNotifications: () => Navigator.of(context).pushNamed(AppRoutes.mainLayout),
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
          const Text('أنت تتصفح كضيف. سجّل الدخول لإدارة ملفك الشخصي.'),
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

class _ProfileContent extends StatelessWidget {
  final Map<String, dynamic> data;
  final VoidCallback onEdit;
  final VoidCallback onLogout;
  final VoidCallback onFavorites;
  final VoidCallback onNotifications;

  const _ProfileContent({
    required this.data,
    required this.onEdit,
    required this.onLogout,
    required this.onFavorites,
    required this.onNotifications,
  });

  @override
  Widget build(BuildContext context) {
    final name = data['full_name'] ?? '';
    final email = data['email'] ?? '';

    return CustomScrollView(
      slivers: [
        SliverAppBar(
          pinned: true,
          expandedHeight: 180,
          backgroundColor: const Color(0xFF0B172A),
          flexibleSpace: FlexibleSpaceBar(
            background: Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF0B172A), Color(0xFF1F2937)],
                  begin: Alignment.topRight,
                  end: Alignment.bottomLeft,
                ),
              ),
              child: const Align(
                alignment: Alignment.bottomRight,
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: Text(
                    'الملف الشخصي',
                    style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ),
          ),
        ),
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: const [
                      BoxShadow(color: Color(0x11000000), blurRadius: 12, offset: Offset(0, 6)),
                    ],
                  ),
                  child: Column(
                    children: [
                      const CircleAvatar(
                        radius: 36,
                        backgroundColor: Color(0xFF0B172A),
                        child: Icon(Icons.person, color: Colors.white, size: 36),
                      ),
                      const SizedBox(height: 12),
                      Text(name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 6),
                      Text(email, style: const TextStyle(color: Color(0xFF6B7280))),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: _QuickAction(
                        icon: Icons.favorite,
                        label: 'المفضلة',
                        onTap: onFavorites,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _QuickAction(
                        icon: Icons.notifications,
                        label: 'الإشعارات',
                        onTap: onNotifications,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: onEdit,
                    icon: const Icon(Icons.edit),
                    label: const Text('تعديل الملف الشخصي'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0B172A),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: onLogout,
                    icon: const Icon(Icons.logout),
                    label: const Text('تسجيل الخروج'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFFB91C1C),
                      side: const BorderSide(color: Color(0xFFB91C1C)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _QuickAction extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _QuickAction({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [
            BoxShadow(color: Color(0x11000000), blurRadius: 10, offset: Offset(0, 6)),
          ],
        ),
        child: Column(
          children: [
            Icon(icon, color: const Color(0xFF0B172A)),
            const SizedBox(height: 6),
            Text(label, style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}
