import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';
import '../models/project_request.dart';
import '../services/api_service.dart';
import 'project_request_form_screen.dart';

class ProjectRequestDetailScreen extends StatefulWidget {
  final int requestId;

  const ProjectRequestDetailScreen({super.key, required this.requestId});

  @override
  State<ProjectRequestDetailScreen> createState() =>
      _ProjectRequestDetailScreenState();
}

class _ProjectRequestDetailScreenState
    extends State<ProjectRequestDetailScreen> {
  final ApiService _apiService = ApiService();
  ProjectRequest? _request;
  bool _isLoading = false;
  bool _isUploading = false;

  @override
  void initState() {
    super.initState();
    _loadRequestDetail();
  }

  Future<void> _loadRequestDetail() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.getProjectRequestDetail(
        widget.requestId,
      );

      if (response['success'] == true) {
        setState(() {
          _request = ProjectRequest.fromJson(response['data']);
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

  Future<void> _deleteRequest() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Project'),
        content: const Text(
          'Apakah Anda yakin ingin menghapus pengajuan project ini?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final response = await _apiService.deleteProjectRequest(widget.requestId);

      if (response['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Project berhasil dihapus'),
              backgroundColor: Colors.green,
            ),
          );
          Navigator.pop(context, true);
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  Future<void> _uploadDocument() async {
    try {
      print('DEBUG: Starting file picker...');

      // Pick file
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
      );

      print('DEBUG: File picker result: ${result != null}');

      if (result == null || !mounted) return;

      final file = result.files.first;
      print('DEBUG: File name: ${file.name}, size: ${file.size}');

      // Validate file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('File melebihi 5MB'),
              backgroundColor: Colors.red,
            ),
          );
        }
        return;
      }

      // Get file bytes from picker
      if (file.bytes == null) {
        print('DEBUG: File bytes is null - file picker didnt return bytes');
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Gagal membaca file. Coba pilih file lain.'),
              backgroundColor: Colors.red,
            ),
          );
        }
        return;
      }

      final fileBytes = file.bytes!;
      print('DEBUG: File bytes length: ${fileBytes.length}');

      // Check mounted before showing dialog
      if (!mounted) return;

      print('DEBUG: Showing metadata dialog...');

      // Show dialog for metadata
      final metadata = await showDialog<Map<String, String?>>(
        context: context,
        builder: (context) => _UploadMetadataDialog(fileName: file.name),
      );

      print('DEBUG: Metadata: $metadata');

      if (metadata == null || !mounted) return;

      // Upload
      print('DEBUG: Starting upload...');
      setState(() => _isUploading = true);

      final response = await _apiService.uploadRequestDocument(
        widget.requestId,
        fileBytes,
        file.name,
        documentType: metadata['type']!,
        description: metadata['description'],
      );

      print('DEBUG: Upload response: $response');

      if (!mounted) return;

      if (response['success'] == true) {
        print('DEBUG: Upload successful!');
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Dokumen berhasil diupload'),
            backgroundColor: Colors.green,
          ),
        );
        _loadRequestDetail();
      } else {
        print('DEBUG: Upload failed: ${response['message']}');
        print('DEBUG: Validation errors: ${response['errors']}');

        // Show detailed error message
        String errorMsg = response['message'] ?? 'Upload failed';
        if (response['errors'] != null) {
          errorMsg += '\nErrors: ${response['errors']}';
        }
        throw Exception(errorMsg);
      }
    } catch (e) {
      print('DEBUG: Upload error: $e');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isUploading = false);
      }
    }
  }

  Future<void> _deleteDocument(int documentId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Dokumen'),
        content: const Text('Apakah Anda yakin ingin menghapus dokumen ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final response = await _apiService.deleteRequestDocument(documentId);

      if (response['success'] == true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Dokumen berhasil dihapus'),
              backgroundColor: Colors.green,
            ),
          );
          _loadRequestDetail();
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_request?.requestNumber ?? 'Detail Project'),
        backgroundColor: const Color(0xFF6C5DD3),
        foregroundColor: Colors.white,
        actions: [
          if (_request != null && _request!.canEdit())
            IconButton(
              icon: const Icon(Icons.edit),
              onPressed: () async {
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ProjectRequestFormScreen(
                      requestId: _request!.id,
                      initialData: _request!.toJson(),
                    ),
                  ),
                );
                if (result == true) {
                  _loadRequestDetail();
                }
              },
            ),
          if (_request != null && _request!.canDelete())
            IconButton(
              icon: const Icon(Icons.delete),
              onPressed: _deleteRequest,
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _request == null
          ? const Center(child: Text('Data tidak ditemukan'))
          : RefreshIndicator(
              onRefresh: _loadRequestDetail,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildRequestInfo(),
                  const SizedBox(height: 16),
                  _buildDocumentsSection(),
                ],
              ),
            ),
      floatingActionButton:
          _request != null && _request!.status == 'pending' && !_isUploading
          ? FloatingActionButton.extended(
              onPressed: _uploadDocument,
              icon: const Icon(Icons.upload_file),
              label: const Text('Upload Dokumen'),
              backgroundColor: const Color(0xFF6C5DD3),
              foregroundColor: Colors.white,
            )
          : _isUploading
          ? FloatingActionButton(
              onPressed: null,
              backgroundColor: Colors.grey,
              child: const CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
              ),
            )
          : null,
    );
  }

  Widget _buildRequestInfo() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status Badge
            Row(
              children: [
                const Text(
                  'Status:',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(_request!.status).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Text(
                    _request!.getStatusLabel(),
                    style: TextStyle(
                      color: _getStatusColor(_request!.status),
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const Divider(height: 24),

            // Title
            const Text(
              'Judul Project',
              style: TextStyle(color: Colors.grey, fontSize: 12),
            ),
            const SizedBox(height: 4),
            Text(
              _request!.title,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),

            // Type
            const Text(
              'Jenis Project',
              style: TextStyle(color: Colors.grey, fontSize: 12),
            ),
            const SizedBox(height: 4),
            Text(
              _request!.getTypeLabel(),
              style: const TextStyle(fontSize: 15),
            ),
            const SizedBox(height: 16),

            // Description
            const Text(
              'Deskripsi',
              style: TextStyle(color: Colors.grey, fontSize: 12),
            ),
            const SizedBox(height: 4),
            Text(_request!.description, style: const TextStyle(fontSize: 15)),
            const SizedBox(height: 16),

            // Location
            const Text(
              'Lokasi',
              style: TextStyle(color: Colors.grey, fontSize: 12),
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.location_on, size: 18, color: Colors.red),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    _request!.location,
                    style: const TextStyle(fontSize: 15),
                  ),
                ),
              ],
            ),

            // Expected Budget
            if (_request!.expectedBudget != null) ...[
              const SizedBox(height: 16),
              const Text(
                'Perkiraan Anggaran',
                style: TextStyle(color: Colors.grey, fontSize: 12),
              ),
              const SizedBox(height: 4),
              Text(
                NumberFormat.currency(
                  locale: 'id_ID',
                  symbol: 'Rp ',
                  decimalDigits: 0,
                ).format(_request!.expectedBudget),
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF6C5DD3),
                ),
              ),
            ],

            // Expected Timeline
            if (_request!.expectedTimeline != null &&
                _request!.expectedTimeline!.isNotEmpty) ...[
              const SizedBox(height: 16),
              const Text(
                'Estimasi Waktu',
                style: TextStyle(color: Colors.grey, fontSize: 12),
              ),
              const SizedBox(height: 4),
              Row(
                children: [
                  const Icon(Icons.schedule, size: 18, color: Colors.blue),
                  const SizedBox(width: 4),
                  Text(
                    _request!.expectedTimeline!,
                    style: const TextStyle(fontSize: 15),
                  ),
                ],
              ),
            ],

            const SizedBox(height: 16),
            const Divider(height: 1),
            const SizedBox(height: 16),

            // Created Date
            Row(
              children: [
                const Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                const SizedBox(width: 8),
                Text(
                  'Diajukan: ${_formatDate(_request!.createdAt)}',
                  style: const TextStyle(color: Colors.grey, fontSize: 13),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentsSection() {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.attach_file, color: Color(0xFF6C5DD3)),
                const SizedBox(width: 8),
                const Text(
                  'Dokumen Pendukung',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                const Spacer(),
                Text(
                  '${_request!.documents.length} file',
                  style: TextStyle(color: Colors.grey[600], fontSize: 13),
                ),
              ],
            ),
            const Divider(height: 24),
            if (_request!.documents.isEmpty)
              Center(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  child: Column(
                    children: [
                      Icon(
                        Icons.folder_open,
                        size: 48,
                        color: Colors.grey[300],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Belum ada dokumen',
                        style: TextStyle(color: Colors.grey[600], fontSize: 14),
                      ),
                      if (_request!.status == 'pending') ...[
                        const SizedBox(height: 4),
                        Text(
                          'Tap tombol "Upload Dokumen" untuk menambahkan',
                          style: TextStyle(
                            color: Colors.grey[500],
                            fontSize: 12,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ],
                  ),
                ),
              )
            else
              ...(_request!.documents.map((doc) => _buildDocumentItem(doc))),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentItem(RequestDocument doc) {
    Color statusColor = doc.verificationStatus == 'verified'
        ? Colors.green
        : doc.verificationStatus == 'rejected'
        ? Colors.red
        : Colors.orange;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey.shade300),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                _getDocumentIcon(doc.fileType),
                color: const Color(0xFF6C5DD3),
                size: 24,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      doc.fileName,
                      style: const TextStyle(
                        fontWeight: FontWeight.w500,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${doc.getDocumentTypeLabel()} • ${doc.getFileSizeFormatted()}',
                      style: TextStyle(color: Colors.grey[600], fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  doc.getStatusLabel(),
                  style: TextStyle(
                    color: statusColor,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              if (_request!.status == 'pending')
                IconButton(
                  icon: const Icon(Icons.delete, size: 20),
                  color: Colors.red,
                  onPressed: () => _deleteDocument(doc.id),
                ),
            ],
          ),
          if (doc.description != null && doc.description!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              doc.description!,
              style: TextStyle(color: Colors.grey[700], fontSize: 13),
            ),
          ],
        ],
      ),
    );
  }

  IconData _getDocumentIcon(String? mimeType) {
    if (mimeType == null) return Icons.description;

    if (mimeType.contains('pdf')) {
      return Icons.picture_as_pdf;
    } else if (mimeType.contains('image')) {
      return Icons.image;
    } else if (mimeType.contains('doc') || mimeType.contains('word')) {
      return Icons.description;
    }
    return Icons.attach_file;
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd MMMM yyyy, HH:mm', 'id_ID').format(date);
    } catch (e) {
      return dateStr;
    }
  }
}

class _UploadMetadataDialog extends StatefulWidget {
  final String fileName;

  const _UploadMetadataDialog({required this.fileName});

  @override
  State<_UploadMetadataDialog> createState() => _UploadMetadataDialogState();
}

class _UploadMetadataDialogState extends State<_UploadMetadataDialog> {
  String _selectedType = 'drawing';
  final _descriptionController = TextEditingController();

  final List<Map<String, String>> _documentTypes = [
    {'value': 'drawing', 'label': 'Gambar/Denah'},
    {'value': 'rab', 'label': 'RAB (Budget)'},
    {'value': 'permit', 'label': 'Dokumen Perizinan'},
    {'value': 'ktp', 'label': 'KTP'},
    {'value': 'npwp', 'label': 'NPWP'},
    {'value': 'photo', 'label': 'Foto'},
    {'value': 'other', 'label': 'Lainnya'},
  ];

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Upload Dokumen'),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'File: ${widget.fileName}',
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _selectedType,
              decoration: const InputDecoration(
                labelText: 'Jenis Dokumen',
                border: OutlineInputBorder(),
                isDense: true,
              ),
              items: _documentTypes.map((type) {
                return DropdownMenuItem(
                  value: type['value'],
                  child: Text(type['label']!),
                );
              }).toList(),
              onChanged: (value) {
                setState(() => _selectedType = value!);
              },
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _descriptionController,
              decoration: const InputDecoration(
                labelText: 'Deskripsi (opsional)',
                hintText: 'Keterangan dokumen',
                border: OutlineInputBorder(),
                isDense: true,
              ),
              maxLines: 3,
            ),
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Batal'),
        ),
        ElevatedButton(
          onPressed: () {
            final result = <String, String?>{
              'type': _selectedType,
              'description': _descriptionController.text.isEmpty
                  ? null
                  : _descriptionController.text,
            };
            Navigator.pop(context, result);
          },
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF6C5DD3),
            foregroundColor: Colors.white,
          ),
          child: const Text('Upload'),
        ),
      ],
    );
  }
}
