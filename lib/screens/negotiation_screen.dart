import 'package:flutter/material.dart';
class Negotiation {
  final String message;
  final String sender;
  final DateTime createdAt;

  Negotiation({
    required this.message,
    required this.sender,
    required this.createdAt,
  });

  factory Negotiation.fromJson(Map<String, dynamic> json) {
    return Negotiation(
      message: json['message'] as String,
      sender: json['sender'] as String,
      createdAt: DateTime.parse(json['createdAt'] as String),
    );
  }
}

class ApiService {
  Future<Map<String, dynamic>> getNegotiations(int quotationId) async {
    await Future.delayed(Duration(milliseconds: 500));
    return {
      'success': true,
      'data': [
        {
          'message': 'Halo, bisa nego harga?',
          'sender': 'User',
          'createdAt': DateTime.now().subtract(Duration(minutes: 5)).toIso8601String(),
        },
        {
          'message': 'Bisa, berapa penawaran Anda?',
          'sender': 'Admin',
          'createdAt': DateTime.now().subtract(Duration(minutes: 3)).toIso8601String(),
        },
      ],
    };
  }

  Future<Map<String, dynamic>> submitNegotiation(int quotationId, String message) async {
    await Future.delayed(Duration(milliseconds: 500));
    return {
      'success': true,
      'data': {
        'message': message,
        'sender': 'User',
        'createdAt': DateTime.now().toIso8601String(),
      },
    };
  }
}

class NegotiationScreen extends StatefulWidget {
  final int quotationId;
  const NegotiationScreen({Key? key, required this.quotationId}) : super(key: key);

  @override
  State<NegotiationScreen> createState() => _NegotiationScreenState();
}

class _NegotiationScreenState extends State<NegotiationScreen> {
  final ApiService _apiService = ApiService();
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  List<Negotiation> _negotiations = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadNegotiations();
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadNegotiations() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.getNegotiations(widget.quotationId);
      if (response['success'] == true && mounted) {
        setState(() {
          _negotiations = (response['data'] as List)
              .map((json) => Negotiation.fromJson(json as Map<String, dynamic>))
              .toList();
        });
        _scrollToBottom();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal memuat negosiasi')));
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _submitNegotiation() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.submitNegotiation(widget.quotationId, text);
      if (response['success'] == true && mounted) {
        setState(() {
          _negotiations.add(Negotiation.fromJson(response['data'] as Map<String, dynamic>));
          _messageController.clear();
        });
        _scrollToBottom();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal mengirim pesan')));
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Widget _buildMessageBubble(Negotiation negotiation, bool isMe) {
    return Align(
      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: EdgeInsets.symmetric(vertical: 4, horizontal: 8),
        padding: EdgeInsets.all(12),
        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.7),
        decoration: BoxDecoration(
          color: isMe ? Colors.blue[600] : Colors.grey[200],
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(18),
            topRight: Radius.circular(18),
            bottomLeft: Radius.circular(isMe ? 18 : 4),
            bottomRight: Radius.circular(isMe ? 4 : 18),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black12,
              blurRadius: 3,
              offset: Offset(0, 1),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
          children: [
            Text(
              negotiation.message,
              style: TextStyle(
                color: isMe ? Colors.white : Colors.black87,
                fontSize: 16,
              ),
            ),
            SizedBox(height: 6),
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  negotiation.sender,
                  style: TextStyle(
                    color: isMe ? Colors.white70 : Colors.black54,
                    fontSize: 12,
                  ),
                ),
                SizedBox(width: 8),
                Text(
                  _formatTime(negotiation.createdAt),
                  style: TextStyle(
                    color: isMe ? Colors.white70 : Colors.black54,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _formatTime(DateTime dt) {
    return "${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}";
  }

  @override
  Widget build(BuildContext context) {
    final myName = "User"; 
    return Scaffold(
      appBar: AppBar(
        title: Text('Negosiasi'),
        elevation: 1,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
      ),
      body: Stack(
        children: [
          Column(
            children: [
              Expanded(
                child: Container(
                  color: Colors.grey[100],
                  child: ListView.builder(
                    controller: _scrollController,
                    itemCount: _negotiations.length,
                    padding: EdgeInsets.symmetric(vertical: 12),
                    itemBuilder: (context, index) {
                      final negotiation = _negotiations[index];
                      final isMe = negotiation.sender == myName;
                      return _buildMessageBubble(negotiation, isMe);
                    },
                  ),
                ),
              ),
              SafeArea(
                child: Container(
                  padding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  color: Colors.white,
                  child: Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _messageController,
                          decoration: InputDecoration(
                            hintText: 'Tulis pesan negosiasi...',
                            filled: true,
                            fillColor: Colors.grey[100],
                            contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(24),
                              borderSide: BorderSide.none,
                            ),
                          ),
                          minLines: 1,
                          maxLines: 4,
                        ),
                      ),
                      SizedBox(width: 8),
                      Material(
                        color: Colors.blue[600],
                        shape: CircleBorder(),
                        child: IconButton(
                          icon: Icon(Icons.send, color: Colors.white),
                          onPressed: _isLoading ? null : _submitNegotiation,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          if (_isLoading)
            Positioned.fill(
              child: Container(
                color: Colors.black26,
                child: Center(child: CircularProgressIndicator()),
              ),
            ),
        ],
      ),
    );
  }
}