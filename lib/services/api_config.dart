import 'package:flutter/foundation.dart' show kIsWeb;

class ApiConfig {
  static const bool usePhysicalDevice = false; // true = HP, false = Emulator
  static const String wifiIp = '192.168.1.8';

  static String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api';
    } else {
      return usePhysicalDevice
          ? 'http://$wifiIp:8000/api'
          : 'http://10.0.2.2:8000/api';
    }
  }

  static const String login = '/login';
  static const String register = '/register';
  static const String logout = '/logout';
  static const String user = '/user';
  static const String updateProfile = '/user/profile';

  static const String dashboardSummary = '/dashboard/summary';

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

  // Quotations
  static const String quotations = '/quotations';
  static String quotationDetail(int id) => '/quotations/$id';

  // Timeout settings
  static const Duration connectTimeout = Duration(seconds: 60);
  static const Duration receiveTimeout = Duration(seconds: 60);
}
