class Payment {
  final int id;
  final String paymentNumber;
  final int invoiceId;
  final String? invoiceNumber;
  final String? klienName;
  final double amount;
  final String paymentDate;
  final String paymentMethod;
  final String? notes;
  final String? proofImage;
  final int createdBy;
  final String? creatorName;
  final String createdAt;
  final String updatedAt;

  Payment({
    required this.id,
    required this.paymentNumber,
    required this.invoiceId,
    this.invoiceNumber,
    this.klienName,
    required this.amount,
    required this.paymentDate,
    required this.paymentMethod,
    this.notes,
    this.proofImage,
    required this.createdBy,
    this.creatorName,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Payment.fromJson(Map<String, dynamic> json) {
    return Payment(
      id: json['id'],
      paymentNumber: json['payment_number'] ?? '',
      invoiceId: json['invoice_id'],
      invoiceNumber: json['invoice']?['invoice_number'],
      klienName: json['invoice']?['klien']?['name'],
      amount: (json['amount'] ?? 0).toDouble(),
      paymentDate: json['payment_date'] ?? '',
      paymentMethod: json['payment_method'] ?? 'other',
      notes: json['notes'],
      proofImage: json['proof_image'],
      createdBy: json['created_by'],
      creatorName: json['creator']?['name'],
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'payment_number': paymentNumber,
      'invoice_id': invoiceId,
      'amount': amount,
      'payment_date': paymentDate,
      'payment_method': paymentMethod,
      'notes': notes,
      'proof_image': proofImage,
      'created_by': createdBy,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }

  String getPaymentMethodLabel() {
    switch (paymentMethod.toLowerCase()) {
      case 'cash':
        return 'Tunai';
      case 'transfer':
        return 'Transfer Bank';
      case 'check':
        return 'Cek';
      case 'other':
        return 'Lainnya';
      default:
        return paymentMethod;
    }
  }
}
