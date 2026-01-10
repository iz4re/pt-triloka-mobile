import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';
import 'screens/login_screen.dart';
import 'screens/payment_upload_screen.dart';
import 'screens/negotiation_screen.dart';
import 'models/invoice.dart';

void main() async {
  // Ensure Flutter binding is initialized
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase
  await Firebase.initializeApp();

  // Initialize sqflite for desktop platforms
  if (!kIsWeb) {
    try {
      if (Platform.isWindows || Platform.isLinux || Platform.isMacOS) {
        sqfliteFfiInit();
        databaseFactory = databaseFactoryFfi;
      }
    } catch (e) {
      debugPrint('Platform initialization failed: $e');
    }
  }

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Triloka Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF6C5DD3)),
        useMaterial3: true,
      ),
      home: const LoginScreen(),
      onGenerateRoute: (settings) {
        if (settings.name == '/payment-upload') {
          final invoice = settings.arguments as Invoice;
          return MaterialPageRoute(
            builder: (context) => PaymentUploadScreen(invoice: invoice),
          );
        }
        if (settings.name == '/negotiation') {
          final quotationId = settings.arguments as int;
          return MaterialPageRoute(
            builder: (context) => NegotiationScreen(quotationId: quotationId),
          );
        }
        return null;
      },
    );
  }
}
