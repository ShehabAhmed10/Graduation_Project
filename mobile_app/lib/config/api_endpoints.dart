import 'package:flutter/foundation.dart';

class ApiEndpoints {
  static const String _androidBaseUrl = 'http://10.0.2.2/YemenTourismProject/backend/api';
  static const String _webBaseUrl = 'http://localhost/YemenTourismProject/backend/api';
  static const String _androidDevice = 'http://192.168.1.37/YemenTourismProject/backend/api';


  static String get baseUrl => kIsWeb ? _webBaseUrl : _androidBaseUrl;

  static String get login => '$baseUrl/auth/login.php';
  static String get register => '$baseUrl/auth/register.php';

  static String get cities => '$baseUrl/cities/list.php';
  static String get attractionsList => '$baseUrl/attractions/list.php';
  static String get attractionDetails => '$baseUrl/attractions/details.php';
  static String get favoritesList => '$baseUrl/attractions/favorites_list.php';
  static String get toggleFavorite => '$baseUrl/attractions/toggle_favorite.php';
  static String get addReview => '$baseUrl/attractions/add_review.php';
  static String get updateReview => '$baseUrl/attractions/update_review.php';
  static String get deleteReview => '$baseUrl/attractions/delete_review.php';
  static String get reviewsList => '$baseUrl/attractions/reviews_list.php';
  static String get addComment => '$baseUrl/attractions/add_comment.php';
  static String get uploadReviewImage => '$baseUrl/reviews/upload_image.php';
  static String get hotelsByCity => '$baseUrl/hotels/list_by_city.php';
  static String get hotelDetails => '$baseUrl/hotels/details.php';
  static String get attractionTypes => '$baseUrl/attraction_types/list.php';

  static String get profile => '$baseUrl/users/profile.php';
  static String get updateProfile => '$baseUrl/users/update_profile.php';
  static String get notificationsList => '$baseUrl/notifications/list.php';
  static String get notificationsMarkRead => '$baseUrl/notifications/mark_read.php';
}
