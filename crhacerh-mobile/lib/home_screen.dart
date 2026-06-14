import 'dart:math' as math;
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/message_screen.dart';
import 'screens/scannqr_screen.dart';
import 'screens/rapportscreen.dart';
import 'screens/profilscreen.dart';
import 'services/auth_service.dart';
import 'services/local_storage_service.dart';
import 'theme_manager.dart';

// ═══════════════════════════════════════════════════════════════════════════════
// HomeScreen — shell avec bottom nav premium
// ═══════════════════════════════════════════════════════════════════════════════
class HomeScreen extends StatefulWidget {
  final Map<String, dynamic> employee;
  const HomeScreen({super.key, required this.employee});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen>
    with SingleTickerProviderStateMixin {
  int _selectedIndex = 0;
  late AnimationController _navCtrl;
  Map<String, dynamic>? _currentEmployee;

  @override
  void initState() {
    super.initState();
    _currentEmployee = widget.employee;
    _navCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 200));
    _loadLocalEmployee();
  }

  Future<void> _loadLocalEmployee() async {
    final prefs = await SharedPreferences.getInstance();
    final cached = prefs.getString('cached_employee');
    if (cached != null) {
      if (mounted) {
        setState(() {
          _currentEmployee = jsonDecode(cached).cast<String, dynamic>();
        });
      }
    }
  }

  @override
  void dispose() {
    _navCtrl.dispose();
    super.dispose();
  }

  Future<void> _logout(BuildContext context) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('cached_employee');
    if (context.mounted) {
      Navigator.pushNamedAndRemoveUntil(
          context, '/matricule', (route) => false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final emp = _currentEmployee ?? widget.employee;
    final pages = [
      HomeTab(
        employeeId: emp['id'],
        name: '${emp['first_name']} ${emp['last_name']}',
        onLogout: () => _logout(context),
        employee: emp,
        onReload: _loadLocalEmployee,
      ),
      MessageScreen(employeeId: emp['id']),
      ScannQRScreen(employeeId: emp['id']),
      RapportsScreen(employee: emp),
      ProfilScreen(
        employee: emp,
        onLogout: () => _logout(context),
        onReload: _loadLocalEmployee,
      ),
    ];

    return ValueListenableBuilder<bool>(
      valueListenable: ThemeManager.isDarkNotifier,
      builder: (context, isDark, _) {
        return Scaffold(
          backgroundColor: AppColors.bg,
          body: SafeArea(
            child: IndexedStack(index: _selectedIndex, children: pages),
          ),
          bottomNavigationBar: _PremiumNavBar(
            selectedIndex: _selectedIndex,
            onTap: (i) {
              _loadLocalEmployee();
              setState(() => _selectedIndex = i);
            },
          ),
        );
      },
    );
  }
}

// ───────────────────────────────────────────────────────────────────────────────
// Premium bottom nav bar — Floating Glassmorphism redesigné
// ───────────────────────────────────────────────────────────────────────────────
class _PremiumNavBar extends StatelessWidget {
  final int selectedIndex;
  final ValueChanged<int> onTap;

  const _PremiumNavBar({required this.selectedIndex, required this.onTap});

  static const _items = [
    (Icons.home_rounded,        Icons.home_outlined,        'Accueil'),
    (Icons.chat_bubble_rounded, Icons.chat_bubble_outline,  'Messages'),
    (Icons.qr_code_scanner,     Icons.qr_code_scanner,      'Scanner'),
    (Icons.folder_rounded,      Icons.folder_outlined,       'Rapports'),
    (Icons.person_rounded,      Icons.person_outline,       'Profil'),
  ];

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
        child: Container(
          height: 72,
          decoration: BoxDecoration(
            color: AppColors.surfaceHigh.withOpacity(0.96),
            borderRadius: BorderRadius.circular(26),
            border: Border.all(
              color: Colors.white.withOpacity(0.08),
              width: 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.45),
                blurRadius: 24,
                offset: const Offset(0, 10),
              ),
              BoxShadow(
                color: AppColors.accent.withOpacity(0.06),
                blurRadius: 40,
                offset: const Offset(0, -4),
              ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: List.generate(_items.length, (i) {
              final isSelected = i == selectedIndex;
              final item = _items[i];

              // Bouton Scanner central — spécial
              if (i == 2) {
                return GestureDetector(
                  onTap: () => onTap(i),
                  child: TweenAnimationBuilder<double>(
                    tween: Tween(begin: 1.0, end: isSelected ? 1.08 : 1.0),
                    duration: const Duration(milliseconds: 200),
                    builder: (context, scale, _) {
                      return Transform.scale(
                        scale: scale,
                        child: Container(
                          width: 52,
                          height: 52,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            gradient: isSelected
                                ? AppColors.accentGradient
                                : const LinearGradient(
                                    colors: [Color(0xFF161A35), Color(0xFF1E2045)],
                                  ),
                            border: Border.all(
                              color: isSelected
                                  ? Colors.white.withOpacity(0.25)
                                  : Colors.white.withOpacity(0.07),
                              width: 1.5,
                            ),
                            boxShadow: isSelected
                                ? [
                                    BoxShadow(
                                      color: AppColors.accentGlow,
                                      blurRadius: 18,
                                      spreadRadius: 0,
                                    )
                                  ]
                                : [],
                          ),
                          child: Icon(
                            item.$1,
                            color: Colors.white,
                            size: 22,
                          ),
                        ),
                      );
                    },
                  ),
                );
              }

              return Expanded(
                child: GestureDetector(
                  onTap: () => onTap(i),
                  behavior: HitTestBehavior.opaque,
                  child: TweenAnimationBuilder<double>(
                    tween: Tween(begin: 0.0, end: isSelected ? 1.0 : 0.0),
                    duration: const Duration(milliseconds: 200),
                    builder: (context, t, _) {
                      final iconColor = Color.lerp(
                          AppColors.textSec, AppColors.accentSoft, t)!;
                      return Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            padding: EdgeInsets.all(isSelected ? 8 : 0),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? AppColors.accent.withOpacity(0.14)
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Icon(
                              isSelected ? item.$1 : item.$2,
                              size: 22,
                              color: iconColor,
                            ),
                          ),
                          const SizedBox(height: 2),
                          AnimatedDefaultTextStyle(
                            duration: const Duration(milliseconds: 200),
                            style: GoogleFonts.inter(
                              fontSize: 10,
                              fontWeight: isSelected
                                  ? FontWeight.w700
                                  : FontWeight.w400,
                              color: iconColor,
                            ),
                            child: Text(item.$3),
                          ),
                        ],
                      );
                    },
                  ),
                ),
              );
            }),
          ),
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// HomeTab — onglet principal redesigné
// ═══════════════════════════════════════════════════════════════════════════════
class HomeTab extends StatefulWidget {
  final int employeeId;
  final String name;
  final VoidCallback onLogout;
  final Map<String, dynamic> employee;
  final VoidCallback? onReload;

  const HomeTab({
    super.key,
    required this.employeeId,
    required this.name,
    required this.onLogout,
    required this.employee,
    this.onReload,
  });

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> with TickerProviderStateMixin {
  final AuthService _authService = AuthService();
  final LocalStorageService _localSvc = LocalStorageService();
  bool _isLoading = true;
  List<dynamic> _history = [];
  Map<String, dynamic> _stats = {
    'presence': 0.0,
    'retard': 0.0,
    'absence': 0.0
  };

  late AnimationController _heroCtrl;
  late Animation<double> _heroFade;
  late AnimationController _cardsCtrl;
  late Animation<Offset> _cardsSlide;

  @override
  void initState() {
    super.initState();
    _heroCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 800));
    _heroFade = CurvedAnimation(parent: _heroCtrl, curve: Curves.easeOut);
    _cardsCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 600));
    _cardsSlide = Tween<Offset>(
            begin: const Offset(0, 0.08), end: Offset.zero)
        .animate(
            CurvedAnimation(parent: _cardsCtrl, curve: Curves.easeOutCubic));
    _loadData();
  }

  @override
  void dispose() {
    _localSvc.stopAutoSync();
    _heroCtrl.dispose();
    _cardsCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    if (mounted) setState(() => _isLoading = true);
    _heroCtrl.forward();
    final result = await _authService.fetchDashboardData(widget.employeeId);
    if (mounted) {
      if (result['success'] == true) {
        await _localSvc.saveDashboardData(widget.employeeId, result['data']);
        _localSvc.startAutoSync(widget.employeeId);
        setState(() {
          _history = result['data']['history'] ?? [];
          _stats = {
            'presence': (result['data']['stats']['presence'] ?? 0).toDouble(),
            'retard': (result['data']['stats']['retard'] ?? 0).toDouble(),
            'absence': (result['data']['stats']['absence'] ?? 0).toDouble(),
          };
          _isLoading = false;
        });
      } else {
        final local = await _localSvc.loadDashboardData(widget.employeeId);
        if (local != null && mounted) {
          setState(() {
            _history = local['history'] ?? [];
            _stats = {
              'presence': (local['stats']?['presence'] ?? 0).toDouble(),
              'retard': (local['stats']?['retard'] ?? 0).toDouble(),
              'absence': (local['stats']?['absence'] ?? 0).toDouble(),
            };
            _isLoading = false;
          });
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
              content: Text('Mode hors-ligne — données locales',
                  style: GoogleFonts.inter(fontWeight: FontWeight.w500)),
              backgroundColor: AppColors.orange,
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ));
          }
        } else {
          if (mounted) setState(() => _isLoading = false);
        }
      }
      _cardsCtrl.forward();
    }
  }

  Color _getClockColor() {
    final pointed =
        _history.any((p) => p['status'] == 'Present' || p['status'] == 'Retard');
    if (pointed) return AppColors.textPri;
    final now = DateTime.now();
    if (now.hour > 8 || (now.hour == 8 && now.minute > 15)) {
      return AppColors.red;
    }
    return AppColors.green;
  }

  String _greet() {
    final h = DateTime.now().hour;
    if (h < 12) return 'Bonjour 👋';
    if (h < 18) return 'Bon après-midi 👋';
    return 'Bonsoir 👋';
  }

  static String _twoDigits(int v) => v.toString().padLeft(2, '0');
  static const _months = [
    'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun',
    'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'
  ];

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final dateLabel = '${_twoDigits(now.day)} ${_months[now.month - 1]} ${now.year}';
    final timeLabel = '${_twoDigits(now.hour)}:${_twoDigits(now.minute)}';
    final clockColor = _getClockColor();

    return RefreshIndicator(
      color: AppColors.accentSoft,
      backgroundColor: AppColors.surfaceHigh,
      onRefresh: _loadData,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          // ── Hero header ──────────────────────────────────────────────────────
          SliverToBoxAdapter(
            child: FadeTransition(
              opacity: _heroFade,
              child: _HeroHeader(
                greet: _greet(),
                name: widget.name,
                date: dateLabel,
                time: timeLabel,
                clockColor: clockColor,
                onLogout: widget.onLogout,
                presence: _stats['presence'] ?? 0,
                retard: _stats['retard'] ?? 0,
                absence: _stats['absence'] ?? 0,
              ),
            ),
          ),

          // ── Banner face enrollment ────────────────────────────────────────
          SliverToBoxAdapter(
            child: SlideTransition(
              position: _cardsSlide,
              child: FadeTransition(
                opacity: _heroFade,
                child: _buildFaceEnrollmentBanner(context),
              ),
            ),
          ),

          if (_isLoading)
            SliverFillRemaining(
              child: Center(
                child: CircularProgressIndicator(
                  color: AppColors.accentSoft,
                  strokeWidth: 2.5,
                ),
              ),
            )
          else ...[
            // ── Stat pills ──────────────────────────────────────────────────
            SliverToBoxAdapter(
              child: SlideTransition(
                position: _cardsSlide,
                child: FadeTransition(
                  opacity: _heroFade,
                  child: _StatPillsRow(stats: _stats),
                ),
              ),
            ),

            // ── Historique ──────────────────────────────────────────────────
            SliverToBoxAdapter(
              child: SlideTransition(
                position: _cardsSlide,
                child: FadeTransition(
                  opacity: _heroFade,
                  child: _AttendanceSection(history: _history),
                ),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 120)),
          ],
        ],
      ),
    );
  }

  Widget _buildFaceEnrollmentBanner(BuildContext context) {
    if (widget.employee['consent_facial_at'] != null) {
      return const SizedBox.shrink();
    }
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppColors.orange.withOpacity(0.18),
            AppColors.orange.withOpacity(0.06),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: AppColors.orange.withOpacity(0.35), width: 1.2),
      ),
      child: Row(
        children: [
          _PulsingIcon(color: AppColors.orange, icon: Icons.face_rounded),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Enregistrement facial requis',
                  style: GoogleFonts.outfit(
                    color: AppColors.textPri,
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  'Enregistrez votre visage pour pointer en cas d\'oubli de téléphone.',
                  style: GoogleFonts.inter(
                    color: AppColors.textSec,
                    fontSize: 11,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          GestureDetector(
            onTap: () async {
              await Navigator.pushNamed(
                context,
                '/face_register',
                arguments: widget.employee,
              );
              if (widget.onReload != null) widget.onReload!();
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: AppColors.orange,
                borderRadius: BorderRadius.circular(14),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.orangeGlow,
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  )
                ],
              ),
              child: Text(
                'Enregistrer',
                style: GoogleFonts.outfit(
                  color: Colors.white,
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ───────────────────────────────────────────────────────────────────────────────
// Widget icône pulsante (pour le banner face)
// ───────────────────────────────────────────────────────────────────────────────
class _PulsingIcon extends StatefulWidget {
  final Color color;
  final IconData icon;
  const _PulsingIcon({required this.color, required this.icon});

  @override
  State<_PulsingIcon> createState() => _PulsingIconState();
}

class _PulsingIconState extends State<_PulsingIcon>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;
  late Animation<double> _scale;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 1200))
      ..repeat(reverse: true);
    _scale = Tween<double>(begin: 0.95, end: 1.05).animate(
        CurvedAnimation(parent: _ctrl, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ScaleTransition(
      scale: _scale,
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: widget.color.withOpacity(0.18),
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: widget.color.withOpacity(0.25),
              blurRadius: 12,
              spreadRadius: 0,
            )
          ],
        ),
        child: Icon(widget.icon, color: widget.color, size: 22),
      ),
    );
  }
}

// ───────────────────────────────────────────────────────────────────────────────
// _HeroHeader redesigné
// ───────────────────────────────────────────────────────────────────────────────
class _HeroHeader extends StatelessWidget {
  final String greet, name, date, time;
  final Color clockColor;
  final VoidCallback onLogout;
  final double presence, retard, absence;

  const _HeroHeader({
    required this.greet,
    required this.name,
    required this.date,
    required this.time,
    required this.clockColor,
    required this.onLogout,
    required this.presence,
    required this.retard,
    required this.absence,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: AppColors.heroGradient,
        borderRadius: BorderRadius.circular(28),
        border: Border.all(
          color: AppColors.accent.withOpacity(0.18),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: AppColors.accent.withOpacity(0.18),
            blurRadius: 40,
            offset: const Offset(0, 14),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Row: greet + logout ──────────────────────────────────────────
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    greet,
                    style: GoogleFonts.inter(
                      color: AppColors.accentSoft,
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      letterSpacing: 0.3,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    name,
                    style: GoogleFonts.outfit(
                      color: Colors.white,
                      fontSize: 21,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.3,
                    ),
                  ),
                ],
              ),
              GestureDetector(
                onTap: onLogout,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.07),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: Colors.white.withOpacity(0.1)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.logout_rounded, size: 15, color: Colors.white60),
                      const SizedBox(width: 6),
                      Text(
                        'Quitter',
                        style: GoogleFonts.inter(
                          color: Colors.white60,
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 24),

          // ── Horloge + donut ───────────────────────────────────────────────
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      time,
                      style: GoogleFonts.outfit(
                        color: clockColor,
                        fontSize: 58,
                        fontWeight: FontWeight.w900,
                        height: 0.95,
                        letterSpacing: -3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      date,
                      style: GoogleFonts.inter(
                        color: Colors.white38,
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        letterSpacing: 0.3,
                      ),
                    ),
                  ],
                ),
              ),
              SizedBox(
                width: 82,
                height: 82,
                child: CustomPaint(
                  painter: _MiniDonut(
                      presence: presence, retard: retard, absence: absence),
                  child: Center(
                    child: Text(
                      '${presence.toInt()}%',
                      style: GoogleFonts.outfit(
                        color: Colors.white,
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 20),

          // ── Status indicator ──────────────────────────────────────────────
          _StatusIndicator(presence: presence),
        ],
      ),
    );
  }
}

class _StatusIndicator extends StatelessWidget {
  final double presence;
  const _StatusIndicator({required this.presence});

  @override
  Widget build(BuildContext context) {
    final isGood = presence >= 80;
    final color = isGood ? AppColors.green : AppColors.orange;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      decoration: BoxDecoration(
        color: color.withOpacity(0.13),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.28)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: BoxDecoration(
              color: color,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(color: color.withOpacity(0.6), blurRadius: 6)
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            isGood ? 'Assiduité excellente' : 'Assiduité à améliorer',
            style: GoogleFonts.inter(
              color: color,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

// ───────────────────────────────────────────────────────────────────────────────
// _StatPillsRow — 3 cartes horizontales redessinées
// ───────────────────────────────────────────────────────────────────────────────
class _StatPillsRow extends StatelessWidget {
  final Map<String, dynamic> stats;
  const _StatPillsRow({required this.stats});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      child: Row(
        children: [
          _StatPill(
            value: '${(stats['presence'] ?? 0).toInt()}%',
            label: 'Présence',
            color: AppColors.green,
            icon: Icons.check_circle_rounded,
          ),
          const SizedBox(width: 10),
          _StatPill(
            value: '${(stats['retard'] ?? 0).toInt()}%',
            label: 'Retards',
            color: AppColors.orange,
            icon: Icons.schedule_rounded,
          ),
          const SizedBox(width: 10),
          _StatPill(
            value: '${(stats['absence'] ?? 0).toInt()}%',
            label: 'Absences',
            color: AppColors.red,
            icon: Icons.cancel_rounded,
          ),
        ],
      ),
    );
  }
}

class _StatPill extends StatelessWidget {
  final String value, label;
  final Color color;
  final IconData icon;
  const _StatPill({
    required this.value,
    required this.label,
    required this.color,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 10),
        decoration: BoxDecoration(
          color: AppColors.surfaceHigh,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: color.withOpacity(0.22)),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.08),
              blurRadius: 12,
              offset: const Offset(0, 4),
            )
          ],
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 18),
            ),
            const SizedBox(height: 10),
            Text(
              value,
              style: GoogleFonts.outfit(
                color: color,
                fontSize: 20,
                fontWeight: FontWeight.w800,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 3),
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
      ),
    );
  }
}

// ───────────────────────────────────────────────────────────────────────────────
// _AttendanceSection — timeline redessinée
// ───────────────────────────────────────────────────────────────────────────────
class _AttendanceSection extends StatelessWidget {
  final List<dynamic> history;
  const _AttendanceSection({required this.history});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      decoration: BoxDecoration(
        color: AppColors.surfaceHigh,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 18, 20, 0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Historique des pointages',
                  style: GoogleFonts.outfit(
                    color: AppColors.textPri,
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppColors.accent.withOpacity(0.14),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.accent.withOpacity(0.2)),
                  ),
                  child: Text(
                    '${history.length} entrées',
                    style: GoogleFonts.inter(
                      color: AppColors.accentSoft,
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          if (history.isEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
              child: Text(
                'Aucun pointage récent.',
                style: GoogleFonts.inter(
                  color: AppColors.textSec,
                  fontSize: 14,
                ),
              ),
            )
          else
            ...List.generate(history.length, (i) {
              final item = history[i];
              final isLast = i == history.length - 1;
              return _AttendanceRow(
                date: item['date'] ?? '',
                time: item['time'] ?? '',
                status: item['status'] ?? '',
                isLast: isLast,
              );
            }),
        ],
      ),
    );
  }
}

class _AttendanceRow extends StatelessWidget {
  final String date, time, status;
  final bool isLast;

  const _AttendanceRow({
    required this.date,
    required this.time,
    required this.status,
    required this.isLast,
  });

  Color get _statusColor {
    switch (status.toUpperCase()) {
      case 'PRESENT':
        return AppColors.green;
      case 'RETARD':
        return AppColors.orange;
      case 'ABSENT':
        return AppColors.red;
      default:
        return AppColors.textSec;
    }
  }

  IconData get _statusIcon {
    switch (status.toUpperCase()) {
      case 'PRESENT':
        return Icons.check_circle_rounded;
      case 'RETARD':
        return Icons.schedule_rounded;
      case 'ABSENT':
        return Icons.cancel_rounded;
      default:
        return Icons.circle_outlined;
    }
  }

  String get _statusLabel {
    switch (status.toUpperCase()) {
      case 'PRESENT':
        return 'Présent';
      case 'RETARD':
        return 'Retard';
      case 'ABSENT':
        return 'Absent';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
      decoration: BoxDecoration(
        border: isLast
            ? null
            : Border(
                bottom: BorderSide(color: AppColors.border, width: 1)),
      ),
      child: Row(
        children: [
          // Icône statut
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(
              color: _statusColor.withOpacity(0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(_statusIcon, color: _statusColor, size: 17),
          ),
          const SizedBox(width: 14),
          // Date
          Expanded(
            flex: 3,
            child: Text(
              date,
              style: GoogleFonts.inter(
                color: AppColors.textPri,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          // Heure
          Text(
            time,
            style: GoogleFonts.robotoMono(
              color: AppColors.textSec,
              fontSize: 12,
            ),
          ),
          const SizedBox(width: 12),
          // Badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: _statusColor.withOpacity(0.12),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: _statusColor.withOpacity(0.25)),
            ),
            child: Text(
              _statusLabel,
              style: GoogleFonts.inter(
                color: _statusColor,
                fontSize: 10,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ───────────────────────────────────────────────────────────────────────────────
// Mini donut painter
// ───────────────────────────────────────────────────────────────────────────────
class _MiniDonut extends CustomPainter {
  final double presence, retard, absence;
  const _MiniDonut(
      {required this.presence, required this.retard, required this.absence});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width * 0.40;
    final strokeW = size.width * 0.14;
    final rect = Rect.fromCircle(center: center, radius: radius);
    final paint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeW
      ..strokeCap = StrokeCap.round;

    paint.color = Colors.white.withOpacity(0.06);
    canvas.drawCircle(center, radius, paint);

    double start = -math.pi / 2;
    void drawArc(double pct, Color color) {
      if (pct <= 0) return;
      paint.color = color;
      final sweep = (pct / 100) * 2 * math.pi;
      canvas.drawArc(rect, start, sweep, false, paint);
      start += sweep;
    }

    drawArc(presence, AppColors.green);
    drawArc(retard, AppColors.orange);
    drawArc(absence, AppColors.red);
  }

  @override
  bool shouldRepaint(covariant _MiniDonut old) =>
      old.presence != presence ||
      old.retard != retard ||
      old.absence != absence;
}

// ───────────────────────────────────────────────────────────────────────────────
// Legacy custom icons (kept unchanged for compatibility)
// ───────────────────────────────────────────────────────────────────────────────
class CustomChatBubbleIcon extends StatelessWidget {
  final Color color;
  final Color dotColor;
  final double size;

  const CustomChatBubbleIcon({
    super.key,
    required this.color,
    required this.dotColor,
    this.size = 24.0,
  });

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      size: Size(size, size),
      painter: _ChatBubblePainter(color: color, dotColor: dotColor),
    );
  }
}

class _ChatBubblePainter extends CustomPainter {
  final Color color;
  final Color dotColor;
  _ChatBubblePainter({required this.color, required this.dotColor});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;
    final w = size.width;
    final h = size.height;
    final rrect = RRect.fromRectAndRadius(
        Rect.fromLTWH(0, 0, w, h * 0.85), Radius.circular(w * 0.45));
    final path = Path()..addRRect(rrect);
    final tail = Path()
      ..moveTo(w * 0.22, h * 0.7)
      ..lineTo(w * 0.08, h * 0.95)
      ..quadraticBezierTo(w * 0.16, h * 0.92, w * 0.35, h * 0.78)
      ..close();
    path.addPath(tail, Offset.zero);
    canvas.drawPath(path, paint);
    final dotPaint = Paint()
      ..color = dotColor
      ..style = PaintingStyle.fill;
    final cy = h * 0.425;
    final dr = w * 0.055;
    final sp = w * 0.16;
    canvas.drawCircle(Offset(w / 2 - sp, cy), dr, dotPaint);
    canvas.drawCircle(Offset(w / 2, cy), dr, dotPaint);
    canvas.drawCircle(Offset(w / 2 + sp, cy), dr, dotPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter old) => false;
}

class BiometricScannerIcon extends StatelessWidget {
  final Color color;
  final double size;
  const BiometricScannerIcon({super.key, required this.color, this.size = 24.0});

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      size: Size(size, size),
      painter: _BiometricScannerPainter(color: color),
    );
  }
}

class _BiometricScannerPainter extends CustomPainter {
  final Color color;
  _BiometricScannerPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final p = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = size.width * 0.08
      ..strokeCap = StrokeCap.round;
    final w = size.width;
    final h = size.height;
    final r = w * 0.12;
    final paths = [
      [Offset(r, 0), Offset(0, r), const Offset(0, 0)],
      [Offset(w - r, 0), Offset(w, r), Offset(w, 0)],
      [Offset(0, h - r), Offset(r, h), Offset(0, h)],
      [Offset(w, h - r), Offset(w - r, h), Offset(w, h)],
    ];
    for (final pts in paths) {
      final pth = Path()
        ..moveTo(pts[0].dx, pts[0].dy)
        ..lineTo(pts[2].dx, pts[2].dy)
        ..lineTo(pts[1].dx, pts[1].dy);
      canvas.drawPath(pth, p);
    }
    for (int i = 0; i < 3; i++) {
      final y = h * (0.35 + i * 0.15);
      canvas.drawLine(Offset(w * 0.2, y), Offset(w * 0.8, y), p);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter old) => false;
}
