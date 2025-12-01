import 'dart:convert';
import 'package:crypto/crypto.dart';
import '../database/database_helper.dart';
import '../models/user.dart';

class AuthService {
  final DatabaseHelper _dbHelper = DatabaseHelper.instance;

  // Hash password using SHA256
  String _hashPassword(String password) {
    final bytes = utf8.encode(password);
    final digest = sha256.convert(bytes);
    return digest.toString();
  }

  // Register new user
  Future<AuthResult> register(String firstName, String email, String password) async {
    try {
      // Check if email already exists
      final exists = await _dbHelper.emailExists(email);
      if (exists) {
        return AuthResult(
          success: false,
          message: 'Email already registered. Please use a different email.',
        );
      }

      // Create new user
      final user = User(
        firstName: firstName,
        email: email,
        passwordHash: _hashPassword(password),
      );

      await _dbHelper.createUser(user);

      return AuthResult(
        success: true,
        message: 'Registration successful!',
      );
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Registration failed: ${e.toString()}',
      );
    }
  }

  // Login user
  Future<AuthResult> login(String email, String password) async {
    try {
      final passwordHash = _hashPassword(password);
      final user = await _dbHelper.getUserByCredentials(email, passwordHash);

      if (user != null) {
        return AuthResult(
          success: true,
          message: 'Login successful!',
          user: user,
        );
      } else {
        return AuthResult(
          success: false,
          message: 'Invalid email or password. Please try again.',
        );
      }
    } catch (e) {
      return AuthResult(
        success: false,
        message: 'Login failed: ${e.toString()}',
      );
    }
  }
}

class AuthResult {
  final bool success;
  final String message;
  final User? user;

  AuthResult({
    required this.success,
    required this.message,
    this.user,
  });
}
