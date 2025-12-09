import 'package:flutter/material.dart';
import '../models/project_request.dart';
import '../services/api_service.dart';
import 'project_request_form_screen.dart';
import 'project_request_detail_screen.dart';
import 'package:intl/intl.dart';

class ProjectRequestListScreen extends StatefulWidget {
  const ProjectRequestListScreen({super.key});

  @override
  State<ProjectRequestListScreen> createState() =>
      _ProjectRequestListScreenState();
}

class _ProjectRequestListScreenState extends State<ProjectRequestListScreen> {
  final ApiService _apiService = ApiService();
  List<ProjectRequest> _requests = [];
  bool _isLoading = false;
  String _selectedStatus = 'all';

  final List<Map<String, String>> _statusTabs = [
    {'key': 'all', 'label': 'Semua'},
    {'key': 'pending', 'label': 'Menunggu'},
    {'key': 'quoted', 'label': 'Dikutip'},
    {'key': 'negotiating', 'label': 'Negosiasi'},
    {'key': 'approved', 'label': 'Disetujui'},
  ];

  @override
  void initState() {
    super.initState();
    _loadRequests();
  }

  Future<void> _loadRequests() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.getProjectRequests(
        status: _selectedStatus == 'all' ? null : _selectedStatus,
      );

      if (response['success'] == true) {
        final List data = response['data'] ?? [];
        setState(() {
          _requests = data
              .map((json) => ProjectRequest.fromJson(json))
              .toList();
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'quoted':
        return Colors.blue;
      case 'negotiating':
        return Colors.purple;
      case 'approved':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      case 'cancelled':
        return Colors.grey;
      default:
        return Colors.grey;
    }
  }

  IconData _getTypeIcon(String type) {
    switch (type.toLowerCase()) {
      case 'construction':
        return Icons.construction;
      case 'renovation':
        return Icons.home_repair_service;
      case 'supply':
        return Icons.inventory_2;
      case 'contractor':
        return Icons.engineering;
      default:
        return Icons.business;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pengajuan Project'),
        backgroundColor: const Color(0xFF6C5DD3),
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // Status Filter Tabs
          Container(
            color: Colors.white,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
              child: Row(
                children: _statusTabs.map((tab) {
                  final isSelected = _selectedStatus == tab['key'];
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                    child: ChoiceChip(
                      label: Text(tab['label']!),
                      selected: isSelected,
                      onSelected: (selected) {
                        if (selected) {
                          setState(() => _selectedStatus = tab['key']!);
                          _loadRequests();
                        }
                      },
                      selectedColor: const Color(0xFF6C5DD3),
                      labelStyle: TextStyle(
                        color: isSelected ? Colors.white : Colors.black87,
                        fontWeight: isSelected
                            ? FontWeight.bold
                            : FontWeight.normal,
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
          ),
          const Divider(height: 1),

          // Request List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _requests.isEmpty
                ? _buildEmptyState()
                : RefreshIndicator(
                    onRefresh: _loadRequests,
                    child: ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _requests.length,
                      itemBuilder: (context, index) {
                        return _buildRequestCard(_requests[index]);
                      },
                    ),
                  ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const ProjectRequestFormScreen()),
          );
          if (result == true) {
            _loadRequests();
          }
        },
        icon: const Icon(Icons.add),
        label: const Text('Ajukan Project'),
        backgroundColor: const Color(0xFF6C5DD3),
        foregroundColor: Colors.white,
      ),
    );
  }

  Widget _buildRequestCard(ProjectRequest request) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => ProjectRequestDetailScreen(requestId: request.id),
            ),
          );
          if (result == true) {
            _loadRequests();
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
                  Icon(
                    _getTypeIcon(request.type),
                    color: const Color(0xFF6C5DD3),
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      request.title,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColor(request.status).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      request.getStatusLabel(),
                      style: TextStyle(
                        color: _getStatusColor(request.status),
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                request.requestNumber,
                style: TextStyle(color: Colors.grey[600], fontSize: 12),
              ),
              const SizedBox(height: 4),
              Text(
                request.getTypeLabel(),
                style: TextStyle(color: Colors.grey[700], fontSize: 13),
              ),
              if (request.expectedBudget != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.paid, size: 16, color: Colors.grey[600]),
                    const SizedBox(width: 4),
                    Text(
                      NumberFormat.currency(
                        locale: 'id_ID',
                        symbol: 'Rp ',
                        decimalDigits: 0,
                      ).format(request.expectedBudget),
                      style: TextStyle(
                        color: Colors.grey[700],
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ],
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.calendar_today, size: 14, color: Colors.grey[500]),
                  const SizedBox(width: 4),
                  Text(
                    _formatDate(request.createdAt),
                    style: TextStyle(color: Colors.grey[600], fontSize: 12),
                  ),
                  const Spacer(),
                  if (request.documents.isNotEmpty)
                    Row(
                      children: [
                        Icon(
                          Icons.attach_file,
                          size: 14,
                          color: Colors.grey[500],
                        ),
                        const SizedBox(width: 2),
                        Text(
                          '${request.documents.length} dokumen',
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 12,
                          ),
                        ),
                      ],
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
          Icon(
            Icons.business_center_outlined,
            size: 80,
            color: Colors.grey[300],
          ),
          const SizedBox(height: 16),
          Text(
            'Belum ada pengajuan project',
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Tap tombol + untuk ajukan project baru',
            style: TextStyle(fontSize: 14, color: Colors.grey[500]),
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
