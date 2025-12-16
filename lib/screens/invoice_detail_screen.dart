import 'package:flutter/material.dart';
import '../models/invoice.dart';
import '../services/api_service.dart';
import 'package:intl/intl.dart';

class InvoiceDetailScreen extends StatefulWidget {
  final int invoiceId;

  const InvoiceDetailScreen({super.key, required this.invoiceId});

  @override
  State<InvoiceDetailScreen> createState() => _InvoiceDetailScreenState();
}

class _InvoiceDetailScreenState extends State<InvoiceDetailScreen> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  Invoice? _invoice;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadInvoiceDetail();
  }

  Future<void> _loadInvoiceDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiService.getInvoiceDetail(widget.invoiceId);

      if (response['success'] == true) {
        setState(() {
          _invoice = Invoice.fromJson(response['data']);
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = response['message'] ?? 'Failed to load invoice';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error loading invoice: ${e.toString()}';
        _isLoading = false;
      });
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
        title: Text(
          _invoice?.invoiceNumber ?? 'Invoice Detail',
          style: const TextStyle(
            color: Colors.black,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null || _invoice == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 60, color: Colors.red),
              const SizedBox(height: 16),
              Text(
                _errorMessage ?? 'Invoice not found',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 16),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadInvoiceDetail,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    return SingleChildScrollView(
      child: Column(
        children: [
          _buildHeaderSection(),
          const SizedBox(height: 12),
          if (_invoice!.status != 'paid' && _invoice!.vaNumber != null)
            _buildPaymentInstructionCard(),
          if (_invoice!.status != 'paid' && _invoice!.vaNumber != null)
            const SizedBox(height: 12),
          _buildItemsSection(),
          const SizedBox(height: 12),
          _buildSummarySection(),
          const SizedBox(height: 12),
          _buildPaymentHistorySection(),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildPaymentInstructionCard() {
    return Container(
      color: const Color(0xFFF3F4F6),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFF2196F3).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(
                  Icons.account_balance,
                  color: Color(0xFF2196F3),
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Instruksi Pembayaran',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      'Transfer ke Virtual Account',
                      style: TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade300),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Bank',
                  style: TextStyle(fontSize: 12, color: Colors.grey),
                ),
                const SizedBox(height: 4),
                Text(
                  _invoice!.vaBank ?? 'BCA',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const Divider(height: 16),
                const Text(
                  'Nomor Virtual Account',
                  style: TextStyle(fontSize: 12, color: Colors.grey),
                ),
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        _formatVANumber(_invoice!.vaNumber!),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'monospace',
                          color: Color(0xFF2196F3),
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.copy, size: 20),
                      onPressed: () {
                        // Copy to clipboard
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('VA Number disalin!')),
                        );
                      },
                    ),
                  ],
                ),
                const Divider(height: 16),
                const Text(
                  'Jumlah',
                  style: TextStyle(fontSize: 12, color: Colors.grey),
                ),
                const SizedBox(height: 4),
                Text(
                  _formatCurrency(_invoice!.total),
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF4CAF50),
                  ),
                ),
                if (_invoice!.vaExpiresAt != null) ...[
                  const Divider(height: 16),
                  Row(
                    children: [
                      const Icon(
                        Icons.access_time,
                        size: 16,
                        color: Colors.orange,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Berlaku sampai ${_formatDate(_invoice!.vaExpiresAt!)}',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Colors.orange,
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () {
                Navigator.pushNamed(
                  context,
                  '/payment-upload',
                  arguments: _invoice,
                );
              },
              icon: const Icon(Icons.upload_file, color: Colors.white),
              label: const Text(
                'Saya Sudah Bayar',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4CAF50),
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _formatVANumber(String vaNumber) {
    // Format: 8808 0001 234567
    if (vaNumber.length >= 14) {
      return '${vaNumber.substring(0, 4)} ${vaNumber.substring(4, 8)} ${vaNumber.substring(8)}';
    }
    return vaNumber;
  }

  Widget _buildHeaderSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Status',
                style: TextStyle(fontSize: 14, color: Colors.grey),
              ),
              _buildStatusBadge(_invoice!.status),
            ],
          ),
          const Divider(height: 24),
          _buildInfoRow('Klien', _invoice!.klienName ?? '-'),
          const SizedBox(height: 8),
          _buildInfoRow('Tanggal Invoice', _formatDate(_invoice!.invoiceDate)),
          const SizedBox(height: 8),
          _buildInfoRow('Jatuh Tempo', _formatDate(_invoice!.dueDate)),
          if (_invoice!.notes != null && _invoice!.notes!.isNotEmpty) ...[
            const SizedBox(height: 8),
            _buildInfoRow('Catatan', _invoice!.notes!),
          ],
        ],
      ),
    );
  }

  Widget _buildItemsSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'ITEM',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 12),
          ..._invoice!.items.map((item) => _buildItemRow(item)).toList(),
        ],
      ),
    );
  }

  Widget _buildItemRow(dynamic item) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.itemName,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                if (item.description != null && item.description!.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Text(
                      item.description!,
                      style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${item.quantity.toStringAsFixed(0)} x ${_formatCurrency(item.unitPrice)}',
                style: TextStyle(fontSize: 13, color: Colors.grey[600]),
              ),
              Text(
                _formatCurrency(item.subtotal),
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSummarySection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildSummaryRow('Subtotal', _invoice!.subtotal),
          const SizedBox(height: 8),
          if (_invoice!.tax > 0) _buildSummaryRow('Pajak', _invoice!.tax),
          if (_invoice!.discount > 0) ...[
            const SizedBox(height: 8),
            _buildSummaryRow(
              _invoice!.isSurveyFeeApplied ? 'Diskon (Survey Fee)' : 'Diskon',
              -_invoice!.discount,
              isDiscount: true,
            ),
          ],
          const Divider(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'TOTAL',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              Text(
                _formatCurrency(_invoice!.total),
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF2196F3),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPaymentHistorySection() {
    final hasPayments = _invoice!.totalPaid > 0;

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'RIWAYAT PEMBAYARAN',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 12),
          if (!hasPayments)
            Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 20),
                child: Text(
                  'Belum ada pembayaran',
                  style: TextStyle(fontSize: 14, color: Colors.grey[600]),
                ),
              ),
            )
          else ...[
            _buildSummaryRow(
              'Total Terbayar',
              _invoice!.totalPaid,
              isPositive: true,
            ),
            const SizedBox(height: 8),
            _buildSummaryRow(
              'Sisa',
              _invoice!.remainingBalance,
              isNegative: _invoice!.remainingBalance > 0,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 14, color: Colors.grey)),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.right,
            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500),
          ),
        ),
      ],
    );
  }

  Widget _buildSummaryRow(
    String label,
    double amount, {
    bool isDiscount = false,
    bool isPositive = false,
    bool isNegative = false,
  }) {
    Color? valueColor;
    if (isPositive) valueColor = const Color(0xFF4CAF50);
    if (isNegative) valueColor = const Color(0xFFF44336);

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 14)),
        Text(
          _formatCurrency(amount),
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w500,
            color: valueColor,
          ),
        ),
      ],
    );
  }

  Widget _buildStatusBadge(String status) {
    Color color;
    String label;

    switch (status.toLowerCase()) {
      case 'paid':
        color = const Color(0xFF4CAF50);
        label = 'Lunas';
        break;
      case 'unpaid':
        color = const Color(0xFFFFA726);
        label = 'Belum Bayar';
        break;
      case 'overdue':
        color = const Color(0xFFF44336);
        label = 'Jatuh Tempo';
        break;
      case 'cancelled':
        color = const Color(0xFF9E9E9E);
        label = 'Dibatalkan';
        break;
      default:
        color = const Color(0xFF2196F3);
        label = status;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color, width: 1.5),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 13,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd MMM yyyy').format(date);
    } catch (e) {
      return dateStr;
    }
  }

  String _formatCurrency(double amount) {
    final formatter = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return formatter.format(amount);
  }
}
