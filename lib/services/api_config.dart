import 'package:flutter/foundation.dart' show kIsWeb;

class ApiConfig {
  // Auto-detect platform and use appropriate URL
  // Web (Chrome): 127.0.0.1
  // Android Emulator: 10.0.2.2
  // Production: Change to your actual server domain
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api';
    } else {
      // Android emulator or real device
      return 'http://10.0.2.2:8000/api';
    }
  }

  // API Endpoints
  static const String login = '/login';
  static const String register = '/register';
  static const String logout = '/logout';
  static const String user = '/user';
  static const String updateProfile = '/user/profile';

  // Dashboard
  static const String dashboardSummary = '/dashboard/summary';

  // Invoices
  static const String invoices = '/invoices';
  static String invoiceDetail(int id) => '/invoices/$id';

  // Payments
  static const String payments = '/payments';
  static String paymentDetail(int id) => '/payments/$id';
  static String invoicePayments(int invoiceId) =>
      '/invoices/$invoiceId/payments';

  // Items (Inventory)
  static const String items = '/items';
  static String itemDetail(int id) => '/items/$id';
  static const String lowStockItems = '/items/status/low-stock';

  // Notifications
  static const String notifications = '/notifications';
  static String notificationDetail(int id) => '/notifications/$id';
  static String markAsRead(int id) => '/notifications/$id/read';
  static const String markAllAsRead = '/notifications/read-all';

  // Project Requests
  static const String projectRequests = '/project-requests';
  static String projectRequestDetail(int id) => '/project-requests/$id';
  static String uploadDocument(int requestId) =>
      '/project-requests/$requestId/documents';
  static String deleteDocument(int documentId) =>
      '/request-documents/$documentId';

  // Timeout settings
  static const Duration connectTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
