class Invoice {
  final int id;
  final String invoiceNumber;
  final int klienId;
  final String? klienName;
  final int createdBy;
  final String? creatorName;
  final String invoiceDate;
  final String dueDate;
  final double subtotal;
  final double tax;
  final double discount;
  final double total;
  final String status;
  final String? notes;
  final String? paidAt;
  final List<InvoiceItem> items;
  final double totalPaid;
  final double remainingBalance;
  final String createdAt;
  final String updatedAt;
  final String? vaNumber;
  final String? vaBank;
  final String? vaExpiresAt;
  final String invoiceType;
  final int? parentInvoiceId;
  final bool isSurveyFeeApplied;

  Invoice({
    required this.id,
    required this.invoiceNumber,
    required this.klienId,
    this.klienName,
    required this.createdBy,
    this.creatorName,
    required this.invoiceDate,
    required this.dueDate,
    required this.subtotal,
    required this.tax,
    required this.discount,
    required this.total,
    required this.status,
    this.notes,
    this.paidAt,
    this.items = const [],
    this.totalPaid = 0,
    this.remainingBalance = 0,
    required this.createdAt,
    required this.updatedAt,
    this.vaNumber,
    this.vaBank,
    this.vaExpiresAt,
    this.invoiceType = 'project',
    this.parentInvoiceId,
    this.isSurveyFeeApplied = false,
  });

  factory Invoice.fromJson(Map<String, dynamic> json) {
    // Helper to safely parse numbers (handles both int, double, and string)
    double _parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }

    // Parse items if available
    List<InvoiceItem> itemsList = [];
    if (json['items'] != null) {
      itemsList = (json['items'] as List)
          .map((item) => InvoiceItem.fromJson(item))
          .toList();
    }

    // Calculate totals
    double totalPaid = 0;
    if (json['payments'] != null) {
      for (var payment in json['payments']) {
        totalPaid += _parseDouble(payment['amount']);
      }
    }

    double total = _parseDouble(json['total']);
    double remainingBalance = total - totalPaid;

    return Invoice(
      id: json['id'],
      invoiceNumber: json['invoice_number'] ?? '',
      klienId: json['klien_id'],
      klienName: json['klien']?['name'],
      createdBy: json['created_by'],
      creatorName: json['creator']?['name'],
      invoiceDate: json['invoice_date'] ?? '',
      dueDate: json['due_date'] ?? '',
      subtotal: _parseDouble(json['subtotal']),
      tax: _parseDouble(json['tax']),
      discount: _parseDouble(json['discount']),
      total: total,
      status: json['status'] ?? 'unpaid',
      notes: json['notes'],
      paidAt: json['paid_at'],
      items: itemsList,
      totalPaid: totalPaid,
      remainingBalance: remainingBalance,
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
      vaNumber: json['va_number'],
      vaBank: json['va_bank'],
      vaExpiresAt: json['va_expires_at'],
      invoiceType: json['invoice_type'] ?? 'project',
      parentInvoiceId: json['parent_invoice_id'],
      isSurveyFeeApplied:
          json['is_survey_fee_applied'] == true ||
          json['is_survey_fee_applied'] == 1,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'invoice_number': invoiceNumber,
      'klien_id': klienId,
      'created_by': createdBy,
      'invoice_date': invoiceDate,
      'due_date': dueDate,
      'subtotal': subtotal,
      'tax': tax,
      'discount': discount,
      'total': total,
      'status': status,
      'notes': notes,
      'paid_at': paidAt,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  String getStatusLabel() {
    switch (status.toLowerCase()) {
      case 'paid':
        return 'Lunas';
      case 'unpaid':
        return 'Belum Bayar';
      case 'overdue':
        return 'Jatuh Tempo';
      case 'cancelled':
        return 'Dibatalkan';
      case 'draft':
        return 'Draft';
      default:
        return status;
    }
  }

  bool isOverdue() {
    if (status == 'paid' || status == 'cancelled') return false;
    try {
      DateTime due = DateTime.parse(dueDate);
      return due.isBefore(DateTime.now());
    } catch (e) {
      return false;
    }
  }
}

class InvoiceItem {
  final int id;
  final int invoiceId;
  final int? itemId;
  final String itemName;
  final String? description;
  final double quantity;
  final double unitPrice;
  final double subtotal;
  final String createdAt;
  final String updatedAt;

  InvoiceItem({
    required this.id,
    required this.invoiceId,
    this.itemId,
    required this.itemName,
    this.description,
    required this.quantity,
    required this.unitPrice,
    required this.subtotal,
    required this.createdAt,
    required this.updatedAt,
  });

  factory InvoiceItem.fromJson(Map<String, dynamic> json) {
    // Helper to safely parse numbers
    double _parseDouble(dynamic value) {
      if (value == null) return 0.0;
      if (value is double) return value;
      if (value is int) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0.0;
      return 0.0;
    }

    return InvoiceItem(
      id: json['id'],
      invoiceId: json['invoice_id'],
      itemId: json['item_id'],
      itemName: json['item_name'] ?? '',
      description: json['description'],
      quantity: _parseDouble(json['quantity']),
      unitPrice: _parseDouble(json['unit_price']),
      subtotal: _parseDouble(json['subtotal']),
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'invoice_id': invoiceId,
      'item_id': itemId,
      'item_name': itemName,
      'description': description,
      'quantity': quantity,
      'unit_price': unitPrice,
      'subtotal': subtotal,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}
