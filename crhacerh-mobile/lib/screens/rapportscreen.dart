import 'dart:io';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

import '../services/weekly_report_service.dart';
import '../services/local_storage_service.dart';
import '../services/auth_service.dart';
import 'report_folder_screen.dart';

// ─────────────────────────────────────────────────────────────────────────────
// RapportsScreen — liste les dossiers-mois de rapports hebdomadaires
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

  // Noms des mois français pour l'icône colorée
  static const _monthColors = {
    'Janvier':   Color(0xFF5C8AE6),
    'Février':   Color(0xFF9B59B6),
    'Mars':      Color(0xFF27AE60),
    'Avril':     Color(0xFF16A085),
    'Mai':       Color(0xFF2ECC71),
    'Juin':      Color(0xFFF39C12),
    'Juillet':   Color(0xFFE67E22),
    'Août':      Color(0xFFE74C3C),
    'Septembre': Color(0xFF8E44AD),
    'Octobre':   Color(0xFFD35400),
    'Novembre':  Color(0xFF2980B9),
    'Décembre':  Color(0xFF1ABC9C),
  };

  @override
  void initState() {
    super.initState();
    _fadeCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 500));
    _fadeAnim = CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeIn);
    _init();
  }

  @override
  void dispose() {
    _fadeCtrl.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    if (mounted) {
      setState(() => _loading = true);
    } else {
      _loading = true;
    }
    // 1. Génère automatiquement les rapports à partir des pointages réels s'il n'y a aucun dossier
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
    // 2. Recharge la liste
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
      _showSnack('Connectez-vous d\'abord pour générer vos rapports.', isError: true);
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
        _showSnack('Aucun pointage trouvé pour générer des rapports.', isError: true);
      } else {
        await _reportSvc.generateAllReportsFromHistory(
          employee: emp,
          history: history,
        );
        await _refreshFolders();
        _showSnack('Rapports générés automatiquement avec succès.');
      }
    } catch (e) {
      _showSnack('Erreur lors de la génération : $e', isError: true);
    } finally {
      if (mounted) setState(() => _generatingReports = false);
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: isError ? Colors.red[700] : const Color(0xFF1A1A1A),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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

  // ── UI ───────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F6FA),
      body: CustomScrollView(
        slivers: [
          _buildAppBar(),
          if (_loading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_monthFolders.isEmpty)
            SliverFillRemaining(child: _buildEmpty())
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  childAspectRatio: 1.15,
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

  SliverAppBar _buildAppBar() {
    return SliverAppBar(
      pinned: true,
      expandedHeight: 140,
      backgroundColor: const Color(0xFF1A1A1A),
      foregroundColor: Colors.white,
      flexibleSpace: FlexibleSpaceBar(
        titlePadding: const EdgeInsets.fromLTRB(20, 0, 16, 16),
        title: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Mes Rapports',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              _syncLabel(),
              style: const TextStyle(fontSize: 10, color: Colors.white54),
            ),
          ],
        ),
        background: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF1A1A1A), Color(0xFF2D2D2D)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: Align(
            alignment: Alignment.topRight,
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Icon(
                Icons.folder_special_rounded,
                size: 64,
                color: Colors.white.withValues(alpha: 0.06),
              ),
            ),
          ),
        ),
      ),
      actions: [
        IconButton(
          tooltip: 'Actualiser',
          icon: const Icon(Icons.refresh_rounded, color: Colors.white70),
          onPressed: _init,
        ),
      ],
    );
  }

  Widget _buildMonthCard(Directory dir) {
    final monthName =
        dir.path.split(Platform.pathSeparator).last;
    final color =
        _monthColors[monthName] ?? const Color(0xFF607D8B);

    // Compte les rapports du dossier
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
          MaterialPageRoute(
            builder: (_) => ReportFolderScreen(folder: dir),
          ),
        );
        await _refreshFolders();
      },
      child: AnimatedBuilder(
        animation: _fadeAnim,
        builder: (_, child) => FadeTransition(
          opacity: _fadeAnim,
          child: child,
        ),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: color.withValues(alpha: 0.18),
                blurRadius: 16,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Icône dossier coloré
              Container(
                width: 64,
                height: 50,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    Icon(Icons.folder_rounded, size: 44, color: color),
                    Positioned(
                      bottom: 8,
                      child: Text(
                        '$count',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: color,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              Text(
                monthName,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF1A1A1A),
                ),
              ),
              const SizedBox(height: 4),
              Text(
                '$count rapport${count > 1 ? 's' : ''}',
                style: const TextStyle(
                  fontSize: 11,
                  color: Color(0xFF888888),
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
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.folder_off_rounded,
                size: 72, color: Colors.grey[300]),
            const SizedBox(height: 20),
            const Text(
              'Aucun rapport disponible',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: Color(0xFF333333),
              ),
            ),
            const SizedBox(height: 10),
            const Text(
              'Les rapports hebdomadaires sont générés automatiquement à partir de vos pointages.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Color(0xFF888888)),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _generatingReports ? null : _generateRealReports,
              icon: const Icon(Icons.add_chart_rounded),
              label: const Text('Générer mes rapports'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1A1A1A),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
                padding: const EdgeInsets.symmetric(
                    horizontal: 20, vertical: 14),
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
      children: [
        // Bouton : générer rapports
        FloatingActionButton.small(
          heroTag: 'fab_example',
          backgroundColor: const Color(0xFF1A1A1A),
          foregroundColor: Colors.white,
          tooltip: 'Générer mes rapports',
          onPressed: _generatingReports ? null : _generateRealReports,
          child: _generatingReports
              ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                      strokeWidth: 2, color: Colors.white))
              : const Icon(Icons.add_rounded, size: 20),
        ),
        const SizedBox(height: 10),
        // Bouton : actualiser
        FloatingActionButton(
          heroTag: 'fab_refresh',
          backgroundColor: Colors.white,
          foregroundColor: const Color(0xFF1A1A1A),
          tooltip: 'Actualiser',
          elevation: 4,
          onPressed: _init,
          child: const Icon(Icons.refresh_rounded),
        ),
      ],
    );
  }
}

