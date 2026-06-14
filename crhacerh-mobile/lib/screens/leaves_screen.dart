import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import '../theme_manager.dart';
import '../services/leave_service.dart';
import 'leave_form_screen.dart';

class LeavesScreen extends StatefulWidget {
  final int employeeId;
  const LeavesScreen({super.key, required this.employeeId});

  @override
  State<LeavesScreen> createState() => _LeavesScreenState();
}

class _LeavesScreenState extends State<LeavesScreen> {
  final LeaveService _leaveSvc = LeaveService();
  bool _isLoading = true;
  List<dynamic> _leaves = [];

  @override
  void initState() {
    super.initState();
    _loadLeaves();
  }

  Future<void> _loadLeaves() async {
    if (mounted) setState(() => _isLoading = true);

    final result = await _leaveSvc.fetchLeaves(widget.employeeId);

    if (mounted) {
      if (result['success'] == true) {
        setState(() {
          _leaves = result['data'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
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
  }

  Color _getStatusColor(String status) {
    if (status.toLowerCase().contains('approuv')) return AppColors.green;
    if (status.toLowerCase().contains('rejet')) return AppColors.red;
    return AppColors.orange;
  }

  IconData _getStatusIcon(String status) {
    if (status.toLowerCase().contains('approuv')) return Icons.check_circle_rounded;
    if (status.toLowerCase().contains('rejet')) return Icons.cancel_rounded;
    return Icons.pending_rounded;
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd/MM/yyyy').format(date);
    } catch (_) {
      return dateStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: Column(
        children: [
          // ── AppBar redesignée ──────────────────────────────────────────────
          Container(
            decoration: BoxDecoration(
              gradient: AppColors.heroGradient,
              border: Border(
                bottom: BorderSide(
                    color: AppColors.accent.withOpacity(0.15)),
              ),
            ),
            child: SafeArea(
              bottom: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(8, 8, 16, 18),
                child: Row(
                  children: [
                    IconButton(
                      icon: Icon(Icons.arrow_back_ios_new_rounded,
                          color: AppColors.textPri, size: 20),
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Congés & Permissions',
                            style: GoogleFonts.outfit(
                              color: AppColors.textPri,
                              fontWeight: FontWeight.w800,
                              fontSize: 20,
                            ),
                          ),
                          Text(
                            '${_leaves.length} demande${_leaves.length > 1 ? 's' : ''}',
                            style: GoogleFonts.inter(
                              color: AppColors.textSec,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // ── Contenu ────────────────────────────────────────────────────────
          Expanded(
            child: _isLoading
                ? Center(
                    child: CircularProgressIndicator(
                        color: AppColors.accentSoft, strokeWidth: 2.5))
                : _leaves.isEmpty
                    ? _buildEmptyState()
                    : RefreshIndicator(
                        color: AppColors.accentSoft,
                        backgroundColor: AppColors.surfaceHigh,
                        onRefresh: _loadLeaves,
                        child: ListView.separated(
                          padding: const EdgeInsets.fromLTRB(20, 20, 20, 120),
                          itemCount: _leaves.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 14),
                          itemBuilder: (context, index) {
                            return _buildLeaveCard(_leaves[index]);
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
            MaterialPageRoute(
              builder: (context) =>
                  LeaveFormScreen(employeeId: widget.employeeId),
            ),
          );
          if (result == true) _loadLeaves();
        },
        backgroundColor: AppColors.accent,
        elevation: 8,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        icon: const Icon(Icons.add_rounded, color: Colors.white, size: 24),
        label: Text(
          'Nouvelle demande',
          style: GoogleFonts.outfit(
              color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: AppColors.surfaceHigh,
              shape: BoxShape.circle,
              border: Border.all(color: AppColors.border),
            ),
            child: Icon(Icons.event_busy_rounded,
                size: 48, color: AppColors.textSec.withOpacity(0.4)),
          ),
          const SizedBox(height: 24),
          Text(
            'Aucune demande',
            style: GoogleFonts.outfit(
              color: AppColors.textPri,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 48),
            child: Text(
              'Vous n\'avez fait aucune demande de congé ou permission pour l\'instant.',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                color: AppColors.textSec,
                fontSize: 13,
                height: 1.5,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLeaveCard(Map<String, dynamic> leave) {
    final type = leave['type'] ?? 'Congé';
    final status = leave['statut'] ?? 'En attente';
    final motif = leave['motif'] ?? '';
    final debut = _formatDate(leave['date_debut'] ?? '');
    final fin = _formatDate(leave['date_fin'] ?? '');
    final statusColor = _getStatusColor(status);
    final statusIcon = _getStatusIcon(status);

    IconData typeIcon;
    Color typeColor;
    if (type.toLowerCase() == 'maladie') {
      typeIcon = Icons.medical_services_rounded;
      typeColor = AppColors.red;
    } else if (type.toLowerCase() == 'permission') {
      typeIcon = Icons.directions_run_rounded;
      typeColor = AppColors.orange;
    } else {
      typeIcon = Icons.flight_takeoff_rounded;
      typeColor = AppColors.accentSoft;
    }

    return Container(
      decoration: BoxDecoration(
        color: AppColors.surfaceHigh,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.12),
            blurRadius: 14,
            offset: const Offset(0, 5),
          )
        ],
      ),
      child: IntrinsicHeight(
        child: Row(
          children: [
            // Bandeau statut vertical à gauche
            Container(
              width: 5,
              decoration: BoxDecoration(
                color: statusColor,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(22),
                  bottomLeft: Radius.circular(22),
                ),
              ),
            ),
            // Contenu
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(9),
                          decoration: BoxDecoration(
                            color: typeColor.withOpacity(0.12),
                            shape: BoxShape.circle,
                          ),
                          child: Icon(typeIcon, color: typeColor, size: 18),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                type,
                                style: GoogleFonts.outfit(
                                  color: AppColors.textPri,
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '$debut → $fin',
                                style: GoogleFonts.inter(
                                  color: AppColors.textSec,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                        // Chip statut
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: statusColor.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                                color: statusColor.withOpacity(0.3)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(statusIcon, color: statusColor, size: 12),
                              const SizedBox(width: 4),
                              Text(
                                status,
                                style: GoogleFonts.inter(
                                  color: statusColor,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    if (motif.isNotEmpty) ...[
                      const SizedBox(height: 14),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.bg.withOpacity(0.5),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Text(
                          motif,
                          style: GoogleFonts.inter(
                            color: AppColors.textPri.withOpacity(0.8),
                            fontSize: 12,
                            height: 1.4,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
