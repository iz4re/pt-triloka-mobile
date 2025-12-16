class QuotationItem {
  final int id;
  final int quotationId;
  final String itemName;
  final String? category;
  final double quantity;
  final String unit;
  final double unitPrice;
  final double subtotal;
  final String? description;

  QuotationItem({
    required this.id,
    required this.quotationId,
    required this.itemName,
    this.category,
    required this.quantity,
    required this.unit,
    required this.unitPrice,
    required this.subtotal,
    this.description,
  });

  factory QuotationItem.fromJson(Map<String, dynamic> json) {
    return QuotationItem(
      id: json['id'] ?? 0,
      quotationId: json['quotation_id'] ?? 0,
      itemName: json['item_name'] ?? '',
      category: json['category'],
      quantity: double.tryParse(json['quantity'].toString()) ?? 0,
      unit: json['unit'] ?? 'pcs',
      unitPrice: double.tryParse(json['unit_price'].toString()) ?? 0,
      subtotal: double.tryParse(json['subtotal'].toString()) ?? 0,
      description: json['description'],
    );
  }

  String getCategoryLabel() {
    switch (category?.toLowerCase()) {
      case 'material':
        return 'Material';
      case 'labor':
        return 'Tenaga Kerja';
      case 'equipment':
        return 'Peralatan';
      default:
        return 'Lainnya';
    }
  }
}

class Quotation {
  final int id;
  final int projectRequestId;
  final String quotationNumber;
  final int version;
  final double subtotal;
  final double tax;
  final double discount;
  final double total;
  final String? notes;
  final String validUntil;
  final String status;
  final String createdAt;
  final List<QuotationItem> items;

  Quotation({
    required this.id,
    required this.projectRequestId,
    required this.quotationNumber,
    required this.version,
    required this.subtotal,
    required this.tax,
    required this.discount,
    required this.total,
    this.notes,
    required this.validUntil,
    required this.status,
    required this.createdAt,
    this.items = const [],
  });

  factory Quotation.fromJson(Map<String, dynamic> json) {
    List<QuotationItem> itemsList = [];
    if (json['items'] != null) {
      itemsList = (json['items'] as List)
          .map((item) => QuotationItem.fromJson(item))
          .toList();
    }

    return Quotation(
      id: json['id'] ?? 0,
      projectRequestId: json['project_request_id'] ?? 0,
      quotationNumber: json['quotation_number'] ?? '',
      version: json['version'] ?? 1,
      subtotal: double.tryParse(json['subtotal'].toString()) ?? 0,
      tax: double.tryParse(json['tax'].toString()) ?? 0,
      discount: double.tryParse(json['discount'].toString()) ?? 0,
      total: double.tryParse(json['total'].toString()) ?? 0,
      notes: json['notes'],
      validUntil: json['valid_until'] ?? '',
      status: json['status'] ?? 'draft',
      createdAt: json['created_at'] ?? '',
      items: itemsList,
    );
  }

  String getStatusLabel() {
    switch (status.toLowerCase()) {
      case 'draft':
        return 'Draft';
      case 'sent':
        return 'Terkirim';
      case 'approved':
        return 'Disetujui';
      case 'rejected':
        return 'Ditolak';
      case 'revised':
        return 'Direvisi';
      case 'expired':
        return 'Kadaluarsa';
      default:
        return status;
    }
  }

  bool isExpired() {
    try {
      final validDate = DateTime.parse(validUntil);
      return validDate.isBefore(DateTime.now());
    } catch (e) {
      return false;
    }
  }

  bool canApprove() {
    return (status == 'sent' || status == 'revised') && !isExpired();
  }
}
