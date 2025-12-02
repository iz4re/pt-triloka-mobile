class User {
  final int? id;
  final String firstName;
  final String? lastName;
  final String email;
  final String passwordHash;
  final String? phone;
  final String? profilePhoto; // Base64 encoded image
  final String? gender;
  final String? dateOfBirth;

  User({
    this.id,
    required this.firstName,
    this.lastName,
    required this.email,
    required this.passwordHash,
    this.phone,
    this.profilePhoto,
    this.gender,
    this.dateOfBirth,
  });

  // Get full name
  String getFullName() {
    if (lastName != null && lastName!.isNotEmpty) {
      return '$firstName $lastName';
    }
    return firstName;
  }

  // Get initials for avatar
  String getInitials() {
    String initials = firstName[0].toUpperCase();
    if (lastName != null && lastName!.isNotEmpty) {
      initials += lastName![0].toUpperCase();
    }
    return initials;
  }

  // Copy with method for updates
  User copyWith({
    int? id,
    String? firstName,
    String? lastName,
    String? email,
    String? passwordHash,
    String? phone,
    String? profilePhoto,
    String? gender,
    String? dateOfBirth,
  }) {
    return User(
      id: id ?? this.id,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      email: email ?? this.email,
      passwordHash: passwordHash ?? this.passwordHash,
      phone: phone ?? this.phone,
      profilePhoto: profilePhoto ?? this.profilePhoto,
      gender: gender ?? this.gender,
      dateOfBirth: dateOfBirth ?? this.dateOfBirth,
    );
  }

  // Convert User to Map for database insertion
  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'firstName': firstName,
      'lastName': lastName,
      'email': email,
      'passwordHash': passwordHash,
      'phone': phone,
      'profilePhoto': profilePhoto,
      'gender': gender,
      'dateOfBirth': dateOfBirth,
    };
  }

  // Create User from Map
  factory User.fromMap(Map<String, dynamic> map) {
    return User(
      id: map['id'],
      firstName: map['firstName'],
      lastName: map['lastName'],
      email: map['email'],
      passwordHash: map['passwordHash'],
      phone: map['phone'],
      profilePhoto: map['profilePhoto'],
      gender: map['gender'],
      dateOfBirth: map['dateOfBirth'],
    );
  }
}
