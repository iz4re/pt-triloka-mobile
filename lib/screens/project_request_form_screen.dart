import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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

            // Note
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
                      'Setelah pengajuan dibuat, Anda dapat menambahkan dokumen pendukung di halaman detail.',
                      style: TextStyle(
                        color: Colors.blue.shade700,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
            ),
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
}
