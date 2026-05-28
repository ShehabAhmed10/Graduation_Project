import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../data/services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  final AuthService service;
  final FlutterSecureStorage storage;

  AuthProvider({required this.service, required this.storage});

  Map<String, dynamic>? currentUser;
  String? token;
  bool isLoading = false;
  String? lastError;

  bool get isLoggedIn => currentUser != null;

  Future<void> loadFromStorage() async {
    final storedToken = await storage.read(key: 'token');
    final storedUser = await storage.read(key: 'user');
    token = storedToken;
    if (storedUser != null) {
      try {
        currentUser = jsonDecode(storedUser) as Map<String, dynamic>;
      } catch (_) {
        currentUser = null;
      }
    }
    if (token != null && currentUser != null) {
      final userId = currentUser?['id'];
      if (userId != null && !token!.startsWith('user_')) {
        token = 'user_$userId';
        await storage.write(key: 'token', value: token);
      }
    }
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    isLoading = true;
    lastError = null;
    notifyListeners();
    try {
      final resp = await service.login(email, password);
      final data = _extractData(resp);
      final user = data['user'] as Map<String, dynamic>?;
      final rawToken = data['token'] as String?;
      final userId = user?['id'];
      final authToken = userId != null ? 'user_$userId' : rawToken;
      if (user == null || authToken == null) {
        lastError = 'بيانات تسجيل الدخول غير مكتملة.';
        return false;
      }
      currentUser = user;
      token = authToken;
      await storage.write(key: 'token', value: authToken);
      await storage.write(key: 'user', value: jsonEncode(user));
      return true;
    } on DioException catch (e) {
      lastError = _readErrorMessage(e);
      return false;
    } catch (_) {
      lastError = 'حدث خطأ غير متوقع أثناء تسجيل الدخول.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> register(String fullName, String email, String password, {String? phone}) async {
    isLoading = true;
    lastError = null;
    notifyListeners();
    try {
      final resp = await service.register(fullName, email, password, phone: phone);
      final data = _extractData(resp);
      final user = data['user'] as Map<String, dynamic>?;
      final rawToken = data['token'] as String?;
      final userId = user?['id'];
      final authToken = userId != null ? 'user_$userId' : rawToken;
      if (user == null || authToken == null) {
        lastError = 'بيانات التسجيل غير مكتملة.';
        return false;
      }
      currentUser = user;
      token = authToken;
      await storage.write(key: 'token', value: authToken);
      await storage.write(key: 'user', value: jsonEncode(user));
      return true;
    } on DioException catch (e) {
      lastError = _readErrorMessage(e);
      return false;
    } catch (_) {
      lastError = 'حدث خطأ غير متوقع أثناء إنشاء الحساب.';
      return false;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    token = null;
    currentUser = null;
    lastError = null;
    await storage.delete(key: 'token');
    await storage.delete(key: 'user');
    notifyListeners();
  }

  Map<String, dynamic> _extractData(dynamic response) {
    if (response is Response) {
      final root = response.data as Map<String, dynamic>;
      return root['data'] as Map<String, dynamic>? ?? {};
    }
    return {};
  }

  String _readErrorMessage(DioException e) {
    final data = e.response?.data;
    if (data is Map<String, dynamic> && data['message'] is String) {
      return data['message'] as String;
    }
    return 'تعذر إتمام العملية، حاول لاحقاً.';
  }
}
