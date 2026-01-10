import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:file_picker/file_picker.dart';
import '../services/api_service.dart';

class ProjectRequestFormScreen extends StatefulWidget {
  final int? requestId;
  final Map<String, dynamic>? initialData;

  const ProjectRequestFormScreen({super.key, this.requestId, this.initialData});

  @override
  State<ProjectRequestFormScreen> createState() =>
      _ProjectRequestFormScreenState();
}

class _ProjectRequestFormScreenState extends State<ProjectRequestFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final ApiService _apiService = ApiService();

  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _locationController = TextEditingController();
  final _budgetController = TextEditingController();
  final _timelineController = TextEditingController();

  String _selectedType = 'construction';
  bool _isSubmitting = false;

  // Document upload state
  List<Map<String, dynamic>> _selectedDocuments = [];
  bool _isUploadingDocuments = false;
  int _uploadProgress = 0;

  final List<Map<String, String>> _projectTypes = [
    {'value': 'construction', 'label': 'Pembangunan'},
    {'value': 'renovation', 'label': 'Renovasi'},
    {'value': 'supply', 'label': 'Penyediaan Material'},
    {'value': 'contractor', 'label': 'Kontraktor'},
    {'value': 'other', 'label': 'Lainnya'},
  ];

  @override
  void initState() {
    super.initState();
    if (widget.initialData != null) {
      _loadInitialData();
    }
  }

  void _loadInitialData() {
    final data = widget.initialData!;
    _titleController.text = data['title'] ?? '';
    _descriptionController.text = data['description'] ?? '';
    _locationController.text = data['location'] ?? '';
    _selectedType = data['type'] ?? 'construction';

    if (data['expected_budget'] != null) {
      _budgetController.text = data['expected_budget'].toString();
    }
    _timelineController.text = data['expected_timeline'] ?? '';
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _locationController.dispose();
    _budgetController.dispose();
    _timelineController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      double? budget;
      if (_budgetController.text.isNotEmpty) {
        budget = double.tryParse(
          _budgetController.text.replaceAll(RegExp(r'[^\d]'), ''),
        );
      }

      Map<String, dynamic> response;

      if (widget.requestId != null) {
        response = await _apiService.updateProjectRequest(
          widget.requestId!,
          title: _titleController.text,
          type: _selectedType,
          description: _descriptionController.text,
          location: _locationController.text,
          expectedBudget: budget,
          expectedTimeline: _timelineController.text.isEmpty
              ? null
              : _timelineController.text,
        );
      } else {
        response = await _apiService.createProjectRequest(
          title: _titleController.text,
          type: _selectedType,
          description: _descriptionController.text,
          location: _locationController.text,
          expectedBudget: budget,
          expectedTimeline: _timelineController.text.isEmpty
              ? null
              : _timelineController.text,
        );
      }

      if (response['success'] == true) {
        // Upload documents if any
        if (_selectedDocuments.isNotEmpty && widget.requestId == null) {
          await _uploadDocuments(response['data']['id']);
        }

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                widget.requestId != null
                    ? 'Project berhasil diupdate'
                    : 'Project berhasil diajukan',
              ),
              backgroundColor: Colors.green,
            ),
          );
          Navigator.pop(context, true);
        }
      } else {
        throw Exception(response['message'] ?? 'Failed to submit');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  Future<void> _uploadDocuments(int projectId) async {
    setState(() {
      _isUploadingDocuments = true;
      _uploadProgress = 0;
    });

    int failedCount = 0;

    for (int i = 0; i < _selectedDocuments.length; i++) {
      final doc = _selectedDocuments[i];

      setState(() => _uploadProgress = i + 1);

      try {
        final response = await _apiService.uploadRequestDocument(
          projectId,
          doc['fileBytes'],
          doc['fileName'],
          documentType: doc['documentType'],
          description: doc['description'],
        );

        if (response['success'] != true) {
          failedCount++;
        }
      } catch (e) {
        debugPrint('Failed to upload ${doc['fileName']}: $e');
        failedCount++;
      }
    }

    setState(() => _isUploadingDocuments = false);

    if (failedCount > 0 && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            '$failedCount dokumen gagal diupload. Silakan coba lagi dari detail project.',
          ),
          backgroundColor: Colors.orange,
          duration: const Duration(seconds: 4),
        ),
      );
    }
  }

  Future<void> _pickDocument() async {
    try {
      FilePickerResult? result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
        withData: true,
      );

      if (result == null || !mounted) return;

      final file = result.files.first;

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

      // Check if bytes are available
      if (file.bytes == null) {
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

      // Show dialog for document type
      final metadata = await showDialog<Map<String, String?>>(
        context: context,
        builder: (context) => _DocumentTypeDialog(fileName: file.name),
      );

      if (metadata == null || !mounted) return;

      setState(() {
        _selectedDocuments.add({
          'fileBytes': file.bytes!,
          'fileName': file.name,
          'fileSize': file.size,
          'documentType': metadata['type']!,
          'description': metadata['description'],
        });
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${file.name} ditambahkan'),
          backgroundColor: Colors.green,
          duration: const Duration(seconds: 1),
        ),
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    }
  }

  void _removeDocument(int index) {
    setState(() {
      _selectedDocuments.removeAt(index);
    });
  }

  String _formatFileSize(int bytes) {
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) return '${(bytes / 1024).toStringAsFixed(1)} KB';
    return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.requestId != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Pengajuan Project' : 'Ajukan Project Baru'),
        backgroundColor: const Color(0xFF6C5DD3),
        foregroundColor: Colors.white,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Title
            TextFormField(
              controller: _titleController,
              decoration: const InputDecoration(
                labelText: 'Judul Project *',
                hintText: 'Contoh: Pembangunan Rumah 2 Lantai',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.title),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Judul project harus diisi';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // Type
            DropdownButtonFormField<String>(
              initialValue: _selectedType,
              decoration: const InputDecoration(
                labelText: 'Jenis Project *',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.category),
              ),
              items: _projectTypes.map((type) {
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

            // Description
            TextFormField(
              controller: _descriptionController,
              decoration: const InputDecoration(
                labelText: 'Deskripsi *',
                hintText: 'Jelaskan detail project Anda',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.description),
                alignLabelWithHint: true,
              ),
              maxLines: 5,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Deskripsi harus diisi';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // Location
            TextFormField(
              controller: _locationController,
              decoration: const InputDecoration(
                labelText: 'Lokasi *',
                hintText: 'Contoh: Jl. Raya No. 123, Surabaya',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.location_on),
              ),
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Lokasi harus diisi';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),

            // Expected Budget
            TextFormField(
              controller: _budgetController,
              decoration: const InputDecoration(
                labelText: 'Perkiraan Anggaran (opsional)',
                hintText: '0',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.paid),
                prefixText: 'Rp ',
              ),
              keyboardType: TextInputType.number,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            ),
            const SizedBox(height: 16),

            // Expected Timeline
            TextFormField(
              controller: _timelineController,
              decoration: const InputDecoration(
                labelText: 'Estimasi Waktu (opsional)',
                hintText: 'Contoh: 6 bulan',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.schedule),
              ),
            ),
            const SizedBox(height: 24),

            // Document Upload Section
            if (widget.requestId == null) ...[
              const Text(
                'Dokumen Pendukung (Opsional)',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 12),

              // Document Picker Button
              InkWell(
                onTap: _pickDocument,
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.grey[50],
                    border: Border.all(
                      color: const Color(0xFF6C5DD3),
                      width: 2,
                      strokeAlign: BorderSide.strokeAlignInside,
                      style: BorderStyle.solid,
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    children: [
                      Icon(
                        Icons.upload_file,
                        size: 40,
                        color: const Color(0xFF6C5DD3).withOpacity(0.7),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Tap untuk memilih file',
                        style: TextStyle(
                          color: Color(0xFF6C5DD3),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'PDF, JPG, PNG, DOC (Max 5MB)',
                        style: TextStyle(color: Colors.grey[600], fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Selected Documents List
              if (_selectedDocuments.isNotEmpty) ...[
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.grey[300]!),
                  ),
                  child: Column(
                    children: [
                      for (int i = 0; i < _selectedDocuments.length; i++)
                        _buildDocumentListItem(i),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],

              // Info note
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.blue.shade200),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: Colors.blue.shade700,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _selectedDocuments.isEmpty
                            ? 'Dokumen pendukung dapat membantu admin memahami kebutuhan project Anda.'
                            : 'Dokumen akan diupload setelah project dibuat.',
                        style: TextStyle(
                          color: Colors.blue.shade700,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 24),

            // Submit Button
            SizedBox(
              height: 50,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF6C5DD3),
                  foregroundColor: Colors.white,
                ),
                child: _isSubmitting
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            Colors.white,
                          ),
                        ),
                      )
                    : _isUploadingDocuments
                    ? Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(
                                Colors.white,
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Text(
                            'Mengupload dokumen $_uploadProgress/${_selectedDocuments.length}...',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      )
                    : Text(
                        isEdit ? 'Update Project' : 'Ajukan Project',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentListItem(int index) {
    final doc = _selectedDocuments[index];
    final isLast = index == _selectedDocuments.length - 1;

    IconData icon;
    final fileName = doc['fileName']?.toString() ?? 'document.pdf';
    if (fileName.endsWith('.pdf')) {
      icon = Icons.picture_as_pdf;
    } else if (fileName.endsWith('.jpg') ||
        fileName.endsWith('.jpeg') ||
        fileName.endsWith('.png')) {
      icon = Icons.image;
    } else {
      icon = Icons.description;
    }

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        border: isLast
            ? null
            : Border(bottom: BorderSide(color: Colors.grey[300]!)),
      ),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFF6C5DD3), size: 32),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  fileName,
                  style: const TextStyle(
                    fontWeight: FontWeight.w500,
                    fontSize: 14,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  '${_getDocumentTypeLabel(doc['documentType'])} • ${_formatFileSize(doc['fileSize'])}',
                  style: TextStyle(color: Colors.grey[600], fontSize: 12),
                ),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.close, size: 20),
            color: Colors.red,
            onPressed: () => _removeDocument(index),
          ),
        ],
      ),
    );
  }

  String _getDocumentTypeLabel(String type) {
    const types = {
      'drawing': 'Gambar/Denah',
      'rab': 'RAB',
      'permit': 'Perizinan',
      'ktp': 'KTP',
      'npwp': 'NPWP',
      'photo': 'Foto',
      'other': 'Lainnya',
    };
    return types[type] ?? type;
  }
}

class _DocumentTypeDialog extends StatefulWidget {
  final String fileName;

  const _DocumentTypeDialog({required this.fileName});

  @override
  State<_DocumentTypeDialog> createState() => _DocumentTypeDialogState();
}

class _DocumentTypeDialogState extends State<_DocumentTypeDialog> {
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
      title: const Text('Jenis Dokumen'),
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
          child: const Text('Tambah'),
        ),
      ],
    );
  }
}
