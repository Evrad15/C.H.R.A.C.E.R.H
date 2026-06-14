import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import '../services/local_storage_service.dart';
import '../theme_manager.dart';
import 'leaves_screen.dart';

class ProfilScreen extends StatefulWidget {
  final Map<String, dynamic> employee;
  final VoidCallback onLogout;
  final VoidCallback? onReload;

  const ProfilScreen({
    super.key,
    required this.employee,
    required this.onLogout,
    this.onReload,
  });

  @override
  State<ProfilScreen> createState() => _ProfilScreenState();
}

class _ProfilScreenState extends State<ProfilScreen>
    with SingleTickerProviderStateMixin {
  final LocalStorageService _localSvc = LocalStorageService();
  bool _isLoading = true;
  double _presenceRate = 0.0;
  int _lates = 0;
  int _absences = 0;
  String _syncTime = 'Jamais';
  bool? _isFaceRegistered;

  late AnimationController _fadeCtrl;
  late Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();
    _fadeCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 500));
    _fadeAnim =
        CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeOut);
    _loadStatsAndSync();
  }

  @override
  void dispose() {
    _fadeCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadStatsAndSync() async {
    if (mounted) setState(() => _isLoading = true);

    final lastSync = await _localSvc.getLastSyncTime();
    if (lastSync != null) {
      final diff = DateTime.now().difference(lastSync);
      if (diff.inMinutes < 1) {
        _syncTime = 'À l\'instant';
      } else if (diff.inMinutes < 60) {
        _syncTime = 'Il y a ${diff.inMinutes} min';
      } else if (diff.inHours < 24) {
        _syncTime = 'Il y a ${diff.inHours}h';
      } else {
        _syncTime = 'Il y a ${diff.inDays}j';
      }
    }

    await _localSvc.syncNow(widget.employee['id']);

    final local = await _localSvc.loadDashboardData(widget.employee['id']);
    if (local != null) {
      final stats = local['stats'] as Map<String, dynamic>?;
      if (stats != null) {
        _presenceRate = (stats['presence'] ?? 0.0).toDouble();
        _lates = (stats['retard'] ?? 0).toInt();
        _absences = (stats['absence'] ?? 0).toInt();
      }
    }

    await _checkFaceStatus();

    if (mounted) {
      setState(() => _isLoading = false);
      _fadeCtrl.forward();
    }
  }

  Future<void> _checkFaceStatus() async {
    final String baseUrl = kIsWeb
        ? 'http://localhost:8000/api'
        : 'https://crhacerh-api.loca.lt/api';
    final String url = '$baseUrl/employee/${widget.employee['id']}/face-status';
    try {
      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Accept': 'application/json',
          'bypass-tunnel-reminder': 'true',
        },
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (mounted) {
          setState(() {
            _isFaceRegistered = data['registered'] == true;
            if (_isFaceRegistered !=
                (widget.employee['consent_facial_at'] != null)) {
              _updateLocalEmployeeStatus(
                  _isFaceRegistered!, data['consent_facial_at']);
            }
          });
        }
      }
    } catch (e) {
      debugPrint("Erreur check face status: $e");
    }
  }

  Future<void> _updateLocalEmployeeStatus(
      bool registered, String? consentTime) async {
    final prefs = await SharedPreferences.getInstance();
    var localEmp = Map<String, dynamic>.from(widget.employee);
    localEmp['consent_facial_at'] =
        registered ? (consentTime ?? DateTime.now().toIso8601String()) : null;
    await prefs.setString('cached_employee', jsonEncode(localEmp));
    await LocalStorageService().saveEmployee(localEmp);
    if (widget.onReload != null) widget.onReload!();
  }

  String _getInitials() {
    final first = widget.employee['first_name']?.toString() ?? '';
    final last = widget.employee['last_name']?.toString() ?? '';
    final f = first.isNotEmpty ? first[0].toUpperCase() : '';
    final l = last.isNotEmpty ? last[0].toUpperCase() : '';
    return '$f$l';
  }

  Future<void> _clearCache() async {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceHigh,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text(
          'Vider le cache local ?',
          style: GoogleFonts.outfit(
              color: AppColors.textPri, fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Cela supprimera les rapports sauvegardés localement. Vous devrez vous synchroniser à nouveau.',
          style: GoogleFonts.inter(
              color: AppColors.textSec, fontSize: 13, height: 1.5),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: Text('Annuler',
                style: GoogleFonts.inter(color: AppColors.textSec)),
          ),
          ElevatedButton(
            onPressed: () async {
              final sm = ScaffoldMessenger.of(context);
              final prefs = await SharedPreferences.getInstance();
              await prefs.remove('dashboard_${widget.employee['id']}');
              if (ctx.mounted) Navigator.of(ctx).pop();
              _loadStatsAndSync();
              sm.showSnackBar(SnackBar(
                content: Text('Cache local vidé',
                    style: GoogleFonts.inter(fontWeight: FontWeight.w500)),
                backgroundColor: AppColors.accent,
                behavior: SnackBarBehavior.floating,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
              ));
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.red,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
            child: Text('Vider',
                style: GoogleFonts.inter(
                    color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final initials = _getInitials();
    final name =
        '${widget.employee['first_name'] ?? ''} ${widget.employee['last_name'] ?? ''}';
    final matricule = widget.employee['matricule'] ?? '—';
    final dept = widget.employee['departement'] ?? 'Sans Département';

    return Scaffold(
      backgroundColor: AppColors.bg,
      body: _isLoading
          ? Center(
              child: CircularProgressIndicator(
                  color: AppColors.accentSoft, strokeWidth: 2.5))
          : FadeTransition(
              opacity: _fadeAnim,
              child: RefreshIndicator(
                color: AppColors.accentSoft,
                backgroundColor: AppColors.surfaceHigh,
                onRefresh: _loadStatsAndSync,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      // ── Hero banner ──────────────────────────────────────
                      _buildHeroBanner(initials, name, matricule, dept),

                      const SizedBox(height: 24),

                      // ── Stat cards ───────────────────────────────────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        child: Row(
                          children: [
                            Expanded(
                              child: _buildStatCard(
                                label: 'Assiduité',
                                value:
                                    '${_presenceRate.toInt()}%',
                                color: _presenceRate >= 80
                                    ? AppColors.green
                                    : AppColors.orange,
                                icon: Icons.offline_pin_rounded,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _buildStatCard(
                                label: 'Retards',
                                value: '$_lates',
                                color: AppColors.orange,
                                icon: Icons.schedule_rounded,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _buildStatCard(
                                label: 'Absences',
                                value: '$_absences',
                                color: AppColors.red,
                                icon: Icons.cancel_rounded,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 28),

                      // ── Section label ────────────────────────────────────
                      Padding(
                        padding: const EdgeInsets.only(left: 24, bottom: 12),
                        child: Align(
                          alignment: Alignment.centerLeft,
                          child: Text(
                            'PARAMÈTRES & CONFIGURATION',
                            style: GoogleFonts.inter(
                              color: AppColors.textSec,
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 1.0,
                            ),
                          ),
                        ),
                      ),

                      // ── Settings Card ─────────────────────────────────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        child: Container(
                          decoration: BoxDecoration(
                            color: AppColors.surfaceHigh,
                            borderRadius: BorderRadius.circular(24),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Column(
                            children: [
                              _buildSettingTile(
                                icon: Icons.calendar_month_rounded,
                                iconColor: AppColors.accentSoft,
                                title: 'Mes Congés & Permissions',
                                onTap: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => LeavesScreen(
                                          employeeId: widget.employee['id']),
                                    ),
                                  );
                                },
                                hasArrow: true,
                              ),
                              _buildDivider(),
                              _buildSettingTile(
                                icon: Icons.face_rounded,
                                iconColor: (_isFaceRegistered ??
                                        (widget.employee['consent_facial_at'] !=
                                            null))
                                    ? AppColors.green
                                    : AppColors.orange,
                                title: 'Enregistrement Facial',
                                trailing: (_isFaceRegistered ??
                                        (widget.employee['consent_facial_at'] !=
                                            null))
                                    ? 'Enregistré ✓'
                                    : 'Non configuré',
                                trailingColor: (_isFaceRegistered ??
                                        (widget.employee['consent_facial_at'] !=
                                            null))
                                    ? AppColors.green
                                    : AppColors.orange,
                                onTap: () async {
                                  await Navigator.pushNamed(
                                    context,
                                    '/face_register',
                                    arguments: widget.employee,
                                  );
                                  await _checkFaceStatus();
                                  if (widget.onReload != null) {
                                    widget.onReload!();
                                  }
                                },
                              ),
                              _buildDivider(),
                              _buildSettingTile(
                                icon: Icons.sync_rounded,
                                iconColor: AppColors.accentSoft,
                                title: 'Dernière synchronisation',
                                trailing: _syncTime,
                                onTap: _loadStatsAndSync,
                              ),
                              _buildDivider(),
                              _buildSettingTile(
                                icon: Icons.cloud_done_rounded,
                                iconColor: AppColors.green,
                                title: 'Sauvegarde locale automatique',
                                trailing: 'Actif',
                                trailingColor: AppColors.green,
                              ),
                              _buildDivider(),
                              _buildSettingTile(
                                icon: Icons.cleaning_services_rounded,
                                iconColor: AppColors.red,
                                title: 'Vider le cache local',
                                onTap: _clearCache,
                                hasArrow: true,
                              ),
                              _buildDivider(),
                              _buildThemeToggleTile(),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: 28),

                      // ── Logout Button ─────────────────────────────────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        child: GestureDetector(
                          onTap: widget.onLogout,
                          child: Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(vertical: 18),
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [AppColors.red, const Color(0xFFD32F2F)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: AppColors.redGlow,
                                  blurRadius: 20,
                                  offset: const Offset(0, 6),
                                )
                              ],
                            ),
                            child: Center(
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.logout_rounded,
                                      color: Colors.white, size: 20),
                                  const SizedBox(width: 10),
                                  Text(
                                    'DÉCONNEXION',
                                    style: GoogleFonts.outfit(
                                      color: Colors.white,
                                      fontSize: 15,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: 0.8,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),

                      const SizedBox(height: 120),
                    ],
                  ),
                ),
              ),
            ),
    );
  }

  Widget _buildHeroBanner(
      String initials, String name, String matricule, String dept) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: AppColors.heroGradient,
        border: Border(
          bottom: BorderSide(color: AppColors.accent.withOpacity(0.15)),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(20, 32, 20, 32),
      child: Column(
        children: [
          // Avatar avec ring animé
          Stack(
            alignment: Alignment.center,
            children: [
              // Ring extérieur
              Container(
                width: 110,
                height: 110,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: AppColors.accent.withOpacity(0.35),
                    width: 2,
                  ),
                ),
              ),
              // Avatar
              Container(
                width: 96,
                height: 96,
                decoration: BoxDecoration(
                  gradient: AppColors.accentGradient,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.accentGlow,
                      blurRadius: 24,
                      offset: const Offset(0, 8),
                    )
                  ],
                ),
                child: Center(
                  child: Text(
                    initials,
                    style: GoogleFonts.outfit(
                      fontSize: 34,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),
          Text(
            name,
            style: GoogleFonts.outfit(
              color: AppColors.textPri,
              fontSize: 22,
              fontWeight: FontWeight.bold,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Matricule : $matricule',
            style: GoogleFonts.robotoMono(
              color: AppColors.textSec,
              fontSize: 12,
              letterSpacing: 0.5,
            ),
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
            decoration: BoxDecoration(
              color: AppColors.accent.withOpacity(0.14),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                  color: AppColors.accent.withOpacity(0.25), width: 1),
            ),
            child: Text(
              dept.toUpperCase(),
              style: GoogleFonts.outfit(
                color: AppColors.accentSoft,
                fontSize: 10,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.0,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard({
    required String label,
    required String value,
    required Color color,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surfaceHigh,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.2)),
        boxShadow: [
          BoxShadow(
            color: color.withOpacity(0.07),
            blurRadius: 12,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 16),
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: GoogleFonts.outfit(
              color: color,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: GoogleFonts.inter(
              color: AppColors.textSec,
              fontSize: 10,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildThemeToggleTile() {
    return ValueListenableBuilder<bool>(
      valueListenable: ThemeManager.isDarkNotifier,
      builder: (context, isDark, _) {
        return InkWell(
          onTap: ThemeManager.toggleTheme,
          borderRadius: BorderRadius.circular(20),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.accentSoft.withOpacity(0.12),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
                    color: AppColors.accentSoft,
                    size: 18,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Text(
                    'Mode Sombre',
                    style: GoogleFonts.inter(
                      color: AppColors.textPri,
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
                Switch(
                  value: isDark,
                  onChanged: (val) => ThemeManager.toggleTheme(),
                  activeColor: AppColors.accent,
                  activeTrackColor: AppColors.accent.withOpacity(0.3),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildSettingTile({
    required IconData icon,
    required Color iconColor,
    required String title,
    String? trailing,
    Color? trailingColor,
    VoidCallback? onTap,
    bool hasArrow = false,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: iconColor.withOpacity(0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: iconColor, size: 18),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Text(
                title,
                style: GoogleFonts.inter(
                  color: AppColors.textPri,
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            if (trailing != null)
              Text(
                trailing,
                style: GoogleFonts.inter(
                  color: trailingColor ?? AppColors.textSec,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                ),
              )
            else if (hasArrow)
              Icon(Icons.arrow_forward_ios_rounded,
                  color: AppColors.textSec.withOpacity(0.4), size: 14),
          ],
        ),
      ),
    );
  }

  Widget _buildDivider() {
    return Divider(
      height: 1,
      thickness: 1,
      color: AppColors.border,
      indent: 20,
      endIndent: 20,
    );
  }
}
