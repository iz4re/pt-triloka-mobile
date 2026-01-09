import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';
import '../models/user.dart';
import 'dart:convert';

class AuthService {
  final ApiService _apiService = ApiService();

  // Login user via API
  Future<AuthResult> login(String email, String password) async {
    try {
      final response = await _apiService.login(email, password);

      if (response['success'] == true) {
        // Parse user data from API response
        final userData = response['data']['user'];
        final String? fullName = userData['name']?.toString();
        final user = User(
          id: userData['id'],
          firstName: fullName?.split(' ').first ?? 'User',
          lastName: fullName != null && fullName.split(' ').length > 1 ? fullName.split(' ').skip(1).join(' ') : '',
          email: userData['email'],
          passwordHash: '', // Not needed from API
          phone: userData['phone'],
          gender: userData['gender'],
          dateOfBirth: userData['date_of_birth'],
        );

        // Save user data to SharedPreferences for session
        await _saveUserToLocalStorage(user);

        return AuthResult(
          success: true,
          message: response['message'] ?? 'Login successful!',
          user: user,
        );
      } else {
        return AuthResult(
          success: false,
          message: response['message'] ?? 'Login failed',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Login failed: ${e.toString()}',
      );
    }
  }

  // Register new user via API
  Future<AuthResult> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? address,
    String? companyName,
  }) async {
    try {
      final response = await _apiService.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        phone: phone,
        address: address,
        companyName: companyName,
      );

      if (response['success'] == true) {
       
        return AuthResult(
          success: true,
          message: response['message'] ?? 'Registration successful!',
        );
      } else {
        return AuthResult(
          success: false,
          message: response['message'] ?? 'Registration failed',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Registration failed: ${e.toString()}',
      );
    }
  }

  // Logout user
  Future<void> logout() async {
    await _apiService.logout();
    await _clearLocalStorage();
  }

  // Save user to local storage for session management
  Future<void> _saveUserToLocalStorage(User user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_data', json.encode(user.toMap()));
  }

  // Clear local storage
  Future<void> _clearLocalStorage() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('user_data');
  }

  // Get current user from local storage
  Future<User?> getCurrentUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userData = prefs.getString('user_data');
    if (userData != null) {
      return User.fromMap(json.decode(userData));
    }
    return null;
  }

  // Check if user is logged in
  Future<bool> isLoggedIn() async {
    return await _apiService.isLoggedIn();
  }
}

class AuthResult {
  final bool success;
  final String message;
  final User? user;

  AuthResult({required this.success, required this.message, this.user});
}
