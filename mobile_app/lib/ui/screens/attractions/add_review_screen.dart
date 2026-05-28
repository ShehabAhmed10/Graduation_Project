import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/utils/helpers.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/reviews_provider.dart';
import '../../widgets/require_login_dialog.dart';

class AddReviewScreen extends StatefulWidget {
  const AddReviewScreen({Key? key}) : super(key: key);

  @override
  State<AddReviewScreen> createState() => _AddReviewScreenState();
}

class _AddReviewScreenState extends State<AddReviewScreen> {
  int _rating = 0;
  int? _attractionId;
  bool _isEdit = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    _attractionId = args?['id'] as int?;
    _rating = args?['rating'] as int? ?? _rating;
    _isEdit = args?['isEdit'] == true;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(title: Text(_isEdit ? 'تعديل التقييم' : 'إضافة تقييم')),
        body: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('اختر تقييمك', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              Row(
                children: List.generate(5, (index) {
                  final value = index + 1;
                  return IconButton(
                    onPressed: () => setState(() => _rating = value),
                    icon: Icon(
                      value <= _rating ? Icons.star : Icons.star_border,
                      color: const Color(0xFFFBBF24),
                    ),
                  );
                }),
              ),
              const SizedBox(height: 12),
              const Text(
                'يمكنك تعديل تقييمك لاحقاً أو حذفه من صفحة التفاصيل.',
                style: TextStyle(color: Color(0xFF6B7280)),
              ),
              const Spacer(),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: () async {
                    if (!auth.isLoggedIn) {
                      showDialog(
                        context: context,
                        builder: (_) => RequireLoginDialog(
                          onLogin: () => Navigator.of(context).pop(),
                        ),
                      );
                      return;
                    }
                    if (_rating == 0 || _attractionId == null) {
                      showAppSnackBar(context, 'يرجى اختيار التقييم أولاً');
                      return;
                    }
                    final provider = context.read<ReviewsProvider>();
                    final ok = _isEdit
                        ? await provider.updateReview(_attractionId!, _rating)
                        : await provider.addReview(_attractionId!, _rating);
                    if (!mounted) return;
                    if (ok) {
                      showAppSnackBar(
                        context,
                        _isEdit ? 'تم تحديث التقييم بنجاح' : 'تم إرسال التقييم بنجاح',
                      );
                      Navigator.of(context).pop(true);
                    } else {
                      final error = provider.lastError ?? 'تعذر إرسال التقييم.';
                      showAppSnackBar(context, error);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B172A),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: Text(_isEdit ? 'حفظ التقييم' : 'إرسال التقييم'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
