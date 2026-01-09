import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';
import 'dart:convert';

class UserSession {
  static final UserSession _instance = UserSession._internal();
  factory UserSession() => _instance;
  UserSession._internal();

  User? _currentUser;

  // Get current user
  User? get currentUser => _currentUser;

  // Check if user is logged in
  bool get isLoggedIn => _currentUser != null;

  // Set current user and persist to SharedPreferences
  Future<void> setUser(User user) async {
    _currentUser = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_data', jsonEncode(user.toMap()));
  }

  // Update current user (for profile updates)
  Future<void> updateUser(User user) async {
    _currentUser = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_data', jsonEncode(user.toMap()));
  }

  // Clear user session (logout)
  Future<void> clearUser() async {
    _currentUser = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('user_data');
  }

  // Load user from SharedPreferences (on app start)
  Future<void> loadUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userData = prefs.getString('user_data');
    if (userData != null) {
      try {
        final userMap = jsonDecode(userData) as Map<String, dynamic>;
        _currentUser = User.fromMap(userMap);
      } catch (e) {
        // If error, clear invalid data
        await clearUser();
      }
    }
  }

  // Set user from JSON data (for API responses)
  Future<void> setUserFromJson(Map<String, dynamic> json) async {
    final user = User.fromMap(json);
    await setUser(user);
  }
}
