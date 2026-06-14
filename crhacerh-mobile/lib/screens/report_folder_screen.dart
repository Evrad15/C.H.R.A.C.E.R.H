import 'dart:io';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:open_filex/open_filex.dart';
import 'package:share_plus/share_plus.dart';

import '../services/weekly_report_service.dart';
import '../theme_manager.dart';

// ─────────────────────────────────────────────────────────────────────────────
// ReportFolderScreen — liste les rapports d'un mois donné
// ─────────────────────────────────────────────────────────────────────────────
class ReportFolderScreen extends StatefulWidget {
  final Directory folder;

  const ReportFolderScreen({super.key, required this.folder});

  @override
  State<ReportFolderScreen> createState() => _ReportFolderScreenState();
}

class _ReportFolderScreenState extends State<ReportFolderScreen> {
  final WeeklyReportService _reportSvc = WeeklyReportService();
  List<WeeklyReportFile> _reports = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadReports();
  }

  Future<void> _loadReports() async {
    if (mounted) {
      setState(() => _loading = true);
    } else {
      _loading = true;
    }
    final reports = await _reportSvc.listReportsInFolder(widget.folder);
    if (mounted) {
      setState(() {
        _reports = reports;
        _loading = false;
      });
    }
  }

  String get _monthTitle =>
      widget.folder.path.split(Platform.pathSeparator).last;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: Column(
        children: [
          // Header premium
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
                padding: const EdgeInsets.fromLTRB(8, 8, 8, 18),
                child: Row(
                  children: [
                    IconButton(
                      icon: Icon(Icons.arrow_back_ios_new_rounded,
                          color: AppColors.textPri, size: 20),
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                    Icon(Icons.folder_open_rounded,
                        size: 20, color: AppColors.accentSoft),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _monthTitle,
                        style: GoogleFonts.outfit(
                          color: AppColors.textPri,
                          fontSize: 20,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.refresh_rounded,
                          color: AppColors.textSec),
                      onPressed: _loadReports,
                    ),
                  ],
                ),
              ),
            ),
          ),
          // Contenu
          Expanded(
            child: _loading
                ? Center(
                    child: CircularProgressIndicator(
                        color: AppColors.accentSoft, strokeWidth: 2.5))
                : _reports.isEmpty
                    ? _buildEmpty()
                    : RefreshIndicator(
                        color: AppColors.accentSoft,
                        backgroundColor: AppColors.surfaceHigh,
                        onRefresh: _loadReports,
                        child: ListView.separated(
                          padding:
                              const EdgeInsets.fromLTRB(16, 20, 16, 120),
                          itemCount: _reports.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 14),
                          itemBuilder: (ctx, i) =>
                              _ReportCard(report: _reports[i]),
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: AppColors.surfaceHigh,
              shape: BoxShape.circle,
              border: Border.all(color: AppColors.border),
            ),
            child: Icon(Icons.description_outlined,
                size: 38, color: AppColors.textSec.withOpacity(0.4)),
          ),
          const SizedBox(height: 20),
          Text(
            'Aucun rapport dans ce mois',
            style: GoogleFonts.outfit(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: AppColors.textPri),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// _ReportCard — carte d'un rapport hebdomadaire avec détail expandable
// ─────────────────────────────────────────────────────────────────────────────
class _ReportCard extends StatefulWidget {
  final WeeklyReportFile report;
  const _ReportCard({required this.report});

  @override
  State<_ReportCard> createState() => _ReportCardState();
}

class _ReportCardState extends State<_ReportCard>
    with SingleTickerProviderStateMixin {
  bool _expanded = false;
  late AnimationController _ctrl;
  late Animation<double> _expandAnim;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 280));
    _expandAnim =
        CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut);
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  void _toggle() {
    setState(() => _expanded = !_expanded);
    _expanded ? _ctrl.forward() : _ctrl.reverse();
  }

  Color get _tauxColor {
    final t = widget.report.tauxAssiduite;
    if (t >= 80) return const Color(0xFF27AE60);
    if (t >= 50) return const Color(0xFFF39C12);
    return const Color(0xFFE74C3C);
  }

  @override
  Widget build(BuildContext context) {
    final r = widget.report;
    return GestureDetector(
      onTap: _toggle,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surfaceHigh,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppColors.border),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.15),
              blurRadius: 14,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: Column(
          children: [
            // ── En-tête ──────────────────────────────────────────────────
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  // Icône rapport (Style Excel / XLSX)
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: AppColors.green.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                          color: AppColors.green.withOpacity(0.3), width: 1.5),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.table_view_rounded,
                            color: AppColors.green, size: 20),
                        Text(
                          'XLSX',
                          style: GoogleFonts.inter(
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                            color: AppColors.green,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 14),
                  // Titre + période
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          r.title,
                          style: GoogleFonts.outfit(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textPri,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          r.periode,
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            color: AppColors.textSec,
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Taux + flèche
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: _tauxColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                              color: _tauxColor.withOpacity(0.25)),
                        ),
                        child: Text(
                          '${r.tauxAssiduite}%',
                          style: GoogleFonts.outfit(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: _tauxColor,
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      AnimatedRotation(
                        turns: _expanded ? 0.5 : 0,
                        duration: const Duration(milliseconds: 280),
                        child: Icon(Icons.keyboard_arrow_down_rounded,
                            color: AppColors.textSec.withOpacity(0.5)),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // ── Chips résumé (toujours visibles) ─────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
              child: Row(
                children: [
                  _StatusChip(
                    icon: Icons.check_circle_rounded,
                    label: '${r.presences} Présent${r.presences > 1 ? 's' : ''}',
                    color: AppColors.green,
                  ),
                  const SizedBox(width: 8),
                  _StatusChip(
                    icon: Icons.schedule_rounded,
                    label: '${r.retards} Retard${r.retards > 1 ? 's' : ''}',
                    color: AppColors.orange,
                  ),
                  const SizedBox(width: 8),
                  _StatusChip(
                    icon: Icons.cancel_rounded,
                    label: '${r.absences} Absent${r.absences > 1 ? 's' : ''}',
                    color: AppColors.red,
                  ),
                ],
              ),
            ),

            // ── Détail expandable ─────────────────────────────────────────
            SizeTransition(
              sizeFactor: _expandAnim,
              child: _buildDetail(r),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetail(WeeklyReportFile r) {
    final detail = <dynamic>[];
    try {
      detail.addAll(r.data['detail'] as List<dynamic>);
    } catch (_) {}

    return Container(
      decoration: BoxDecoration(
        color: AppColors.bg.withOpacity(0.5),
        borderRadius: const BorderRadius.only(
          bottomLeft: Radius.circular(20),
          bottomRight: Radius.circular(20),
        ),
        border: Border(
          top: BorderSide(color: AppColors.border, width: 1),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Détail journalier',
                style: GoogleFonts.inter(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textSec,
                ),
              ),
              _buildExcelActions(r),
            ],
          ),
          const SizedBox(height: 10),
          if (detail.isEmpty)
            Text('Aucun détail disponible.',
                style: GoogleFonts.inter(
                    fontSize: 12, color: AppColors.textSec))
          else
            ...detail.map((entry) {
              final e = entry as Map<String, dynamic>;
              final status =
                  (e['status'] ?? '').toString().toUpperCase();
              Color statusColor;
              IconData statusIcon;
              switch (status) {
                case 'PRESENT':
                  statusColor = AppColors.green;
                  statusIcon = Icons.check_circle_outline_rounded;
                  break;
                case 'RETARD':
                  statusColor = AppColors.orange;
                  statusIcon = Icons.schedule_rounded;
                  break;
                default:
                  statusColor = AppColors.red;
                  statusIcon = Icons.cancel_outlined;
              }
              return Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  children: [
                    Icon(statusIcon, size: 16, color: statusColor),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        '${e['date']}',
                        style: GoogleFonts.inter(
                            fontSize: 12, color: AppColors.textPri),
                      ),
                    ),
                    Text(
                      '${e['time'] ?? '—'}',
                      style: GoogleFonts.robotoMono(
                          fontSize: 11, color: AppColors.textSec),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      e['status'] ?? '—',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: statusColor,
                      ),
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildExcelActions(WeeklyReportFile r) {
    final xlsxPath = r.file.path;
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Ouvrir Excel
        InkWell(
          onTap: () async {
            final messenger = ScaffoldMessenger.of(context);
            try {
              await OpenFilex.open(xlsxPath);
            } catch (e) {
              messenger.showSnackBar(SnackBar(
                content: Text('Impossible d\'ouvrir le fichier : $e'),
                backgroundColor: AppColors.red,
              ));
            }
          },
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.table_view_rounded, size: 16, color: AppColors.green),
                const SizedBox(width: 4),
                Text(
                  'Ouvrir',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.green,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(width: 8),
        InkWell(
          onTap: () async {
            final messenger = ScaffoldMessenger.of(context);
            try {
              await SharePlus.instance.share(
                ShareParams(
                  text: 'Rapport hebdomadaire - ${r.title}',
                  files: [XFile(xlsxPath)],
                ),
              );
            } catch (e) {
              messenger.showSnackBar(SnackBar(
                content: Text('Impossible de partager le fichier : $e'),
                backgroundColor: AppColors.red,
              ));
            }
          },
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.share_rounded, size: 16, color: AppColors.accentSoft),
                const SizedBox(width: 4),
                Text(
                  'Partager',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.accentSoft,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// _StatusChip
// ─────────────────────────────────────────────────────────────────────────────
class _StatusChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;

  const _StatusChip(
      {required this.icon, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.25)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: color,
            ),
          ),
        ],
      ),
    );
  }
}

