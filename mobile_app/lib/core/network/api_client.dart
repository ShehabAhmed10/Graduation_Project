import 'package:dio/dio.dart';

class ApiClient {
  final Dio dio;

  ApiClient(
    String baseUrl, {
    Interceptor? authInterceptor,
  }) : dio = Dio(
          BaseOptions(
            baseUrl: baseUrl,
            connectTimeout: Duration(milliseconds: 10000),
            receiveTimeout: Duration(milliseconds: 10000),
          ),
        ) {
    if (authInterceptor != null) {
      dio.interceptors.add(authInterceptor);
    }
    dio.interceptors.add(LogInterceptor(responseBody: true));
  }

  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) async {
    return dio.get(path, queryParameters: queryParameters);
  }

  Future<Response> post(String path, {dynamic data}) async {
    return dio.post(path, data: data);
  }
}
 