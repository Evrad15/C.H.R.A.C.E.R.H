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
            content: Text(result['message'] ?? 'Erreur'),
            backgroundColor: AppColors.red,
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
      appBar: AppBar(
        backgroundColor: AppColors.bg,
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textPri, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Congs & Permissions',
          style: GoogleFonts.outfit(
            color: AppColors.textPri,
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator(color: AppColors.accent))
          : _leaves.isEmpty
              ? _buildEmptyState()
              : RefreshIndicator(
                  color: AppColors.accent,
                  backgroundColor: AppColors.card,
                  onRefresh: _loadLeaves,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(20),
                    itemCount: _leaves.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 16),
                    itemBuilder: (context, index) {
                      final leave = _leaves[index];
                      return _buildLeaveCard(leave);
                    },
                  ),
                ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => LeaveFormScreen(employeeId: widget.employeeId),
            ),
          );
          if (result == true) {
            _loadLeaves();
          }
        },
        backgroundColor: AppColors.accent,
        elevation: 8,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: const Icon(Icons.add_rounded, color: Colors.white, size: 28),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.event_busy_rounded, size: 80, color: AppColors.textSec.withValues(alpha: 0.3)),
          const SizedBox(height: 20),
          Text(
            'Aucune demande',
            style: GoogleFonts.outfit(
              color: AppColors.textPri,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Vous n\'avez fait aucune demande pour l\'instant.',
            textAlign: TextAlign.center,
            style: GoogleFonts.inter(
              color: AppColors.textSec,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLeaveCard(Map<String, dynamic> leave) {
    final type = leave['type'] ?? 'Cong';
    final status = leave['statut'] ?? 'En attente';
    final motif = leave['motif'] ?? '';
    final debut = _formatDate(leave['date_debut'] ?? '');
    final fin = _formatDate(leave['date_fin'] ?? '');
    final statusColor = _getStatusColor(status);

    IconData typeIcon;
    if (type.toLowerCase() == 'maladie') typeIcon = Icons.medical_services_rounded;
    else if (type.toLowerCase() == 'permission') typeIcon = Icons.directions_run_rounded;
    else typeIcon = Icons.flight_takeoff_rounded;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppColors.textSec.withValues(alpha: 0.1)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.accent.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: Icon(typeIcon, color: AppColors.accent, size: 20),
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
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '$debut - $fin',
                      style: GoogleFonts.inter(
                        color: AppColors.textSec,
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  status,
                  style: GoogleFonts.inter(
                    color: statusColor,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
          if (motif.isNotEmpty) ...[
            const SizedBox(height: 16),
            Text(
              motif,
              style: GoogleFonts.inter(
                color: AppColors.textPri.withValues(alpha: 0.8),
                fontSize: 13,
                height: 1.4,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      ),
    );
  }
}
