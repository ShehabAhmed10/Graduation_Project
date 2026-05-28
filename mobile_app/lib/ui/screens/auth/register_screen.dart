import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/routing/app_routes.dart';
import '../../../core/utils/helpers.dart';
import '../../../providers/auth_provider.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({Key? key}) : super(key: key);

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscurePassword = true;
  bool _obscureConfirm = true;

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        body: LayoutBuilder(
          builder: (context, constraints) {
            final heroHeight = constraints.maxHeight * 0.28;
            return Stack(
              children: [
                Container(
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFF0B172A), Color(0xFF1F2937)],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                ),
                Positioned(
                  top: -30,
                  right: -20,
                  child: _GlowCircle(size: 140, color: Color(0xFF14B8A6)),
                ),
                Positioned(
                  top: 80,
                  left: -40,
                  child: _GlowCircle(size: 200, color: Color(0xFFD4AF37)),
                ),
                SafeArea(
                  child: Column(
                    children: [
                      SizedBox(
                        height: heroHeight,
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: const [
                            Text(
                              'إنشاء حساب جديد',
                              style: TextStyle(
                                fontSize: 22,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            SizedBox(height: 6),
                            Text(
                              'انضم إلى مجتمع الرحّالة اليمني',
                              style: TextStyle(color: Color(0xFF9CA3AF)),
                            ),
                          ],
                        ),
                      ),
                      Expanded(
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 22),
                          decoration: const BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.vertical(top: Radius.circular(36)),
                          ),
                          child: SingleChildScrollView(
                            child: Form(
                              key: _formKey,
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  TextFormField(
                                    controller: _nameController,
                                    validator: (value) {
                                      if (value == null || value.trim().isEmpty) {
                                        return 'الرجاء إدخال الاسم الكامل';
                                      }
                                      return null;
                                    },
                                    decoration: const InputDecoration(
                                      labelText: 'الاسم الكامل',
                                      prefixIcon: Icon(Icons.person_outline),
                                      border: OutlineInputBorder(),
                                    ),
                                  ),
                                  const SizedBox(height: 14),
                                  TextFormField(
                                    controller: _emailController,
                                    keyboardType: TextInputType.emailAddress,
                                    validator: (value) {
                                      if (value == null || value.trim().isEmpty) {
                                        return 'الرجاء إدخال البريد الإلكتروني';
                                      }
                                      return null;
                                    },
                                    decoration: const InputDecoration(
                                      labelText: 'البريد الإلكتروني',
                                      prefixIcon: Icon(Icons.email_outlined),
                                      border: OutlineInputBorder(),
                                    ),
                                  ),
                                  const SizedBox(height: 14),
                                  TextFormField(
                                    controller: _phoneController,
                                    keyboardType: TextInputType.phone,
                                    decoration: const InputDecoration(
                                      labelText: 'رقم الهاتف (اختياري)',
                                      prefixIcon: Icon(Icons.phone_outlined),
                                      border: OutlineInputBorder(),
                                    ),
                                  ),
                                  const SizedBox(height: 14),
                                  TextFormField(
                                    controller: _passwordController,
                                    obscureText: _obscurePassword,
                                    validator: (value) {
                                      if (value == null || value.trim().isEmpty) {
                                        return 'الرجاء إدخال كلمة المرور';
                                      }
                                      if (value.trim().length < 6) {
                                        return 'كلمة المرور قصيرة جداً';
                                      }
                                      return null;
                                    },
                                    decoration: InputDecoration(
                                      labelText: 'كلمة المرور',
                                      prefixIcon: const Icon(Icons.lock_outline),
                                      border: const OutlineInputBorder(),
                                      suffixIcon: IconButton(
                                        icon: Icon(
                                          _obscurePassword ? Icons.visibility_off : Icons.visibility,
                                        ),
                                        onPressed: () {
                                          setState(() => _obscurePassword = !_obscurePassword);
                                        },
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 14),
                                  TextFormField(
                                    controller: _confirmPasswordController,
                                    obscureText: _obscureConfirm,
                                    validator: (value) {
                                      if (value == null || value.trim().isEmpty) {
                                        return 'الرجاء تأكيد كلمة المرور';
                                      }
                                      if (value.trim() != _passwordController.text.trim()) {
                                        return 'كلمتا المرور غير متطابقتين';
                                      }
                                      return null;
                                    },
                                    decoration: InputDecoration(
                                      labelText: 'تأكيد كلمة المرور',
                                      prefixIcon: const Icon(Icons.lock_outline),
                                      border: const OutlineInputBorder(),
                                      suffixIcon: IconButton(
                                        icon: Icon(
                                          _obscureConfirm ? Icons.visibility_off : Icons.visibility,
                                        ),
                                        onPressed: () {
                                          setState(() => _obscureConfirm = !_obscureConfirm);
                                        },
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 10),
                                  const Text(
                                    'يجب أن تكون كلمة المرور 8 أحرف على الأقل وتحتوي على حرف كبير ورقم.',
                                    style: TextStyle(color: Color(0xFF6B7280), fontSize: 12),
                                  ),
                                  const SizedBox(height: 18),
                                  SizedBox(
                                    height: 50,
                                    child: ElevatedButton(
                                      onPressed: auth.isLoading
                                          ? null
                                          : () async {
                                              if (!_formKey.currentState!.validate()) return;
                                              final ok = await auth.register(
                                                _nameController.text.trim(),
                                                _emailController.text.trim(),
                                                _passwordController.text.trim(),
                                                phone: _phoneController.text.trim().isEmpty
                                                    ? null
                                                    : _phoneController.text.trim(),
                                              );
                                              if (!mounted) return;
                                              if (ok) {
                                                Navigator.of(context).pushNamedAndRemoveUntil(
                                                  AppRoutes.mainLayout,
                                                  (route) => false,
                                                );
                                              } else {
                                                showAppSnackBar(
                                                  context,
                                                  auth.lastError ?? 'تعذر إنشاء الحساب.',
                                                );
                                              }
                                            },
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: const Color(0xFFD4AF37),
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(24),
                                        ),
                                      ),
                                      child: auth.isLoading
                                          ? const SizedBox(
                                              width: 20,
                                              height: 20,
                                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                            )
                                          : const Text('إنشاء الحساب'),
                                    ),
                                  ),
                                  const SizedBox(height: 16),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      const Text('لديك حساب بالفعل؟'),
                                      TextButton(
                                        onPressed: () => Navigator.of(context).pushNamed(AppRoutes.login),
                                        child: const Text(
                                          'تسجيل الدخول',
                                          style: TextStyle(color: Color(0xFF0B172A)),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            );
          },
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
