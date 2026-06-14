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

  final List<Map<String, dynamic>> _types = [
    {
      'label': 'Congé',
      'icon': Icons.flight_takeoff_rounded,
      'color': const Color(0xFF818CF8),
    },
    {
      'label': 'Permission',
      'icon': Icons.directions_run_rounded,
      'color': Color(0xFFF59E0B),
    },
    {
      'label': 'Maladie',
      'icon': Icons.medical_services_rounded,
      'color': Color(0xFFEF4444),
    },
  ];

  @override
  void dispose() {
    _motifController.dispose();
    super.dispose();
  }

  Future<void> _pickDate(bool isStart) async {
    final initialDate = isStart
        ? (_startDate ?? DateTime.now())
        : (_endDate ?? _startDate ?? DateTime.now());

    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: DateTime.now().subtract(const Duration(days: 30)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.dark(
              primary: AppColors.accent,
              onPrimary: Colors.white,
              surface: AppColors.surfaceHigh,
              onSurface: AppColors.textPri,
            ),
            dialogBackgroundColor: AppColors.surfaceHigh,
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
        SnackBar(
          content: Text('Veuillez sélectionner les dates',
              style: GoogleFonts.inter(fontWeight: FontWeight.w500)),
          backgroundColor: AppColors.red,
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
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
        SnackBar(
          content: Text('Demande soumise avec succès',
              style: GoogleFonts.inter(fontWeight: FontWeight.w500)),
          backgroundColor: AppColors.green,
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Erreur',
              style: GoogleFonts.inter(fontWeight: FontWeight.w500)),
          backgroundColor: AppColors.red,
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: Column(
        children: [
          // ── Header ─────────────────────────────────────────────────────────
          Container(
            decoration: BoxDecoration(
              gradient: AppColors.heroGradient,
              border: Border(
                  bottom: BorderSide(
                      color: AppColors.accent.withOpacity(0.15))),
            ),
            child: SafeArea(
              bottom: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(8, 8, 16, 18),
                child: Row(
                  children: [
                    IconButton(
                      icon: Icon(Icons.close_rounded,
                          color: AppColors.textPri, size: 22),
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Nouvelle Demande',
                      style: GoogleFonts.outfit(
                        color: AppColors.textPri,
                        fontWeight: FontWeight.w800,
                        fontSize: 20,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // ── Formulaire ──────────────────────────────────────────────────────
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24.0),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // ── Type selector : 3 cartes ───────────────────────────
                    _buildSectionLabel('Type de demande'),
                    const SizedBox(height: 12),
                    Row(
                      children: _types.map((t) {
                        final isSelected = _selectedType == t['label'];
                        final color = t['color'] as Color;
                        return Expanded(
                          child: GestureDetector(
                            onTap: () =>
                                setState(() => _selectedType = t['label']),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              margin: EdgeInsets.only(
                                right: t == _types.last ? 0 : 10,
                              ),
                              padding: const EdgeInsets.symmetric(
                                  vertical: 16, horizontal: 8),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? color.withOpacity(0.15)
                                    : AppColors.surfaceHigh,
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(
                                  color: isSelected
                                      ? color.withOpacity(0.6)
                                      : AppColors.border,
                                  width: isSelected ? 1.5 : 1,
                                ),
                                boxShadow: isSelected
                                    ? [
                                        BoxShadow(
                                          color: color.withOpacity(0.15),
                                          blurRadius: 12,
                                          offset: const Offset(0, 4),
                                        )
                                      ]
                                    : [],
                              ),
                              child: Column(
                                children: [
                                  Icon(t['icon'] as IconData,
                                      color: isSelected
                                          ? color
                                          : AppColors.textSec,
                                      size: 24),
                                  const SizedBox(height: 8),
                                  Text(
                                    t['label'] as String,
                                    style: GoogleFonts.inter(
                                      color: isSelected
                                          ? color
                                          : AppColors.textSec,
                                      fontSize: 12,
                                      fontWeight: isSelected
                                          ? FontWeight.w700
                                          : FontWeight.w400,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      }).toList(),
                    ),

                    const SizedBox(height: 28),

                    // ── Dates ─────────────────────────────────────────────
                    _buildSectionLabel('Période concernée'),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Début',
                                  style: GoogleFonts.inter(
                                      color: AppColors.textSec,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600)),
                              const SizedBox(height: 6),
                              _buildDateBox(
                                date: _startDate,
                                onTap: () => _pickDate(true),
                              ),
                            ],
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          child: Icon(Icons.arrow_forward_rounded,
                              color: AppColors.textSec, size: 20),
                        ),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Fin',
                                  style: GoogleFonts.inter(
                                      color: AppColors.textSec,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600)),
                              const SizedBox(height: 6),
                              _buildDateBox(
                                date: _endDate,
                                onTap: () => _pickDate(false),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    // Nombre de jours calculé
                    if (_startDate != null && _endDate != null) ...[
                      const SizedBox(height: 10),
                      Center(
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 6),
                          decoration: BoxDecoration(
                            color: AppColors.accent.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                                color: AppColors.accent.withOpacity(0.2)),
                          ),
                          child: Text(
                            '${_endDate!.difference(_startDate!).inDays + 1} jour(s)',
                            style: GoogleFonts.outfit(
                              color: AppColors.accentSoft,
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ),
                    ],

                    const SizedBox(height: 28),

                    // ── Motif ────────────────────────────────────────────
                    _buildSectionLabel('Motif de l\'absence'),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _motifController,
                      maxLines: 4,
                      maxLength: 300,
                      style: GoogleFonts.inter(
                          color: AppColors.textPri, fontSize: 14),
                      decoration: InputDecoration(
                        hintText: 'Décrivez brièvement la raison...',
                        hintStyle: GoogleFonts.inter(
                            color: AppColors.textSec.withOpacity(0.5),
                            fontSize: 14),
                        filled: true,
                        fillColor: AppColors.surfaceHigh,
                        counterStyle:
                            GoogleFonts.inter(color: AppColors.textSec, fontSize: 11),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(18),
                          borderSide: BorderSide(color: AppColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(18),
                          borderSide: BorderSide(color: AppColors.border),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(18),
                          borderSide: BorderSide(
                              color: AppColors.accent.withOpacity(0.6),
                              width: 1.5),
                        ),
                        contentPadding: const EdgeInsets.all(18),
                      ),
                      validator: (val) {
                        if (val == null || val.trim().isEmpty) {
                          return 'Le motif est requis';
                        }
                        return null;
                      },
                    ),

                    const SizedBox(height: 36),

                    // ── Bouton Soumettre ─────────────────────────────────
                    GestureDetector(
                      onTap: _isSubmitting ? null : _submit,
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        width: double.infinity,
                        height: 58,
                        decoration: BoxDecoration(
                          gradient: _isSubmitting
                              ? LinearGradient(
                                  colors: [
                                    AppColors.accent.withOpacity(0.5),
                                    AppColors.accent.withOpacity(0.5),
                                  ],
                                )
                              : AppColors.accentGradient,
                          borderRadius: BorderRadius.circular(18),
                          boxShadow: _isSubmitting
                              ? []
                              : [
                                  BoxShadow(
                                    color: AppColors.accentGlow,
                                    blurRadius: 20,
                                    offset: const Offset(0, 6),
                                  )
                                ],
                        ),
                        child: Center(
                          child: _isSubmitting
                              ? const SizedBox(
                                  height: 22,
                                  width: 22,
                                  child: CircularProgressIndicator(
                                      color: Colors.white, strokeWidth: 2.5),
                                )
                              : Text(
                                  'SOUMETTRE LA DEMANDE',
                                  style: GoogleFonts.outfit(
                                    color: Colors.white,
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 0.8,
                                  ),
                                ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionLabel(String text) {
    return Text(
      text,
      style: GoogleFonts.outfit(
        color: AppColors.textPri,
        fontWeight: FontWeight.w700,
        fontSize: 15,
      ),
    );
  }

  Widget _buildDateBox({required DateTime? date, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 16),
        decoration: BoxDecoration(
          color: AppColors.surfaceHigh,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: date != null
                ? AppColors.accent.withOpacity(0.35)
                : AppColors.border,
          ),
        ),
        child: Row(
          children: [
            Icon(
              Icons.calendar_today_rounded,
              color: date != null ? AppColors.accentSoft : AppColors.textSec,
              size: 16,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                date != null
                    ? DateFormat('dd/MM/yyyy').format(date)
                    : 'Sélectionner',
                style: GoogleFonts.inter(
                  color: date != null ? AppColors.textPri : AppColors.textSec,
                  fontSize: 13,
                  fontWeight:
                      date != null ? FontWeight.w600 : FontWeight.normal,
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
