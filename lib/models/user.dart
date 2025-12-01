class User {
  final int? id;
  final String firstName;
  final String email;
  final String passwordHash;

  User({
    this.id,
    required this.firstName,
    required this.email,
    required this.passwordHash,
  });

  // Convert User to Map for database insertion
  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'firstName': firstName,
      'email': email,
      'passwordHash': passwordHash,
    };
  }

  // Create User from Map
  factory User.fromMap(Map<String, dynamic> map) {
    return User(
      id: map['id'],
      firstName: map['firstName'],
      email: map['email'],
      passwordHash: map['passwordHash'],
    );
  }
}
