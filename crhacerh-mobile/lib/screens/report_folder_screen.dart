import 'dart:io';

import 'package:flutter/material.dart';
import 'package:open_filex/open_filex.dart';
import 'package:share_plus/share_plus.dart';

import '../services/weekly_report_service.dart';

// ─────────────────────────────────────────────────────────────────────────────
// ReportFolderScreen — liste les rapports JSON d'un mois donné
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
      backgroundColor: const Color(0xFFF4F6FA),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A1A1A),
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Row(
          children: [
            const Icon(Icons.folder_open_rounded,
                size: 22, color: Colors.white70),
            const SizedBox(width: 10),
            Text(
              _monthTitle,
              style: const TextStyle(
                  fontSize: 18, fontWeight: FontWeight.w600),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white70),
            onPressed: _loadReports,
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _reports.isEmpty
              ? _buildEmpty()
              : RefreshIndicator(
                  onRefresh: _loadReports,
                  child: ListView.separated(
                    padding:
                        const EdgeInsets.fromLTRB(16, 20, 16, 120),
                    itemCount: _reports.length,
                    separatorBuilder: (_, __) =>
                        const SizedBox(height: 12),
                    itemBuilder: (ctx, i) =>
                        _ReportCard(report: _reports[i]),
                  ),
                ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.description_outlined, size: 64, color: Colors.grey[300]),
          const SizedBox(height: 16),
          const Text(
            'Aucun rapport dans ce mois',
            style: TextStyle(fontSize: 16, color: Color(0xFF888888)),
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
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
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
                  // Icône rapport
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: const Color(0xFF1A1A1A).withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.bar_chart_rounded,
                        color: Color(0xFF1A1A1A), size: 26),
                  ),
                  const SizedBox(width: 14),
                  // Titre + période
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          r.title,
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1A1A1A),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          r.periode,
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF888888),
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
                          color: _tauxColor.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          '${r.tauxAssiduite}%',
                          style: TextStyle(
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
                        child: const Icon(Icons.keyboard_arrow_down_rounded,
                            color: Color(0xFFAAAAAA)),
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
                    color: const Color(0xFF27AE60),
                  ),
                  const SizedBox(width: 8),
                  _StatusChip(
                    icon: Icons.schedule_rounded,
                    label: '${r.retards} Retard${r.retards > 1 ? 's' : ''}',
                    color: const Color(0xFFF39C12),
                  ),
                  const SizedBox(width: 8),
                  _StatusChip(
                    icon: Icons.cancel_rounded,
                    label: '${r.absences} Absent${r.absences > 1 ? 's' : ''}',
                    color: const Color(0xFFE74C3C),
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
        color: const Color(0xFFF8F9FA),
        borderRadius: const BorderRadius.only(
          bottomLeft: Radius.circular(18),
          bottomRight: Radius.circular(18),
        ),
        border: Border(
          top: BorderSide(color: Colors.grey[200]!, width: 1),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Détail journalier',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF555555),
                ),
              ),
              _buildExcelActions(r),
            ],
          ),
          const SizedBox(height: 10),
          if (detail.isEmpty)
            const Text('Aucun détail disponible.',
                style: TextStyle(fontSize: 12, color: Color(0xFFAAAAAA)))
          else
            ...detail.map((entry) {
              final e = entry as Map<String, dynamic>;
              final status =
                  (e['status'] ?? '').toString().toUpperCase();
              Color statusColor;
              IconData statusIcon;
              switch (status) {
                case 'PRESENT':
                  statusColor = const Color(0xFF27AE60);
                  statusIcon = Icons.check_circle_outline_rounded;
                  break;
                case 'RETARD':
                  statusColor = const Color(0xFFF39C12);
                  statusIcon = Icons.schedule_rounded;
                  break;
                default:
                  statusColor = const Color(0xFFE74C3C);
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
                        style: const TextStyle(
                            fontSize: 12, color: Color(0xFF444444)),
                      ),
                    ),
                    Text(
                      '${e['time'] ?? '—'}',
                      style: const TextStyle(
                          fontSize: 12, color: Color(0xFF888888)),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      e['status'] ?? '—',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
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
            try {
              await OpenFilex.open(xlsxPath);
            } catch (e) {
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                  content: Text('Impossible d\'ouvrir le fichier : $e'),
                  backgroundColor: Colors.red[700],
                ));
              }
            }
          },
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.table_view_rounded, size: 16, color: Color(0xFF27AE60)),
                const SizedBox(width: 4),
                const Text(
                  'Ouvrir',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF27AE60),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(width: 8),
        // Partager Excel
        InkWell(
          onTap: () async {
            try {
              await SharePlus.instance.share(
                ShareParams(
                  text: 'Rapport hebdomadaire - ${r.title}',
                  files: [XFile(xlsxPath)],
                ),
              );
            } catch (e) {
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                  content: Text('Impossible de partager le fichier : $e'),
                  backgroundColor: Colors.red[700],
                ));
              }
            }
          },
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.share_rounded, size: 16, color: Color(0xFF2F80ED)),
                const SizedBox(width: 4),
                const Text(
                  'Partager',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF2F80ED),
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
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
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

