import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/local_storage_service.dart';

// ═══════════════════════════════════════════════════════════════════════════════
// Design Tokens
// ═══════════════════════════════════════════════════════════════════════════════
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

class _ProfilScreenState extends State<ProfilScreen> {
  final LocalStorageService _localSvc = LocalStorageService();
  bool _isLoading = true;
  double _presenceRate = 0.0;
  int _lates = 0;
  int _absences = 0;
  String _syncTime = 'Jamais';

  @override
  void initState() {
    super.initState();
    _loadStatsAndSync();
  }

  Future<void> _loadStatsAndSync() async {
    if (mounted) setState(() => _isLoading = true);
    
    // Load last sync time
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

    // Force a sync to fetch latest stats from the API
    await _localSvc.syncNow(widget.employee['id']);

    // Load stats from cache (which should now be updated)
    final local = await _localSvc.loadDashboardData(widget.employee['id']);
    if (local != null) {
      final stats = local['stats'] as Map<String, dynamic>?;
      if (stats != null) {
        _presenceRate = (stats['presence'] ?? 0.0).toDouble();
        _lates = (stats['retard'] ?? 0).toInt();
        _absences = (stats['absence'] ?? 0).toInt();
      }
    }

    if (mounted) {
      setState(() {
        _isLoading = false;
      });
    }
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
        backgroundColor: AppColors.card,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Vider le cache local ?',
          style: GoogleFonts.outfit(color: AppColors.textPri, fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Cela supprimera les rapports sauvegardés localement. Vous devrez vous synchroniser à nouveau pour récupérer les données.',
          style: GoogleFonts.inter(color: AppColors.textSec, fontSize: 13, height: 1.5),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: Text('Annuler', style: GoogleFonts.inter(color: AppColors.textSec)),
          ),
          ElevatedButton(
            onPressed: () async {
              final sm = ScaffoldMessenger.of(context);
              final prefs = await SharedPreferences.getInstance();
              await prefs.remove('dashboard_${widget.employee['id']}');
              if (ctx.mounted) Navigator.of(ctx).pop();
              _loadStatsAndSync();
              sm.showSnackBar(SnackBar(
                content: Text('Cache local vidé avec succès'),
                backgroundColor: AppColors.accent,
                behavior: SnackBarBehavior.floating,
              ));
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.red),
            child: Text('Vider', style: GoogleFonts.inter(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final initials = _getInitials();
    final name = '${widget.employee['first_name'] ?? ''} ${widget.employee['last_name'] ?? ''}';
    final matricule = widget.employee['matricule'] ?? '—';
    final dept = widget.employee['departement'] ?? 'Sans Département';

    return Scaffold(
      backgroundColor: AppColors.bg,
      body: _isLoading
          ? Center(child: CircularProgressIndicator(color: AppColors.accent))
          : RefreshIndicator(
              color: AppColors.accent,
              backgroundColor: AppColors.card,
              onRefresh: _loadStatsAndSync,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    const SizedBox(height: 20),
                    // ── Header Profil (Bubble Avatar + Name) ────────────────
                    Container(
                      width: 100,
                      height: 100,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [AppColors.accent, AppColors.accentSoft],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: AppColors.accent.withValues(alpha: 0.3),
                            blurRadius: 20,
                            offset: const Offset(0, 8),
                          )
                        ],
                      ),
                      child: Center(
                        child: Text(
                          initials,
                          style: GoogleFonts.outfit(
                            fontSize: 36,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),
                    Text(
                      name,
                      style: GoogleFonts.outfit(
                        color: AppColors.textPri,
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Matricule : $matricule',
                      style: GoogleFonts.inter(
                        color: AppColors.textSec,
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppColors.accent.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: AppColors.accent.withValues(alpha: 0.2), width: 1),
                      ),
                      child: Text(
                        dept.toUpperCase(),
                        style: GoogleFonts.outfit(
                          color: AppColors.accentSoft,
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 0.8,
                        ),
                      ),
                    ),
                    const SizedBox(height: 32),

                    // ── Stat Row ──────────────────────────────────────────
                    Row(
                      children: [
                        Expanded(
                          child: _buildStatCard(
                            label: 'Assiduité',
                            value: '${_presenceRate.toInt()}%',
                            color: _presenceRate >= 80 ? AppColors.green : AppColors.orange,
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
                    const SizedBox(height: 32),

                    // ── Settings List ─────────────────────────────────────
                    Align(
                      alignment: Alignment.centerLeft,
                      child: Padding(
                        padding: const EdgeInsets.only(left: 4, bottom: 12),
                        child: Text(
                          'PARAMÈTRES SYSTÈME',
                          style: GoogleFonts.outfit(
                            color: AppColors.textSec,
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 1.0,
                          ),
                        ),
                      ),
                    ),
                    Container(
                      decoration: BoxDecoration(
                        color: AppColors.card,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
                      ),
                      child: Column(
                        children: [
                          _buildSettingTile(
                            icon: Icons.calendar_month_rounded,
                            title: 'Mes Congés & Permissions',
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => LeavesScreen(employeeId: widget.employee['id']),
                                ),
                              );
                            },
                          ),
                          _buildDivider(),
                          _buildSettingTile(
                            icon: Icons.face_rounded,
                            title: 'Enregistrement Facial',
                            trailing: widget.employee['consent_facial_at'] != null ? 'Enregistré' : 'Non configuré',
                            trailingColor: widget.employee['consent_facial_at'] != null ? AppColors.green : AppColors.orange,
                            onTap: () async {
                              final res = await Navigator.pushNamed(
                                context,
                                '/face_register',
                                arguments: widget.employee,
                              );
                              if (widget.onReload != null) {
                                widget.onReload!();
                              }
                            },
                          ),
                          _buildDivider(),
                          _buildSettingTile(
                            icon: Icons.sync_rounded,
                            title: 'Dernière synchronisation',
                            trailing: _syncTime,
                            onTap: _loadStatsAndSync,
                          ),
                          _buildDivider(),
                          _buildSettingTile(
                            icon: Icons.cloud_done_rounded,
                            title: 'Sauvegarde locale automatique',
                            trailing: 'Actif',
                          ),
                          _buildDivider(),
                          _buildSettingTile(
                            icon: Icons.cleaning_services_rounded,
                            title: 'Vider le cache local',
                            trailingColor: AppColors.red,
                            onTap: _clearCache,
                          ),
                          _buildDivider(),
                          _buildThemeToggleTile(),
                        ],
                      ),
                    ),
                    const SizedBox(height: 40),

                    // ── Logout Button ─────────────────────────────────────
                    GestureDetector(
                      onTap: widget.onLogout,
                      child: Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 18),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            colors: [AppColors.red, Color(0xFFD63031)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: AppColors.red.withValues(alpha: 0.25),
                              blurRadius: 16,
                              offset: const Offset(0, 6),
                            )
                          ],
                        ),
                        child: Center(
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.logout_rounded, color: Colors.white, size: 20),
                              const SizedBox(width: 10),
                              Text(
                                'DÉCONNEXION',
                                style: GoogleFonts.outfit(
                                  color: Colors.white,
                                  fontSize: 14,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.8,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 120),
                  ],
                ),
              ),
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
        color: AppColors.card,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 14),
          Text(
            value,
            style: GoogleFonts.outfit(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: GoogleFonts.inter(
              color: AppColors.textSec,
              fontSize: 11,
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
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            child: Row(
              children: [
                Icon(isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded, color: AppColors.accentSoft, size: 20),
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
                ),
              ],
            ),
          ),
        );
      }
    );
  }

  Widget _buildSettingTile({
    required IconData icon,
    required String title,
    String? trailing,
    Color? trailingColor,
    VoidCallback? onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
        child: Row(
          children: [
            Icon(icon, color: AppColors.accentSoft, size: 20),
            const SizedBox(width: 16),
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
              ),
            if (onTap != null && trailing == null)
              const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white30, size: 14),
          ],
        ),
      ),
    );
  }

  Widget _buildDivider() {
    return Divider(
      height: 1,
      thickness: 1,
      color: Colors.white.withValues(alpha: 0.05),
      indent: 20,
      endIndent: 20,
    );
  }
}



