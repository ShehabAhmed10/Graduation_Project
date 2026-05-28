 import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/routing/app_routes.dart';
import '../../../providers/auth_provider.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({Key? key}) : super(key: key);

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _fade;
  late final Animation<double> _scale;
  late final Animation<Offset> _slide;

  late final AnimationController _bgController;
  late final Animation<double> _bgShift;

  @override
  void initState() {
    super.initState();

    // Main intro animation
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1100),
    );

    _fade = CurvedAnimation(parent: _controller, curve: Curves.easeInOut);

    _scale = Tween<double>(begin: 0.92, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeOutBack),
    );

    _slide = Tween<Offset>(begin: const Offset(0, 0.06), end: Offset.zero)
        .animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut));

    // Background subtle motion
    _bgController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2600),
    )..repeat(reverse: true);

    _bgShift = Tween<double>(begin: -0.15, end: 0.15).animate(
      CurvedAnimation(parent: _bgController, curve: Curves.easeInOut),
    );

    _controller.forward();

    _goNext();
  }

  Future<void> _goNext() async {
    // وقت مناسب للسلاش (لا تطول)
    await Future.delayed(const Duration(milliseconds: 2200));

    await context.read<AuthProvider>().loadFromStorage();
    if (!mounted) return;

    Navigator.of(context).pushReplacementNamed(AppRoutes.mainLayout);
  }

  @override
  void dispose() {
    _controller.dispose();
    _bgController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Directionality(
        textDirection: TextDirection.rtl,
        child: AnimatedBuilder(
          animation: _bgController,
          builder: (context, _) {
            return Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF071426), Color(0xFF0B1F3A), Color(0xFF101827)],
                  begin: Alignment.topRight,
                  end: Alignment.bottomLeft,
                ),
              ),
              child: Stack(
                children: [
                  // Soft moving glow blobs
                  _GlowBlob(
                    alignment: Alignment(0.7, -0.8 + _bgShift.value),
                    color: const Color(0xFF22C55E).withOpacity(0.12),
                    size: 260,
                  ),
                  _GlowBlob(
                    alignment: Alignment(-0.9, 0.2 - _bgShift.value),
                    color: const Color(0xFF60A5FA).withOpacity(0.12),
                    size: 300,
                  ),
                  _GlowBlob(
                    alignment: Alignment(0.2, 0.9 + _bgShift.value),
                    color: const Color(0xFFF59E0B).withOpacity(0.10),
                    size: 260,
                  ),

                  // Content
                  Center(
                    child: FadeTransition(
                      opacity: _fade,
                      child: SlideTransition(
                        position: _slide,
                        child: ScaleTransition(
                          scale: _scale,
                          child: _GlassCard(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: const [
                                _LogoMark(),
                                SizedBox(height: 18),
                                _TitleWithShimmer(text: 'دليل السياحة اليمني'),
                                SizedBox(height: 10),
                                Text(
                                  'اكتشف اليمن بروح جديدة',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    fontSize: 14,
                                    height: 1.3,
                                    color: Color(0xFFB8C2D1),
                                  ),
                                ),
                                SizedBox(height: 18),
                                _TinyLoader(),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Bottom small text (optional)
                  Positioned(
                    bottom: 22,
                    left: 0,
                    right: 0,
                    child: Text(
                      '© ${2026} — Yemen Tourism Guide',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 11,
                        color: const Color(0xFF9CA3AF).withOpacity(0.7),
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}

class _GlassCard extends StatelessWidget {
  final Widget child;
  const _GlassCard({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 320,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 22),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.10), width: 1),
        color: Colors.white.withOpacity(0.06),
        boxShadow: [
          BoxShadow(
            blurRadius: 24,
            spreadRadius: 0,
            color: Colors.black.withOpacity(0.35),
            offset: const Offset(0, 14),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(22),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 14, sigmaY: 14),
          child: child,
        ),
      ),
    );
  }
}

class _LogoMark extends StatelessWidget {
  const _LogoMark();

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 82,
      width: 82,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: const LinearGradient(
          colors: [Color(0xFF60A5FA), Color(0xFF22C55E)],
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
        ),
        boxShadow: [
          BoxShadow(
            blurRadius: 22,
            color: const Color(0xFF60A5FA).withOpacity(0.25),
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Center(
        child: Container(
          height: 66,
          width: 66,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: const Color(0xFF071426),
            border: Border.all(color: Colors.white.withOpacity(0.14)),
          ),
          child: const Icon(
            Icons.travel_explore,
            size: 34,
            color: Colors.white,
          ),
        ),
      ),
    );
  }
}

class _TinyLoader extends StatelessWidget {
  const _TinyLoader();

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 34,
      height: 34,
      child: CircularProgressIndicator(
        strokeWidth: 2.4,
        valueColor: AlwaysStoppedAnimation<Color>(
          Colors.white.withOpacity(0.85),
        ),
      ),
    );
  }
}

class _GlowBlob extends StatelessWidget {
  final Alignment alignment;
  final Color color;
  final double size;

  const _GlowBlob({
    required this.alignment,
    required this.color,
    required this.size,
  });

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: alignment,
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: color,
        ),
      ),
    );
  }
}

class _TitleWithShimmer extends StatefulWidget {
  final String text;
  const _TitleWithShimmer({required this.text});

  @override
  State<_TitleWithShimmer> createState() => _TitleWithShimmerState();
}

class _TitleWithShimmerState extends State<_TitleWithShimmer>
    with SingleTickerProviderStateMixin {
  late final AnimationController _c;
  late final Animation<double> _t;

  @override
  void initState() {
    super.initState();
    _c = AnimationController(vsync: this, duration: const Duration(milliseconds: 1600))
      ..repeat(reverse: true);
    _t = CurvedAnimation(parent: _c, curve: Curves.easeInOut);
  }

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _c,
      builder: (context, _) {
        final v = _t.value;
        return ShaderMask(
          shaderCallback: (rect) {
            return LinearGradient(
              colors: [
                Colors.white.withOpacity(0.78),
                Colors.white.withOpacity(1.0),
                Colors.white.withOpacity(0.78),
              ],
              stops: [0.0, v, 1.0],
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
            ).createShader(rect);
          },
          child: Text(
            widget.text,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 26,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.2,
              color: Colors.white,
            ),
          ),
        );
      },
    );
  }
}
