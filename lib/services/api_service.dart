import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'api_config.dart';
import 'dart:convert';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  late Dio _dio;
  final _storage = const FlutterSecureStorage();

  ApiService._internal() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConfig.baseUrl,
        connectTimeout: ApiConfig.connectTimeout,
        receiveTimeout: ApiConfig.receiveTimeout,
        headers: {'Accept': 'application/json'},
        contentType: Headers.jsonContentType, // Let Dio handle Content-Type
        responseType: ResponseType.json,
      ),
    );

    // Add interceptors
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Add auth token to requests
          final token = await getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (error, handler) async {
          // Handle errors globally
          if (error.response?.statusCode == 401) {
            // Unauthorized - clear token and navigate to login
            await clearToken();
          }
          return handler.next(error);
        },
      ),
    );
  }

  // Token management
  Future<void> saveToken(String token) async {
    await _storage.write(key: 'auth_token', value: token);
  }

  Future<String?> getToken() async {
    return await _storage.read(key: 'auth_token');
  }

  Future<void> clearToken() async {
    await _storage.delete(key: 'auth_token');
  }

  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null;
  }

  // Auth APIs
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final requestData = {'email': email, 'password': password};

      final response = await _dio.post(ApiConfig.login, data: requestData);

      if (response.data['success'] == true) {
        // Save token
        final token = response.data['data']['token'];
        await saveToken(token);
      }

      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String role = 'klien',
    String? phone,
    String? address,
    String? companyName,
  }) async {
    try {
      final response = await _dio.post(
        ApiConfig.register,
        data: {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'role': role,
          if (phone != null) 'phone': phone,
          if (address != null) 'address': address,
          if (companyName != null) 'company_name': companyName,
        },
      );

      // Don't save token - user needs to login manually
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> logout() async {
    try {
      final response = await _dio.post(ApiConfig.logout);
      await clearToken();
      return response.data;
    } on DioException catch (e) {
      await clearToken(); // Clear token anyway
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getUser() async {
    try {
      final response = await _dio.get(ApiConfig.user);
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> updateProfile({
    String? name,
    String? phone,
    String? address,
    String? companyName,
  }) async {
    try {
      final response = await _dio.put(
        ApiConfig.updateProfile,
        data: {
          if (name != null) 'name': name,
          if (phone != null) 'phone': phone,
          if (address != null) 'address': address,
          if (companyName != null) 'company_name': companyName,
        },
      );
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  // Dashboard API
  Future<Map<String, dynamic>> getDashboardSummary() async {
    try {
      final response = await _dio.get(ApiConfig.dashboardSummary);
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  // Invoice APIs
  Future<Map<String, dynamic>> getInvoices({
    String? status,
    String? search,
  }) async {
    try {
      Map<String, dynamic> queryParams = {};
      if (status != null && status.isNotEmpty) {
        queryParams['status'] = status;
      }
      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }

      final response = await _dio.get(
        ApiConfig.invoices,
        queryParameters: queryParams.isNotEmpty ? queryParams : null,
      );
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getInvoiceDetail(int id) async {
    try {
      final response = await _dio.get(ApiConfig.invoiceDetail(id));
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  // Notification APIs
  Future<Map<String, dynamic>> getNotifications() async {
    try {
      final response = await _dio.get(ApiConfig.notifications);
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> markNotificationAsRead(int id) async {
    try {
      final response = await _dio.put(ApiConfig.markAsRead(id));
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> markAllNotificationsAsRead() async {
    try {
      final response = await _dio.put(ApiConfig.markAllAsRead);
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  // Overdue invoices
  Future<Map<String, dynamic>> getOverdueInvoices() async {
    try {
      final response = await _dio.get('/invoices/overdue');
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  // Payment APIs
  Future<Map<String, dynamic>> getPayments() async {
    try {
      final response = await _dio.get(ApiConfig.payments);
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getPaymentDetail(int id) async {
    try {
      final response = await _dio.get(ApiConfig.paymentDetail(id));
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getInvoicePayments(int invoiceId) async {
    try {
      final response = await _dio.get(ApiConfig.invoicePayments(invoiceId));
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  // Project Requests
  Future<Map<String, dynamic>> getProjectRequests({String? status}) async {
    try {
      final response = await _dio.get(
        ApiConfig.projectRequests,
        queryParameters: status != null ? {'status': status} : null,
      );
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getProjectRequestDetail(int id) async {
    try {
      final response = await _dio.get(ApiConfig.projectRequestDetail(id));
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> createProjectRequest({
    required String title,
    required String type,
    required String description,
    required String location,
    double? expectedBudget,
    String? expectedTimeline,
  }) async {
    try {
      final response = await _dio.post(
        ApiConfig.projectRequests,
        data: {
          'title': title,
          'type': type,
          'description': description,
          'location': location,
          'expected_budget': expectedBudget,
          'expected_timeline': expectedTimeline,
        },
      );
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> updateProjectRequest(
    int id, {
    String? title,
    String? type,
    String? description,
    String? location,
    double? expectedBudget,
    String? expectedTimeline,
  }) async {
    try {
      final Map<String, dynamic> data = {};

      if (title != null) data['title'] = title;
      if (type != null) data['type'] = type;
      if (description != null) data['description'] = description;
      if (location != null) data['location'] = location;
      if (expectedBudget != null) data['expected_budget'] = expectedBudget;
      if (expectedTimeline != null)
        data['expected_timeline'] = expectedTimeline;

      final response = await _dio.put(
        ApiConfig.projectRequestDetail(id),
        data: data,
      );
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> deleteProjectRequest(int id) async {
    try {
      final response = await _dio.delete(ApiConfig.projectRequestDetail(id));
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> uploadRequestDocument(
    int requestId,
    List<int> fileBytes,
    String fileName, {
    required String documentType,
    String? description,
  }) async {
    try {
      FormData formData = FormData.fromMap({
        'file': MultipartFile.fromBytes(fileBytes, filename: fileName),
        'document_type': documentType,
        if (description != null) 'description': description,
      });

      final response = await _dio.post(
        ApiConfig.uploadDocument(requestId),
        data: formData,
      );
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }

  Future<Map<String, dynamic>> deleteRequestDocument(int documentId) async {
    try {
      final response = await _dio.delete(ApiConfig.deleteDocument(documentId));
      return response.data;
    } on DioException catch (e) {
      if (e.response != null) {
        return e.response!.data;
      }
      rethrow;
    }
  }
}
