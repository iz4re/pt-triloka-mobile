import 'package:flutter/material.dart';
import '../models/quotation.dart';
import '../services/api_service.dart';
import '../utils/error_handler.dart';
import 'quotation_detail_screen.dart';
import 'package:intl/intl.dart';

class QuotationListScreen extends StatefulWidget {
  const QuotationListScreen({super.key});

  @override
  State<QuotationListScreen> createState() => _QuotationListScreenState();
}

class _QuotationListScreenState extends State<QuotationListScreen> {
  final ApiService _apiService = ApiService();
  List<Quotation> _quotations = [];
  bool _isLoading = false;
  String _selectedStatus = 'all';

  @override
  void initState() {
    super.initState();
    _loadQuotations(); // FIXED: removed space
  }

  Future<void> _loadQuotations() async {
    setState(() => _isLoading = true);
    try {
      debugPrint('Loading quotations with status: $_selectedStatus');
      final response = await _apiService.getQuotations(
        status: _selectedStatus == 'all' ? null : _selectedStatus,
      );

      debugPrint('Quotations API Response: $response');

      if (response['success'] == true && mounted) {
        final List data = response['data'] ?? [];
        debugPrint('Found ${data.length} quotations');
        setState(() {
          _quotations = data.map((json) => Quotation.fromJson(json)).toList();
        });
      } else {
        debugPrint('API success is false or response null');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(response['message'] ?? 'Failed to load quotations'),
            ),
          );
        }
      }
    } catch (e) {
      debugPrint('Error loading quotations: $e');
      debugPrint('Error type: ${e.runtimeType}');
      if (mounted) {
        ErrorHandler.showError(context, e);
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'draft':
        return Colors.grey;
      case 'sent':
        return Colors.blue;
      case 'approved':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      case 'revised':
        return Colors.orange;
      case 'expired':
        return Colors.brown;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Quotation',
          style: TextStyle(
            color: Colors.black,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: Column(
        children: [
          _buildFilterChips(),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _quotations.isEmpty
                ? _buildEmptyState()
                : RefreshIndicator(
                    onRefresh: _loadQuotations,
                    child: ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _quotations.length,
                      itemBuilder: (context, index) {
                        return _buildQuotationCard(_quotations[index]);
                      },
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChips() {
    final statuses = [
      {'key': 'all', 'label': 'Semua'},
      {'key': 'sent', 'label': 'Terkirim'},
      {'key': 'approved', 'label': 'Disetujui'},
      {'key': 'rejected', 'label': 'Ditolak'},
    ];

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: statuses.map((status) {
            final isSelected = _selectedStatus == status['key'];
            return Padding(
              padding: const EdgeInsets.only(right: 8),
              child: ChoiceChip(
                label: Text(status['label']!),
                selected: isSelected,
                onSelected: (selected) {
                  if (selected) {
                    setState(() => _selectedStatus = status['key']!);
                    _loadQuotations();
                  }
                },
                selectedColor: const Color(0xFF6C5DD3),
                labelStyle: TextStyle(
                  color: isSelected ? Colors.white : Colors.black87,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildQuotationCard(Quotation quotation) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => QuotationDetailScreen(quotationId: quotation.id),
            ),
          );
          if (result == true) {
            _loadQuotations();
          }
        },
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      quotation.quotationNumber,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColor(
                        quotation.status,
                      ).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _getStatusColor(quotation.status),
                      ),
                    ),
                    child: Text(
                      quotation.getStatusLabel(),
                      style: TextStyle(
                        color: _getStatusColor(quotation.status),
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(Icons.inventory_2, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    '${quotation.items.length} item',
                    style: TextStyle(color: Colors.grey[600], fontSize: 13),
                  ),
                  const SizedBox(width: 16),
                  Icon(Icons.calendar_today, size: 14, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Flexible(
                    child: Text(
                      'Valid sampai: ${_formatDate(quotation.validUntil)}',
                      style: TextStyle(color: Colors.grey[600], fontSize: 13),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const Divider(height: 24),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Total',
                    style: TextStyle(color: Colors.grey[700], fontSize: 14),
                  ),
                  Text(
                    NumberFormat.currency(
                      locale: 'id_ID',
                      symbol: 'Rp ',
                      decimalDigits: 0,
                    ).format(quotation.total),
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF6C5DD3),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.description_outlined, size: 80, color: Colors.grey[300]),
          const SizedBox(height: 16),
          Text(
            'Belum ada quotation',
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd MMM yyyy', 'id_ID').format(date);
    } catch (e) {
      return dateStr;
    }
  }
}
