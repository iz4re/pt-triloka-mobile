import 'package:flutter/material.dart';
import '../models/quotation.dart';
import '../services/api_service.dart';
import '../utils/error_handler.dart';
import 'package:intl/intl.dart';

class QuotationDetailScreen extends StatefulWidget {
  final int quotationId;

  const QuotationDetailScreen({super.key, required this.quotationId});

  @override
  State<QuotationDetailScreen> createState() => _QuotationDetailScreenState();
}

class _QuotationDetailScreenState extends State<QuotationDetailScreen> {
  final ApiService _apiService = ApiService();
  Quotation? _quotation;
  bool _isLoading = true;
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    _loadQuotation();
  }

  Future<void> _loadQuotation() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.getQuotationDetail(widget.quotationId);

      if (response['success'] == true && mounted) {
        setState(() {
          _quotation = Quotation.fromJson(response['data']);
        });
      }
    } catch (e) {
      if (mounted) {
        ErrorHandler.showError(context, e);
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _approveQuotation() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Setujui Quotation?',
          style: TextStyle(color: Colors.white),
        ),
        content: const Text(
          'Anda yakin ingin menyetujui quotation ini? Status project request akan berubah menjadi Disetujui.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
            child: const Text('Setuju'),
          ),
        ],
      ),
    );

    if (confirm == true && mounted) {
      setState(() => _isProcessing = true);
      try {
        final response = await _apiService.approveQuotation(widget.quotationId);

        if (response['success'] == true && mounted) {
          ErrorHandler.showSuccess(context, 'Quotation berhasil disetujui!');
          Navigator.pop(context, true);
        } else if (mounted) {
          ErrorHandler.showError(
            context,
            response['message'] ?? 'Gagal menyetujui quotation',
          );
        }
      } catch (e) {
        if (mounted) {
          ErrorHandler.showError(context, e);
        }
      } finally {
        if (mounted) {
          setState(() => _isProcessing = false);
        }
      }
    }
  }

  Future<void> _rejectQuotation() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tolak Quotation?'),
        content: const Text('Anda yakin ingin menolak quotation ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Tolak'),
          ),
        ],
      ),
    );

    if (confirm == true && mounted) {
      setState(() => _isProcessing = true);
      try {
        final response = await _apiService.rejectQuotation(widget.quotationId);

        if (response['success'] == true && mounted) {
          ErrorHandler.showSuccess(context, 'Quotation ditolak');
          Navigator.pop(context, true);
        }
      } catch (e) {
        if (mounted) {
          ErrorHandler.showError(context, e);
        }
      } finally {
        if (mounted) {
          setState(() => _isProcessing = false);
        }
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
          'Detail Quotation',
          style: TextStyle(
            color: Colors.black,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _quotation == null
          ? const Center(child: Text('Quotation tidak ditemukan'))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeaderCard(),
                  const SizedBox(height: 16),
                  _buildItemsCard(),
                  const SizedBox(height: 16),
                  _buildTotalCard(),
                  if (_quotation!.notes != null) ...[
                    const SizedBox(height: 16),
                    _buildNotesCard(),
                  ],
                  if (_quotation!.canApprove()) ...[
                    const SizedBox(height: 24),
                    _buildActionButtons(),
                  ],
                  if (_quotation!.isExpired()) ...[
                    const SizedBox(height: 16),
                    _buildExpiredWarning(),
                  ],
                ],
              ),
            ),
    );
  }

  Widget _buildHeaderCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  _quotation!.quotationNumber,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(
                      _quotation!.status,
                    ).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: _getStatusColor(_quotation!.status),
                    ),
                  ),
                  child: Text(
                    _quotation!.getStatusLabel(),
                    style: TextStyle(
                      color: _getStatusColor(_quotation!.status),
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
                Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Text(
                  'Valid sampai: ${_formatDate(_quotation!.validUntil)}',
                  style: TextStyle(color: Colors.grey[600], fontSize: 13),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(Icons.history, size: 16, color: Colors.grey[600]),
                const SizedBox(width: 4),
                Text(
                  'Versi ${_quotation!.version}',
                  style: TextStyle(color: Colors.grey[600], fontSize: 13),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildItemsCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Item Quotation',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            ..._quotation!.items.asMap().entries.map((entry) {
              final index = entry.key;
              final item = entry.value;
              return Column(
                children: [
                  if (index > 0) const Divider(height: 24),
                  _buildItemRow(item),
                ],
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildItemRow(QuotationItem item) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.itemName,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  if (item.category != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      item.getCategoryLabel(),
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              flex: 2,
              child: Text(
                '${item.quantity} ${item.unit} × ${NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(item.unitPrice)}',
                style: TextStyle(fontSize: 12, color: Colors.grey[700]),
                overflow: TextOverflow.ellipsis,
                maxLines: 1,
              ),
            ),
            const SizedBox(width: 8),
            Text(
              NumberFormat.currency(
                locale: 'id_ID',
                symbol: 'Rp ',
                decimalDigits: 0,
              ).format(item.subtotal),
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        if (item.description != null) ...[
          const SizedBox(height: 4),
          Text(
            item.description!,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey[600],
              fontStyle: FontStyle.italic,
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildTotalCard() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildTotalRow('Subtotal', _quotation!.subtotal, false),
            if (_quotation!.tax > 0) ...[
              const SizedBox(height: 8),
              _buildTotalRow('Pajak', _quotation!.tax, false),
            ],
            if (_quotation!.discount > 0) ...[
              const SizedBox(height: 8),
              _buildTotalRow('Diskon', -_quotation!.discount, false),
            ],
            const Divider(height: 24),
            _buildTotalRow('TOTAL', _quotation!.total, true),
          ],
        ),
      ),
    );
  }

  Widget _buildTotalRow(String label, double amount, bool isBold) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: isBold ? 16 : 14,
            fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            color: isBold ? Colors.black : Colors.grey[700],
          ),
        ),
        Text(
          NumberFormat.currency(
            locale: 'id_ID',
            symbol: 'Rp ',
            decimalDigits: 0,
          ).format(amount),
          style: TextStyle(
            fontSize: isBold ? 18 : 14,
            fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
            color: isBold ? const Color(0xFF6C5DD3) : Colors.black87,
          ),
        ),
      ],
    );
  }

  Widget _buildNotesCard() {
    return SizedBox(
      width: double.infinity,
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Catatan',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),
              Text(
                _quotation!.notes!,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[700],
                  height: 1.6,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildActionButtons() {
    return Row(
      children: [
        Expanded(
          child: OutlinedButton(
            onPressed: _isProcessing ? null : _rejectQuotation,
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.red,
              side: const BorderSide(color: Colors.red),
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
            child: _isProcessing
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Tolak'),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          flex: 2,
          child: ElevatedButton(
            onPressed: _isProcessing ? null : _approveQuotation,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
            child: _isProcessing
                ? const SizedBox(
                    height: 20,
                    width: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Text(
                    'Setujui Quotation',
                    style: TextStyle(color: Colors.white),
                  ),
          ),
        ),
      ],
    );
  }

  Widget _buildExpiredWarning() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.orange.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.orange),
      ),
      child: Row(
        children: [
          const Icon(Icons.warning, color: Colors.orange),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'Quotation ini telah melewati tanggal berlaku',
              style: TextStyle(color: Colors.orange[900], fontSize: 13),
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
