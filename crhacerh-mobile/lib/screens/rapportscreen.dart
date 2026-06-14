import 'dart:io';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:google_fonts/google_fonts.dart';

import '../services/weekly_report_service.dart';
import '../services/local_storage_service.dart';
import '../services/auth_service.dart';
import '../theme_manager.dart';
import 'report_folder_screen.dart';

// ─────────────────────────────────────────────────────────────────────────────
// RapportsScreen — liste des dossiers mois redessinée
// ─────────────────────────────────────────────────────────────────────────────
class RapportsScreen extends StatefulWidget {
  final Map<String, dynamic>? employee;
  const RapportsScreen({super.key, this.employee});

  @override
  State<RapportsScreen> createState() => _RapportsScreenState();
}

class _RapportsScreenState extends State<RapportsScreen>
    with SingleTickerProviderStateMixin {
  final WeeklyReportService _reportSvc = WeeklyReportService();
  final LocalStorageService _localSvc = LocalStorageService();

  List<Directory> _monthFolders = [];
  bool _loading = true;
  bool _generatingReports = false;
  DateTime? _lastSync;

  late AnimationController _fadeCtrl;
  late Animation<double> _fadeAnim;

  static const _monthMeta = {
    'Janvier':   {'color': Color(0xFF6366F1), 'abbr': 'JAN'},
    'Février':   {'color': Color(0xFF8B5CF6), 'abbr': 'FÉV'},
    'Mars':      {'color': Color(0xFF10B981), 'abbr': 'MAR'},
    'Avril':     {'color': Color(0xFF06B6D4), 'abbr': 'AVR'},
    'Mai':       {'color': Color(0xFF22C55E), 'abbr': 'MAI'},
    'Juin':      {'color': Color(0xFFF59E0B), 'abbr': 'JUN'},
    'Juillet':   {'color': Color(0xFFF97316), 'abbr': 'JUL'},
    'Août':      {'color': Color(0xFFEF4444), 'abbr': 'AOÛ'},
    'Septembre': {'color': Color(0xFFEC4899), 'abbr': 'SEP'},
    'Octobre':   {'color': Color(0xFFD97706), 'abbr': 'OCT'},
    'Novembre':  {'color': Color(0xFF3B82F6), 'abbr': 'NOV'},
    'Décembre':  {'color': Color(0xFF14B8A6), 'abbr': 'DÉC'},
  };

  @override
  void initState() {
    super.initState();
    _fadeCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 500));
    _fadeAnim = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut);
    _init();
  }

  @override
  void dispose() {
    _fadeCtrl.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    if (mounted) setState(() => _loading = true);

    final emp = widget.employee ?? await _loadEmployeeFromCache();
    if (emp != null) {
      final folders = await _reportSvc.listMonthFolders();
      if (folders.isEmpty) {
        try {
          final result = await AuthService().fetchDashboardData(emp['id']);
          List<dynamic> history = [];
          if (result['success'] == true) {
            history = result['data']['all_history'] ?? [];
            await _localSvc.saveDashboardData(emp['id'], result['data']);
          } else {
            final local = await _localSvc.loadDashboardData(emp['id']);
            if (local != null) {
              history = local['all_history'] ?? [];
            }
          }
          if (history.isNotEmpty) {
            await _reportSvc.generateAllReportsFromHistory(
              employee: emp,
              history: history,
            );
          }
        } catch (_) {}
      }
    }
    await _refreshFolders();
    _lastSync = await _localSvc.getLastSyncTime();
    _fadeCtrl.forward();
  }

  Future<Map<String, dynamic>?> _loadEmployeeFromCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString('cached_employee');
      if (raw == null) return null;
      return jsonDecode(raw) as Map<String, dynamic>;
    } catch (_) {
      return null;
    }
  }

  Future<void> _refreshFolders() async {
    final folders = await _reportSvc.listMonthFolders();
    if (mounted) {
      setState(() {
        _monthFolders = folders;
        _loading = false;
      });
    }
  }

  Future<void> _generateRealReports() async {
    final emp = widget.employee ?? await _loadEmployeeFromCache();
    if (emp == null) {
      _showSnack('Connectez-vous d\'abord pour générer vos rapports.',
          isError: true);
      return;
    }
    setState(() => _generatingReports = true);
    try {
      final result = await AuthService().fetchDashboardData(emp['id']);
      List<dynamic> history = [];
      if (result['success'] == true) {
        history = result['data']['all_history'] ?? [];
        await _localSvc.saveDashboardData(emp['id'], result['data']);
      } else {
        final local = await _localSvc.loadDashboardData(emp['id']);
        if (local != null) {
          history = local['all_history'] ?? [];
        }
      }
      if (history.isEmpty) {
        _showSnack('Aucun pointage trouvé pour générer des rapports.',
            isError: true);
      } else {
        await _reportSvc.generateAllReportsFromHistory(
          employee: emp,
          history: history,
        );
        await _refreshFolders();
        _showSnack('Rapports générés avec succès ✓');
      }
    } catch (e) {
      _showSnack('Erreur : $e', isError: true);
    } finally {
      if (mounted) setState(() => _generatingReports = false);
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg,
          style: GoogleFonts.inter(fontWeight: FontWeight.w500)),
      backgroundColor:
          isError ? AppColors.red : AppColors.accent,
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
    ));
  }

  String _syncLabel() {
    if (_lastSync == null) return 'Jamais synchronisé';
    final diff = DateTime.now().difference(_lastSync!);
    if (diff.inMinutes < 1) return 'Synchronisé à l\'instant';
    if (diff.inMinutes < 60) return 'Sync il y a ${diff.inMinutes} min';
    if (diff.inHours < 24) return 'Sync il y a ${diff.inHours}h';
    return 'Sync il y a ${diff.inDays}j';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: CustomScrollView(
        slivers: [
          _buildSliverAppBar(),
          if (_loading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_monthFolders.isEmpty)
            SliverFillRemaining(child: _buildEmpty())
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 120),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  childAspectRatio: 1.05,
                  crossAxisSpacing: 14,
                  mainAxisSpacing: 14,
                ),
                delegate: SliverChildBuilderDelegate(
                  (ctx, i) => _buildMonthCard(_monthFolders[i]),
                  childCount: _monthFolders.length,
                ),
              ),
            ),
        ],
      ),
      floatingActionButton: _buildFab(),
    );
  }

  SliverAppBar _buildSliverAppBar() {
    return SliverAppBar(
      pinned: true,
      expandedHeight: 150,
      backgroundColor: AppColors.surfaceHigh,
      foregroundColor: AppColors.textPri,
      surfaceTintColor: Colors.transparent,
      elevation: 0,
      flexibleSpace: FlexibleSpaceBar(
        titlePadding: const EdgeInsets.fromLTRB(20, 0, 16, 16),
        title: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Mes Rapports',
              style: GoogleFonts.outfit(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: AppColors.textPri,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              _syncLabel(),
              style: GoogleFonts.inter(
                  fontSize: 10, color: AppColors.textSec),
            ),
          ],
        ),
        background: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [
                const Color(0xFF0E1128),
                const Color(0xFF1E1B4B),
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: Stack(
            children: [
              // Icône décorative
              Positioned(
                top: 20,
                right: 20,
                child: Icon(
                  Icons.folder_special_rounded,
                  size: 80,
                  color: AppColors.accent.withOpacity(0.08),
                ),
              ),
              // Badge compteur
              Positioned(
                top: 40,
                left: 20,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.accent.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: AppColors.accent.withOpacity(0.25)),
                  ),
                  child: Text(
                    '${_monthFolders.length} mois',
                    style: GoogleFonts.inter(
                      color: AppColors.accentSoft,
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      actions: [
        IconButton(
          tooltip: 'Actualiser',
          icon: Icon(Icons.refresh_rounded,
              color: AppColors.textPri.withOpacity(0.7)),
          onPressed: _init,
        ),
      ],
    );
  }

  Widget _buildMonthCard(Directory dir) {
    final monthName = dir.path.split(Platform.pathSeparator).last;
    final meta = _monthMeta[monthName];
    final color = (meta?['color'] as Color?) ?? const Color(0xFF6366F1);
    final abbr = (meta?['abbr'] as String?) ?? monthName.substring(0, 3).toUpperCase();

    int count = 0;
    try {
      count = dir
          .listSync()
          .whereType<File>()
          .where((f) => f.path.endsWith('.xlsx'))
          .length;
    } catch (_) {}

    return GestureDetector(
      onTap: () async {
        await Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => ReportFolderScreen(folder: dir)),
        );
        await _refreshFolders();
      },
      child: FadeTransition(
        opacity: _fadeAnim,
        child: Container(
          decoration: BoxDecoration(
            color: AppColors.surfaceHigh,
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: color.withOpacity(0.2)),
            boxShadow: [
              BoxShadow(
                color: color.withOpacity(0.12),
                blurRadius: 16,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Badge mois avec couleur
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      color.withOpacity(0.2),
                      color.withOpacity(0.08),
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  shape: BoxShape.circle,
                  border: Border.all(color: color.withOpacity(0.3), width: 1.5),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.folder_rounded, size: 26, color: color),
                    Text(
                      '$count',
                      style: GoogleFonts.outfit(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: color,
                        height: 1.1,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              Text(
                monthName,
                style: GoogleFonts.outfit(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPri,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                '$count rapport${count > 1 ? 's' : ''}',
                style: GoogleFonts.inter(
                  fontSize: 11,
                  color: AppColors.textSec,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: AppColors.surfaceHigh,
                shape: BoxShape.circle,
                border: Border.all(color: AppColors.border),
              ),
              child: Icon(Icons.folder_off_rounded,
                  size: 48, color: AppColors.textSec.withOpacity(0.4)),
            ),
            const SizedBox(height: 24),
            Text(
              'Aucun rapport disponible',
              style: GoogleFonts.outfit(
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: AppColors.textPri,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Les rapports hebdomadaires sont générés automatiquement à partir de vos pointages.',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                  fontSize: 13,
                  color: AppColors.textSec,
                  height: 1.5),
            ),
            const SizedBox(height: 28),
            GestureDetector(
              onTap: _generatingReports ? null : _generateRealReports,
              child: Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 24, vertical: 14),
                decoration: BoxDecoration(
                  gradient: AppColors.accentGradient,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.accentGlow,
                      blurRadius: 14,
                      offset: const Offset(0, 4),
                    )
                  ],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.add_chart_rounded,
                        color: Colors.white, size: 20),
                    const SizedBox(width: 8),
                    Text(
                      'Générer mes rapports',
                      style: GoogleFonts.outfit(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFab() {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        FloatingActionButton.small(
          heroTag: 'fab_generate',
          backgroundColor: AppColors.accent,
          foregroundColor: Colors.white,
          tooltip: 'Générer mes rapports',
          onPressed: _generatingReports ? null : _generateRealReports,
          child: _generatingReports
              ? const SizedBox(
                  width: 16,
                  height: 16,
                  child: CircularProgressIndicator(
                      strokeWidth: 2, color: Colors.white))
              : const Icon(Icons.add_rounded, size: 20),
        ),
        const SizedBox(height: 10),
        FloatingActionButton(
          heroTag: 'fab_refresh',
          backgroundColor: AppColors.surfaceHigh,
          foregroundColor: AppColors.textPri,
          tooltip: 'Actualiser',
          elevation: 4,
          onPressed: _init,
          child: Icon(Icons.refresh_rounded, color: AppColors.accentSoft),
        ),
      ],
    );
  }
}
