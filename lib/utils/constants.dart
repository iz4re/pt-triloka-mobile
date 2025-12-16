class AppConstants {
  static const String appName = 'CV Triloka';
  static const String appVersion = '1.0.0';

  static const String networkError =
      'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
  static const String serverError =
      'Terjadi kesalahan pada server. Silakan coba lagi.';
  static const String unauthorizedError =
      'Sesi Anda telah berakhir. Silakan login kembali.';
  static const String notFoundError = 'Data tidak ditemukan.';
  static const String validationError =
      'Mohon periksa kembali data yang Anda masukkan.';
  static const String genericError = 'Terjadi kesalahan. Silakan coba lagi.';
  static const String loginSuccess = 'Login berhasil!';
  static const String registerSuccess = 'Registrasi berhasil! Silakan login.';
  static const String updateSuccess = 'Data berhasil diperbarui!';
  static const String deleteSuccess = 'Data berhasil dihapus!';
  static const String uploadSuccess = 'Upload berhasil!';

  static const int minPasswordLength = 6;
  static const int maxFileSize = 5 * 1024 * 1024; // 5MB
  static const List<String> allowedImageTypes = ['jpg', 'jpeg', 'png'];
  static const int itemsPerPage = 20;
  static const Duration animationDuration = Duration(milliseconds: 300);
  static const Duration snackBarDuration = Duration(seconds: 3);

  static const Map<String, String> projectTypes = {
    'construction': 'Konstruksi',
    'renovation': 'Renovasi',
    'supply': 'Supply',
    'contractor': 'Kontraktor',
    'other': 'Lainnya',
  };

  // Project Request Status
  static const Map<String, String> projectStatus = {
    'pending': 'Menunggu',
    'quoted': 'Dikutip',
    'negotiating': 'Negosiasi',
    'approved': 'Disetujui',
    'rejected': 'Ditolak',
    'cancelled': 'Dibatalkan',
  };

  // Invoice Status
  static const Map<String, String> invoiceStatus = {
    'pending': 'Menunggu',
    'paid': 'Lunas',
    'cancelled': 'Dibatalkan',
  };

  // Payment Status
  static const Map<String, String> paymentStatus = {
    'pending': 'Menunggu Verifikasi',
    'verified': 'Terverifikasi',
    'rejected': 'Ditolak',
  };
}
