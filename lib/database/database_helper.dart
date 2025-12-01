import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:sqflite/sqflite.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';
import 'package:path/path.dart';
import '../models/user.dart';

class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  DatabaseHelper._init() {
    // Initialize sqflite for desktop platforms (not web)
    if (!kIsWeb) {
      try {
        if (Platform.isWindows || Platform.isLinux || Platform.isMacOS) {
          sqfliteFfiInit();
          databaseFactory = databaseFactoryFfi;
        }
      } catch (e) {
        // Platform not supported, use default factory
        debugPrint('Platform detection failed: $e');
      }
    }
  }

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('triloka.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 1,
      onCreate: _createDB,
    );
  }

  Future<void> _createDB(Database db, int version) async {
    await db.execute('''
      CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        firstName TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        passwordHash TEXT NOT NULL
      )
    ''');
  }

  // Create new user
  Future<int> createUser(User user) async {
    final db = await database;
    return await db.insert('users', user.toMap());
  }

  // Check if email exists
  Future<bool> emailExists(String email) async {
    final db = await database;
    final result = await db.query(
      'users',
      where: 'email = ?',
      whereArgs: [email],
    );
    return result.isNotEmpty;
  }

  // Get user by email and password hash
  Future<User?> getUserByCredentials(String email, String passwordHash) async {
    final db = await database;
    final result = await db.query(
      'users',
      where: 'email = ? AND passwordHash = ?',
      whereArgs: [email, passwordHash],
    );

    if (result.isNotEmpty) {
      return User.fromMap(result.first);
    }
    return null;
  }

  // Close database
  Future<void> close() async {
    final db = await database;
    await db.close();
  }
}
