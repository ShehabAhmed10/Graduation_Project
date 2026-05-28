import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../../core/utils/helpers.dart';
import '../../../providers/auth_provider.dart';
import '../../../providers/comments_provider.dart';
import '../../widgets/require_login_dialog.dart';

class AddCommentScreen extends StatefulWidget {
  const AddCommentScreen({Key? key}) : super(key: key);

  @override
  State<AddCommentScreen> createState() => _AddCommentScreenState();
}

class _AddCommentScreenState extends State<AddCommentScreen> {
  final _commentController = TextEditingController();
  int? _attractionId;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments as Map<String, dynamic>?;
    _attractionId = args?['id'] as int?;
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final comments = context.watch<CommentsProvider>();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        appBar: AppBar(title: const Text('إضافة تعليق')),
        body: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('تعليقك', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              TextField(
                controller: _commentController,
                maxLines: 5,
                decoration: const InputDecoration(
                  labelText: 'اكتب تعليقك هنا',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'يمكنك إضافة أكثر من تعليق على نفس المعلم.',
                style: TextStyle(color: Color(0xFF6B7280)),
              ),
              const Spacer(),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: comments.isLoading
                      ? null
                      : () async {
                          if (!auth.isLoggedIn) {
                            showDialog(
                              context: context,
                              builder: (_) => RequireLoginDialog(
                                onLogin: () => Navigator.of(context).pop(),
                              ),
                            );
                            return;
                          }
                          if (_attractionId == null) {
                            showAppSnackBar(context, 'لا يمكن إضافة تعليق بدون معرف المعلم.');
                            return;
                          }
                          final text = _commentController.text.trim();
                          if (text.isEmpty) {
                            showAppSnackBar(context, 'يرجى كتابة تعليق أولاً.');
                            return;
                          }
                          final ok = await context.read<CommentsProvider>().addComment(
                                _attractionId!,
                                text,
                              );
                          if (!mounted) return;
                          if (ok) {
                            showAppSnackBar(context, 'تم إضافة التعليق بنجاح');
                            Navigator.of(context).pop(true);
                          } else {
                            final error = context.read<CommentsProvider>().lastError ?? 'تعذر إضافة التعليق.';
                            showAppSnackBar(context, error);
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0B172A),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: comments.isLoading
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('إرسال التعليق'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
