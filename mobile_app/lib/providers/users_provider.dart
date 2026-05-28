import 'package:flutter/foundation.dart';
import '../data/services/users_service.dart';

class UsersProvider extends ChangeNotifier {
  final UsersService service;
  UsersProvider(this.service);
}
