import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../theme_manager.dart';
import '../services/leave_service.dart';

class LeaveFormScreen extends StatefulWidget {
  final int employeeId;

  const LeaveFormScreen({super.key, required this.employeeId});

  @override
  State<LeaveFormScreen> createState() => _LeaveFormScreenState();
}

class _LeaveFormScreenState extends State<LeaveFormScreen> {
  final LeaveService _leaveSvc = LeaveService();
  final _formKey = GlobalKey<FormState>();

  String _selectedType = 'Congé';
  DateTime? _startDate;
  DateTime? _endDate;
  final TextEditingController _motifController = TextEditingController();
  
  bool _isSubmitting = false;

  final List<String> _types = ['Congé', 'Permission', 'Maladie'];

  Future<void> _pickDate(bool isStart) async {
    final initialDate = isStart
        ? (_startDate ?? DateTime.now())
        : (_endDate ?? _startDate ?? DateTime.now());
    
    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: DateTime.now().subtract(const Duration(days: 30)), // Permet demandes rtroactives maladie
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: AppColors.accent,
              onPrimary: Colors.white,
              onSurface: AppColors.textPri,
              surface: AppColors.bg,
            ),
            dialogBackgroundColor: AppColors.card,
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
          if (_endDate != null && _endDate!.isBefore(_startDate!)) {
            _endDate = _startDate;
          }
        } else {
          _endDate = picked;
          if (_startDate != null && _startDate!.isAfter(_endDate!)) {
            _startDate = _endDate;
          }
        }
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_startDate == null || _endDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: const Text('Veuillez slectionner les dates'), backgroundColor: AppColors.red),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    final result = await _leaveSvc.submitLeave(
      employeeId: widget.employeeId,
      type: _selectedType,
      dateDebut: DateFormat('yyyy-MM-dd').format(_startDate!),
      dateFin: DateFormat('yyyy-MM-dd').format(_endDate!),
      motif: _motifController.text.trim(),
    );

    setState(() => _isSubmitting = false);

    if (!mounted) return;

    if (result['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: const Text('Demande soumise avec succs'), backgroundColor: AppColors.green),
      );
      Navigator.pop(context, true); // true = rafraichir
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'] ?? 'Erreur'), backgroundColor: AppColors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        backgroundColor: AppColors.bg,
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: Icon(Icons.close_rounded, color: AppColors.textPri, size: 24),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Nouvelle Demande',
          style: GoogleFonts.outfit(
            color: AppColors.textPri,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildLabel('Type de demande'),
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                decoration: BoxDecoration(
                  color: AppColors.card,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.textSec.withValues(alpha: 0.2)),
                ),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: _selectedType,
                    isExpanded: true,
                    dropdownColor: AppColors.card,
                    icon: Icon(Icons.keyboard_arrow_down_rounded, color: AppColors.textSec),
                    style: GoogleFonts.inter(color: AppColors.textPri, fontSize: 16),
                    onChanged: (String? newValue) {
                      if (newValue != null) setState(() => _selectedType = newValue);
                    },
                    items: _types.map<DropdownMenuItem<String>>((String value) {
                      return DropdownMenuItem<String>(
                        value: value,
                        child: Text(value),
                      );
                    }).toList(),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildLabel('Date de dbut'),
                        const SizedBox(height: 10),
                        _buildDateBox(
                          date: _startDate,
                          onTap: () => _pickDate(true),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildLabel('Date de fin'),
                        const SizedBox(height: 10),
                        _buildDateBox(
                          date: _endDate,
                          onTap: () => _pickDate(false),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              _buildLabel('Motif de l\'absence'),
              const SizedBox(height: 10),
              TextFormField(
                controller: _motifController,
                maxLines: 4,
                style: GoogleFonts.inter(color: AppColors.textPri, fontSize: 15),
                decoration: InputDecoration(
                  hintText: 'Dcrivez brivement la raison...',
                  hintStyle: GoogleFonts.inter(color: AppColors.textSec),
                  filled: true,
                  fillColor: AppColors.card,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: AppColors.textSec.withValues(alpha: 0.2)),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: AppColors.textSec.withValues(alpha: 0.2)),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: BorderSide(color: AppColors.accent),
                  ),
                ),
                validator: (val) {
                  if (val == null || val.trim().isEmpty) return 'Le motif est requis';
                  return null;
                },
              ),
              const SizedBox(height: 40),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.accent,
                    padding: const EdgeInsets.symmetric(vertical: 18),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    elevation: 4,
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : Text(
                          'SOUMETTRE',
                          style: GoogleFonts.outfit(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1.0,
                          ),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Text(
      text,
      style: GoogleFonts.inter(
        color: AppColors.textPri,
        fontWeight: FontWeight.w600,
        fontSize: 14,
      ),
    );
  }

  Widget _buildDateBox({required DateTime? date, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        decoration: BoxDecoration(
          color: AppColors.card,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.textSec.withValues(alpha: 0.2)),
        ),
        child: Row(
          children: [
            Icon(Icons.calendar_today_rounded, color: AppColors.textSec, size: 18),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                date != null ? DateFormat('dd/MM/yyyy').format(date) : 'Slectionner',
                style: GoogleFonts.inter(
                  color: date != null ? AppColors.textPri : AppColors.textSec,
                  fontSize: 14,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
