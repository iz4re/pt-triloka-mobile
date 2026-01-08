class ProjectRequest {
  final int id;
  final String requestNumber;
  final int klienId; 
  final String? klienName;
  final String title;
  final String type;
  final String description;
  final String location;
  final double? expectedBudget;
  final String? expectedTimeline;
  final String status;
  final List<RequestDocument> documents;
  final String createdAt;
  final String updatedAt;

  ProjectRequest({
    required this.id,
    required this.requestNumber,
    required this.klienId,
    this.klienName,
    required this.title,
    required this.type,
    required this.description,
    required this.location,
    this.expectedBudget,
    this.expectedTimeline,
    required this.status,
    this.documents = const [],
    required this.createdAt,
    required this.updatedAt,
  });

  factory ProjectRequest.fromJson(Map<String, dynamic> json) {
    // Helper to safely parse numbers
    double? parseDoubleNullable(dynamic value) {
      if (value == null) return null;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value);
      return null;
    }

    // Parse documents if available
    List<RequestDocument> documentsList = [];
    if (json['documents'] != null) {
      documentsList = (json['documents'] as List)
          .map((doc) => RequestDocument.fromJson(doc))
          .toList();
    }

    return ProjectRequest(
      id: json['id'],
      requestNumber: json['request_number'] ?? '',
      klienId: json['user_id'] ?? json['klien_id'] ?? 0, 
      klienName: json['klien']?['name'] ?? json['user']?['name'], // Handle user relation too
      title: json['title'] ?? '',
      type: json['type'] ?? 'other',
      description: json['description'] ?? '',
      location: json['location'] ?? '',
      expectedBudget: parseDoubleNullable(json['expected_budget']),
      expectedTimeline: json['expected_timeline'],
      status: json['status'] ?? 'pending',
      documents: documentsList,
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'title': title,
      'type': type,
      'description': description,
      'location': location,
      'expected_budget': expectedBudget,
      'expected_timeline': expectedTimeline,
    };
  }

  String getStatusLabel() {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Menunggu';
      case 'quoted':
        return 'Sudah Dikutip';
      case 'negotiating':
        return 'Negosiasi';
      case 'approved':
        return 'Disetujui';
      case 'rejected':
        return 'Ditolak';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return status;
    }
  }

  String getTypeLabel() {
    switch (type.toLowerCase()) {
      case 'construction':
        return 'Pembangunan';
      case 'renovation':
        return 'Renovasi';
      case 'supply':
        return 'Penyediaan Material';
      case 'contractor':
        return 'Kontraktor';
      case 'other':
        return 'Lainnya';
      default:
        return type;
    }
  }

  bool canEdit() {
    return status == 'pending';
  }

  bool canDelete() {
    return status == 'pending';
  }
}

class RequestDocument {
  final int id;
  final int requestId;
  final String documentType;
  final String filePath;
  final String fileName;
  final String? fileType;
  final int? fileSize;
  final String? description;
  final String verificationStatus;
  final String createdAt;

  RequestDocument({
    required this.id,
    required this.requestId,
    required this.documentType,
    required this.filePath,
    required this.fileName,
    this.fileType,
    this.fileSize,
    this.description,
    required this.verificationStatus,
    required this.createdAt,
  });

  factory RequestDocument.fromJson(Map<String, dynamic> json) {
    return RequestDocument(
      id: json['id'],
      // FIX 2: Baca project_request_id (kolom baru di DB)
      requestId: json['project_request_id'] ?? json['request_id'] ?? 0, 
      documentType: json['document_type'] ?? 'other',
      filePath: json['file_path'] ?? '',
      fileName: json['file_name'] ?? '',
      fileType: json['file_type'],
      fileSize: json['file_size'],
      description: json['description'],
      verificationStatus: json['verification_status'] ?? 'pending',
      createdAt: json['created_at'] ?? '',
    );
  }

  // ... (Sisa method getDocumentTypeLabel, dll biarin sama)
  String getDocumentTypeLabel() {
    switch (documentType.toLowerCase()) {
      case 'ktp':
        return 'KTP';
      case 'npwp':
        return 'NPWP';
      case 'drawing':
        return 'Gambar Desain';
      case 'rab':
        return 'RAB (Rencana Anggaran Biaya)';
      case 'permit':
        return 'Izin';
      case 'photo':
        return 'Foto';
      case 'other':
        return 'Lainnya';
      default:
        return documentType;
    }
  }

  String getStatusLabel() {
    switch (verificationStatus.toLowerCase()) {
      case 'pending':
        return 'Menunggu Verifikasi';
      case 'verified':
        return 'Terverifikasi';
      case 'rejected':
        return 'Ditolak';
      default:
        return verificationStatus;
    }
  }

  String getFileSizeFormatted() {
    if (fileSize == null) return 'Unknown';

    if (fileSize! < 1024) {
      return '$fileSize B';
    } else if (fileSize! < 1024 * 1024) {
      return '${(fileSize! / 1024).toStringAsFixed(1)} KB';
    } else {
      return '${(fileSize! / (1024 * 1024)).toStringAsFixed(1)} MB';
    }
  }
}