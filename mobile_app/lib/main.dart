import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:provider/provider.dart';

import 'config/api_endpoints.dart';
import 'config/app_constants.dart';
import 'core/network/api_client.dart';
import 'core/network/auth_interceptor.dart';
import 'core/routing/app_router.dart';
import 'core/routing/app_routes.dart';
import 'core/theme/app_theme.dart';
import 'data/services/auth_service.dart';
import 'data/services/attraction_types_service.dart';
import 'data/services/attractions_service.dart';
import 'data/services/cities_service.dart';
import 'data/services/comments_service.dart';
import 'data/services/favorites_service.dart';
import 'data/services/hotels_service.dart';
import 'data/services/notifications_service.dart';
import 'data/services/reviews_service.dart';
import 'data/services/users_service.dart';
import 'providers/auth_provider.dart';
import 'providers/attraction_types_provider.dart';
import 'providers/attractions_provider.dart';
import 'providers/cities_provider.dart';
import 'providers/comments_provider.dart';
import 'providers/favorites_provider.dart';
import 'providers/hotels_provider.dart';
import 'providers/map_provider.dart';
import 'providers/notifications_provider.dart';
import 'providers/profile_provider.dart';
import 'providers/reviews_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<FlutterSecureStorage>(create: (_) => const FlutterSecureStorage()),
        Provider<ApiClient>(
          create: (context) {
            final storage = context.read<FlutterSecureStorage>();
            final interceptor = AuthInterceptor(() => storage.read(key: 'token'));
            return ApiClient(ApiEndpoints.baseUrl, authInterceptor: interceptor);
          },
        ),
        Provider<AuthService>(create: (context) => AuthService(context.read<ApiClient>())),
        Provider<CitiesService>(create: (context) => CitiesService(context.read<ApiClient>())),
        Provider<AttractionsService>(create: (context) => AttractionsService(context.read<ApiClient>())),
        Provider<AttractionTypesService>(create: (context) => AttractionTypesService(context.read<ApiClient>())),
        Provider<HotelsService>(create: (context) => HotelsService(context.read<ApiClient>())),
        Provider<FavoritesService>(create: (context) => FavoritesService(context.read<ApiClient>())),
        Provider<ReviewsService>(create: (context) => ReviewsService(context.read<ApiClient>())),
        Provider<CommentsService>(create: (context) => CommentsService(context.read<ApiClient>())),
        Provider<UsersService>(create: (context) => UsersService(context.read<ApiClient>())),
        Provider<NotificationsService>(create: (context) => NotificationsService(context.read<ApiClient>())),
        ChangeNotifierProvider(
          create: (context) => AuthProvider(
            service: context.read<AuthService>(),
            storage: context.read<FlutterSecureStorage>(),
          ),
        ),
        ChangeNotifierProvider(create: (context) => CitiesProvider(context.read<CitiesService>())),
        ChangeNotifierProvider(create: (context) => AttractionsProvider(context.read<AttractionsService>())),
        ChangeNotifierProvider(create: (context) => AttractionTypesProvider(context.read<AttractionTypesService>())),
        ChangeNotifierProvider(create: (context) => HotelsProvider(context.read<HotelsService>())),
        ChangeNotifierProvider(create: (context) => FavoritesProvider(context.read<FavoritesService>())),
        ChangeNotifierProvider(create: (context) => ReviewsProvider(context.read<ReviewsService>())),
        ChangeNotifierProvider(create: (context) => CommentsProvider(context.read<CommentsService>())),
        ChangeNotifierProvider(create: (context) => ProfileProvider(context.read<UsersService>())),
        ChangeNotifierProvider(create: (context) => NotificationsProvider(context.read<NotificationsService>())),
        ChangeNotifierProvider(create: (_) => MapProvider()),
      ],
      child: MaterialApp(
        title: AppConstants.appName,
        theme: AppTheme.lightTheme,
        initialRoute: AppRoutes.splash,
        onGenerateRoute: AppRouter.onGenerateRoute,
      ),
    );
  }
}
