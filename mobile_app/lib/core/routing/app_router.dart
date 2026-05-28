import 'package:flutter/material.dart';

import '../../ui/screens/splash/splash_screen.dart';
import '../../ui/screens/auth/login_screen.dart';
import '../../ui/screens/auth/register_screen.dart';
import '../../ui/screens/main/main_layout.dart';
import '../../ui/screens/attractions/list_screen.dart';
import '../../ui/screens/attractions/details_screen.dart';
import '../../ui/screens/attractions/attraction_map_screen.dart';
import '../../ui/screens/attractions/add_review_screen.dart';
import '../../ui/screens/attractions/add_comment_screen.dart';
import '../../ui/screens/hotels/details_screen.dart';
import '../../ui/screens/profile/edit_profile_screen.dart';
import '../../ui/screens/settings/settings_screen.dart';
import 'app_routes.dart';

class AppRouter {
  static Route<dynamic> onGenerateRoute(RouteSettings settings) {
    switch (settings.name) {
      case AppRoutes.splash:
        return MaterialPageRoute(settings: settings, builder: (_) => const SplashScreen());
      case AppRoutes.login:
        return MaterialPageRoute(settings: settings, builder: (_) => const LoginScreen());
      case AppRoutes.register:
        return MaterialPageRoute(settings: settings, builder: (_) => const RegisterScreen());
      case AppRoutes.mainLayout:
        return MaterialPageRoute(settings: settings, builder: (_) => const MainLayout());
      case AppRoutes.attractionsList:
        return MaterialPageRoute(settings: settings, builder: (_) => const AttractionsListScreen());
      case AppRoutes.attractionDetails:
        return MaterialPageRoute(settings: settings, builder: (_) => const AttractionDetailsScreen());
      case AppRoutes.attractionMap:
        return MaterialPageRoute(settings: settings, builder: (_) => const AttractionMapScreen());
      case AppRoutes.addReview:
        return MaterialPageRoute(settings: settings, builder: (_) => const AddReviewScreen());
      case AppRoutes.addComment:
        return MaterialPageRoute(settings: settings, builder: (_) => const AddCommentScreen());
      case AppRoutes.hotelDetails:
        return MaterialPageRoute(settings: settings, builder: (_) => const HotelDetailsScreen());
      case AppRoutes.editProfile:
        return MaterialPageRoute(settings: settings, builder: (_) => const EditProfileScreen());
      case AppRoutes.settings:
        return MaterialPageRoute(settings: settings, builder: (_) => const SettingsScreen());
      default:
        return MaterialPageRoute(
          settings: settings,
          builder: (_) => const Scaffold(
            body: Center(child: Text('Route not found')),
          ),
        );
    }
  }
}
