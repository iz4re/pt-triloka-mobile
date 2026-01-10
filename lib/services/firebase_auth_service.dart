import 'dart:developer' as developer;
import 'package:firebase_auth/firebase_auth.dart';

/// Service to handle Firebase Authentication operations
class FirebaseAuthService {
  final FirebaseAuth _auth = FirebaseAuth.instance;

  /// Returns the currently signed-in Firebase user
  User? get currentUser => _auth.currentUser;

  /// Registers a new user with [email] and [password]
  Future<UserCredential> registerWithEmailPassword({
    required String email,
    required String password,
  }) async {
    try {
      developer.log('Registering user with email: $email', name: 'FirebaseAuthService');
      return await _auth.createUserWithEmailAndPassword(
        email: email,
        password: password,
      );
    } catch (e) {
      developer.log('Registration error: $e', name: 'FirebaseAuthService', error: e);
      rethrow;
    }
  }

  /// Signs in an existing user with [email] and [password]
  Future<UserCredential> signInWithEmailPassword({
    required String email,
    required String password,
  }) async {
    try {
      developer.log('Signing in user with email: $email', name: 'FirebaseAuthService');
      return await _auth.signInWithEmailAndPassword(
        email: email,
        password: password,
      );
    } catch (e) {
      developer.log('Sign in error: $e', name: 'FirebaseAuthService', error: e);
      rethrow;
    }
  }

  /// Retrieves the current user's ID Token for backend verification
  Future<String?> getIdToken() async {
    try {
      return await _auth.currentUser?.getIdToken();
    } catch (e) {
      developer.log('Error getting ID token: $e', name: 'FirebaseAuthService', error: e);
      return null;
    }
  }

  /// Signs out the current user
  Future<void> signOut() async {
    developer.log('Signing out user...', name: 'FirebaseAuthService');
    await _auth.signOut();
  }

  /// Sends a password reset email to the specified [email]
  Future<void> sendPasswordResetEmail(String email) async {
    developer.log('Sending password reset email to: $email', name: 'FirebaseAuthService');
    await _auth.sendPasswordResetEmail(email: email);
  }
}
