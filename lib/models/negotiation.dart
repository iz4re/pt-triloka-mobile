import 'user.dart';

class Negotiation {
  final int id;
  final int quotationId;
  final int senderId;
  final String senderType;
  final String message;
  final double? counterAmount;
  final String status;
  final DateTime createdAt;
  final DateTime updatedAt;
  final User? sender;

  Negotiation({
    required this.id,
    required this.quotationId,
    required this.senderId,
    required this.senderType,
    required this.message,
    this.counterAmount,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
    this.sender,
  });

  factory Negotiation.fromJson(Map<String, dynamic> json) {
    return Negotiation(
      id: json['id'] ?? 0,
      quotationId: json['quotation_id'] ?? 0,
      senderId: json['sender_id'] ?? 0,
      senderType: json['sender_type'] ?? '',
      message: json['message'] ?? '',
      counterAmount: json['counter_amount'] != null
          ? double.tryParse(json['counter_amount'].toString())
          : null,
      status: json['status'] ?? '',
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      sender: json['sender'] != null
          ? User.fromMap({
              'id': json['sender']['id'],
              'firstName': json['sender']['first_name'] ??
                  json['sender']['name'] ??
                  '',
              'lastName': json['sender']['last_name'],
              'email': json['sender']['email'] ?? '',
              'passwordHash': '', 
              'phone': json['sender']['phone'],
              'profilePhoto': json['sender']['profile_photo'],
              'gender': json['sender']['gender'],
              'dateOfBirth': json['sender']['date_of_birth'],
            })
          : null,
    );
  }
}
