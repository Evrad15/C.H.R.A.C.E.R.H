import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/message_screen.dart';
import 'screens/scannqr_screen.dart';
import 'screens/rapportscreen.dart';
import 'screens/profilscreen.dart';
import 'services/auth_service.dart';
import 'services/local_storage_service.dart';

// ═══════════════════════════════════════════════════════════════════════════════
// Design tokens
// ═══════════════════════════════════════════════════════════════════════════════
class _C {
  static const bg         = Color(0xFF0D0D0D);
  static const card       = Color(0xFF1A1A1A);
  static const accent     = Color(0xFF6C63FF);
  static const accentSoft = Color(0xFF8B83FF);
  static const green      = Color(0xFF00C48C);
  static const orange     = Color(0xFFFF8C42);
  static const red        = Color(0xFFFF4757);
  static const textPri    = Color(0xFFFFFFFF);
  static const textSec    = Color(0xFF9E9E9E);
}

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

  @override
  void initState() {
    super.initState();
    _navCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 200));
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
    final pages = [
      HomeTab(
        employeeId: widget.employee['id'],
        name: '${widget.employee['first_name']} ${widget.employee['last_name']}',
        onLogout: () => _logout(context),
        employee: widget.employee,
      ),
      MessageScreen(employeeId: widget.employee['id']),
      ScannQRScreen(employeeId: widget.employee['id']),
      RapportsScreen(employee: widget.employee),
      ProfilScreen(
        employee: widget.employee,
        onLogout: () => _logout(context),
      ),
    ];

    return Scaffold(
      backgroundColor: _C.bg,
      body: SafeArea(
        child: IndexedStack(index: _selectedIndex, children: pages),
      ),
      bottomNavigationBar: _PremiumNavBar(
        selectedIndex: _selectedIndex,
        onTap: (i) => setState(() => _selectedIndex = i),
      ),
    );
  }
}

// ───────────────────────────────────────────────────────────────────────────────
// Premium bottom nav bar (Floating Glassmorphism)
// ───────────────────────────────────────────────────────────────────────────────
class _PremiumNavBar extends StatelessWidget {
  final int selectedIndex;
  final ValueChanged<int> onTap;

  const _PremiumNavBar({required this.selectedIndex, required this.onTap});

  static const _items = [
    (Icons.home_rounded,         Icons.home_outlined,        'Accueil'),
    (Icons.chat_bubble_rounded,  Icons.chat_bubble_outline,  'Messages'),
    (Icons.qr_code_scanner,      Icons.qr_code_scanner,      'Scanner'),
    (Icons.folder_rounded,       Icons.folder_outlined,      'Rapports'),
    (Icons.person_rounded,       Icons.person_outline,       'Profil'),
  ];

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
        child: Container(
          height: 68,
          decoration: BoxDecoration(
            color: const Color(0xFF121212).withValues(alpha: 0.90),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(
              color: Colors.white.withValues(alpha: 0.08),
              width: 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.35),
                blurRadius: 16,
                offset: const Offset(0, 8),
              )
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: List.generate(_items.length, (i) {
              final isSelected = i == selectedIndex;
              final item = _items[i];

              // Scanner Center button
              if (i == 2) {
                return GestureDetector(
                  onTap: () => onTap(i),
                  child: TweenAnimationBuilder<double>(
                    tween: Tween(begin: 1.0, end: isSelected ? 1.10 : 1.0),
                    duration: const Duration(milliseconds: 200),
                    builder: (context, scale, child) {
                      return Transform.scale(
                        scale: scale,
                        child: Container(
                          width: 52,
                          height: 52,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            gradient: LinearGradient(
                              colors: isSelected
                                  ? [_C.accent, _C.accentSoft]
                                  : [const Color(0xFF1E1E1E), const Color(0xFF282828)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            border: Border.all(
                              color: isSelected
                                  ? Colors.white.withValues(alpha: 0.2)
                                  : Colors.white.withValues(alpha: 0.08),
                              width: 1.2,
                            ),
                            boxShadow: isSelected
                                ? [
                                    BoxShadow(
                                      color: _C.accent.withValues(alpha: 0.4),
                                      blurRadius: 14,
                                      offset: const Offset(0, 4),
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
                    builder: (context, t, child) {
                      final color = Color.lerp(_C.textSec, _C.accentSoft, t);
                      final iconScale = 1.0 + (t * 0.08);
                      return Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Transform.scale(
                            scale: iconScale,
                            child: Icon(
                              isSelected ? item.$1 : item.$2,
                              size: 22,
                              color: color,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Opacity(
                            opacity: t,
                            child: Container(
                              width: 4,
                              height: 4,
                              decoration: const BoxDecoration(
                                color: _C.accentSoft,
                                shape: BoxShape.circle,
                              ),
                            ),
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
// HomeTab — onglet principal avec design sombre premium
// ═══════════════════════════════════════════════════════════════════════════════
class HomeTab extends StatefulWidget {
  final int employeeId;
  final String name;
  final VoidCallback onLogout;
  final Map<String, dynamic> employee;

  const HomeTab({
    super.key,
    required this.employeeId,
    required this.name,
    required this.onLogout,
    required this.employee,
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
    _heroFade =
        CurvedAnimation(parent: _heroCtrl, curve: Curves.easeOut);
    _cardsCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 600));
    _cardsSlide = Tween<Offset>(
            begin: const Offset(0, 0.1), end: Offset.zero)
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
    if (mounted) {
      setState(() => _isLoading = true);
    } else {
      _isLoading = true;
    }
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
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text('Mode hors-ligne — données locales'),
              backgroundColor: Colors.orange,
              behavior: SnackBarBehavior.floating,
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
    final pointed = _history
        .any((p) => p['status'] == 'Present' || p['status'] == 'Retard');
    if (pointed) return _C.textPri;
    final now = DateTime.now();
    if (now.hour > 8 || (now.hour == 8 && now.minute > 15)) {
      return _C.red;
    }
    return _C.green;
  }

  String _greet() {
    final h = DateTime.now().hour;
    if (h < 12) return 'Bonjour';
    if (h < 18) return 'Bon après-midi';
    return 'Bonsoir';
  }

  static String _twoDigits(int v) => v.toString().padLeft(2, '0');
  static const _months = [
    'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun',
    'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'
  ];

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final dateLabel =
        '${_twoDigits(now.day)} ${_months[now.month - 1]} ${now.year}';
    final timeLabel = '${_twoDigits(now.hour)}:${_twoDigits(now.minute)}';
    final clockColor = _getClockColor();

    return RefreshIndicator(
      color: _C.accent,
      backgroundColor: _C.card,
      onRefresh: _loadData,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          // ── Hero header ────────────────────────────────────────────────────
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

          if (_isLoading)
            const SliverFillRemaining(
              child: Center(
                child: CircularProgressIndicator(color: _C.accent),
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

            // ── Historique pointages ────────────────────────────────────────
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
}

// ───────────────────────────────────────────────────────────────────────────────
// _HeroHeader
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
        gradient: const LinearGradient(
          colors: [Color(0xFF1E1B4B), Color(0xFF2D1B69)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: _C.accent.withValues(alpha: 0.25),
            blurRadius: 30,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row: greet + logout
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    greet,
                    style: const TextStyle(
                      color: Color(0xFFB0A8FF),
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    name,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.5,
                    ),
                  ),
                ],
              ),
              GestureDetector(
                onTap: onLogout,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                        color: Colors.white.withValues(alpha: 0.15)),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.logout_rounded,
                          size: 16, color: Colors.white70),
                      SizedBox(width: 6),
                      Text('Quitter',
                          style: TextStyle(
                              color: Colors.white70, fontSize: 13)),
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 24),

          // Big clock + date
          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      time,
                      style: TextStyle(
                        color: clockColor,
                        fontSize: 56,
                        fontWeight: FontWeight.w900,
                        height: 1,
                        letterSpacing: -2,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      date,
                      style: const TextStyle(
                        color: Color(0xFF9090BB),
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              // Mini donut
              SizedBox(
                width: 80,
                height: 80,
                child: CustomPaint(
                  painter: _MiniDonut(
                      presence: presence,
                      retard: retard,
                      absence: absence),
                  child: Center(
                    child: Text(
                      '${presence.toInt()}%',
                      style: const TextStyle(
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

          // Status indicator
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
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      decoration: BoxDecoration(
        color: (isGood ? _C.green : _C.orange).withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: (isGood ? _C.green : _C.orange).withValues(alpha: 0.3),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(
              color: isGood ? _C.green : _C.orange,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: (isGood ? _C.green : _C.orange)
                      .withValues(alpha: 0.6),
                  blurRadius: 6,
                )
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            isGood ? 'Assiduité excellente' : 'Assiduité à améliorer',
            style: TextStyle(
              color: isGood ? _C.green : _C.orange,
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
// _StatPillsRow — 3 cartes horizontales (Présences / Retards / Absences)
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
            label: 'Assiduité',
            color: _C.green,
            icon: Icons.check_circle_rounded,
          ),
          const SizedBox(width: 10),
          _StatPill(
            value: '${(stats['retard'] ?? 0).toInt()}%',
            label: 'Retards',
            color: _C.orange,
            icon: Icons.schedule_rounded,
          ),
          const SizedBox(width: 10),
          _StatPill(
            value: '${(stats['absence'] ?? 0).toInt()}%',
            label: 'Absences',
            color: _C.red,
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
  const _StatPill(
      {required this.value,
      required this.label,
      required this.color,
      required this.icon});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
        decoration: BoxDecoration(
          color: _C.card,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 20),
            const SizedBox(height: 6),
            Text(
              value,
              style: TextStyle(
                color: color,
                fontSize: 18,
                fontWeight: FontWeight.w800,
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: const TextStyle(
                color: _C.textSec,
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
// _AttendanceSection — historique avec badge de statut coloré
// ───────────────────────────────────────────────────────────────────────────────
class _AttendanceSection extends StatelessWidget {
  final List<dynamic> history;
  const _AttendanceSection({required this.history});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 16, 16, 0),
      decoration: BoxDecoration(
        color: _C.card,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.white.withValues(alpha: 0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 18, 20, 0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Historique des pointages',
                  style: TextStyle(
                    color: _C.textPri,
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: _C.accent.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${history.length} entrées',
                    style: const TextStyle(
                      color: _C.accentSoft,
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
            const Padding(
              padding: EdgeInsets.fromLTRB(20, 8, 20, 20),
              child: Text(
                'Aucun pointage récent.',
                style: TextStyle(color: _C.textSec, fontSize: 14),
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
        return _C.green;
      case 'RETARD':
        return _C.orange;
      case 'ABSENT':
        return _C.red;
      default:
        return _C.textSec;
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
      padding: const EdgeInsets.fromLTRB(20, 10, 20, 10),
      decoration: BoxDecoration(
        border: isLast
            ? null
            : Border(
                bottom: BorderSide(
                    color: Colors.white.withValues(alpha: 0.05), width: 1)),
      ),
      child: Row(
        children: [
          // Status dot
          Container(
            width: 8,
            height: 8,
            decoration: BoxDecoration(
              color: _statusColor,
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                    color: _statusColor.withValues(alpha: 0.5),
                    blurRadius: 6)
              ],
            ),
          ),
          const SizedBox(width: 14),
          // Date
          Expanded(
            flex: 3,
            child: Text(
              date,
              style: const TextStyle(
                color: _C.textPri,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          // Time
          Text(
            time,
            style: const TextStyle(
              color: _C.textSec,
              fontSize: 13,
            ),
          ),
          const SizedBox(width: 14),
          // Badge statut
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: _statusColor.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              _statusLabel,
              style: TextStyle(
                color: _statusColor,
                fontSize: 11,
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
      {required this.presence,
      required this.retard,
      required this.absence});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width * 0.40;
    final strokeW = size.width * 0.16;
    final rect = Rect.fromCircle(center: center, radius: radius);
    final paint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeW
      ..strokeCap = StrokeCap.round;

    // Background ring
    paint.color = Colors.white.withValues(alpha: 0.08);
    canvas.drawCircle(center, radius, paint);

    double start = -math.pi / 2;
    void drawArc(double pct, Color color) {
      if (pct <= 0) return;
      paint.color = color;
      final sweep = (pct / 100) * 2 * math.pi;
      canvas.drawArc(rect, start, sweep, false, paint);
      start += sweep;
    }

    drawArc(presence, _C.green);
    drawArc(retard, _C.orange);
    drawArc(absence, _C.red);
  }

  @override
  bool shouldRepaint(covariant _MiniDonut old) =>
      old.presence != presence ||
      old.retard != retard ||
      old.absence != absence;
}


// ───────────────────────────────────────────────────────────────────────────────
// Legacy custom icons (kept unchanged for nav bar compatibility)
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
  const BiometricScannerIcon(
      {super.key, required this.color, this.size = 24.0});

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
    // Corners
    final r = w * 0.12;
    final paths = [
      [Offset(r, 0), Offset(0, r), Offset(0, 0)],
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
    // Scan lines
    for (int i = 0; i < 3; i++) {
      final y = h * (0.35 + i * 0.15);
      canvas.drawLine(Offset(w * 0.2, y), Offset(w * 0.8, y), p);
    }

  }

  @override
  bool shouldRepaint(covariant CustomPainter old) => false;
}
