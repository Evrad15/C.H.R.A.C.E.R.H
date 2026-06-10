import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/message_screen.dart';
import 'screens/scannqr_screen.dart';
import 'services/auth_service.dart';

class HomeScreen extends StatefulWidget {
  final Map<String, dynamic> employee;

  const HomeScreen({super.key, required this.employee});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  Widget _buildNavIcon(int index, bool isSelected) {
    final color = isSelected ? Colors.white : const Color(0xFF171717);
    switch (index) {
      case 0:
        return Icon(
          Icons.home_rounded,
          color: color,
          size: 26,
        );
      case 1:
        return CustomChatBubbleIcon(
          color: color,
          dotColor: isSelected ? const Color(0xFF171717) : Colors.white,
          size: 24,
        );
      case 2:
        return BiometricScannerIcon(
          color: color,
          size: 24,
        );
      case 3:
        return Icon(
          Icons.folder_rounded,
          color: color,
          size: 26,
        );
      case 4:
        return Icon(
          Icons.person_rounded,
          color: color,
          size: 26,
        );
      default:
        return Icon(
          Icons.help_outline,
          color: color,
          size: 26,
        );
    }
  }

  void _onNavTap(int index) {
    setState(() {
      _selectedIndex = index;
    });
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
          name:
              '${widget.employee['first_name']} ${widget.employee['last_name']}',
          onLogout: () => _logout(context)),
      MessageScreen(employeeId: widget.employee['id']),
      ScannQRScreen(employeeId: widget.employee['id']),
      const _PlaceholderScreen(title: 'Documents'),
      const _PlaceholderScreen(title: 'Profil'),
    ];

    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      body: SafeArea(
        child: IndexedStack(
          index: _selectedIndex,
          children: pages,
        ),
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        child: Container(
          margin: const EdgeInsets.fromLTRB(24, 8, 24, 16),
          height: 70,
          decoration: BoxDecoration(
            color: const Color(0xFFF2F2F2),
            borderRadius: BorderRadius.circular(35),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: List.generate(5, (index) {
              final isSelected = _selectedIndex == index;
              return _NavBarItem(
                isSelected: isSelected,
                onTap: () => _onNavTap(index),
                child: _buildNavIcon(index, isSelected),
              );
            }),
          ),
        ),
      ),
    );
  }
}

class HomeTab extends StatefulWidget {
  final int employeeId;
  final String name;
  final VoidCallback onLogout;

  const HomeTab(
      {super.key,
      required this.employeeId,
      required this.name,
      required this.onLogout});

  @override
  State<HomeTab> createState() => _HomeTabState();
}

class _HomeTabState extends State<HomeTab> {
  final AuthService _authService = AuthService();
  bool _isLoading = true;
  List<dynamic> _history = [];
  Map<String, dynamic> _stats = {
    'presence': 0.0,
    'retard': 0.0,
    'absence': 0.0
  };

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final result = await _authService.fetchDashboardData(widget.employeeId);
    if (mounted) {
      if (result['success']) {
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
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'])),
        );
      }
    }
  }

  Color _getClockColor() {
    final aPointeAujourdHui = _history.any((p) =>
        p['status'] == 'PRESENT' || p['status'] == 'RETARD');

    if (aPointeAujourdHui) {
      return const Color(0xFF1A1A1A); // ⚫ Noir après le premier pointage
    }

    final maintenant = DateTime.now();
    
    if (maintenant.hour > 8 || (maintenant.hour == 8 && maintenant.minute > 15)) {
      return const Color(0xFFEC1C24); // 🔴 Rouge si l'heure dépasse 08:15
    }

    return const Color(0xFF47D534); // 🟢 Vert par défaut
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final dateLabel =
        '${_twoDigits(now.day)} ${_monthName(now.month)} ${now.year}';
    final timeLabel = '${_twoDigits(now.hour)}:${_twoDigits(now.minute)}';

    return RefreshIndicator(
      onRefresh: _loadData,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _HeaderCard(
              name: widget.name,
              date: dateLabel,
              time: timeLabel,
              clockColor: _getClockColor(),
              onLogout: widget.onLogout,
            ),
            const SizedBox(height: 20),
            if (_isLoading)
              const Center(
                  child: Padding(
                      padding: EdgeInsets.all(20),
                      child: CircularProgressIndicator()))
            else ...[
              _AttendanceCard(history: _history),
              const SizedBox(height: 20),
              _StatsCard(stats: _stats),
            ],
          ],
        ),
      ),
    );
  }

  static String _twoDigits(int value) => value.toString().padLeft(2, '0');

  static String _monthName(int month) {
    const frenchMonths = [
      'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
      'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
    ];
    return frenchMonths[month - 1];
  }
}

class _HeaderCard extends StatelessWidget {
  final String name;
  final String date;
  final String time;
  final Color clockColor;
  final VoidCallback onLogout;

  const _HeaderCard({
    required this.name,
    required this.date,
    required this.time,
    required this.clockColor,
    required this.onLogout,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Bonjour,',
                        style:
                            TextStyle(fontSize: 16, color: Color(0xFF4B4B4B))),
                    const SizedBox(height: 6),
                    Text(name,
                        style: const TextStyle(
                            fontSize: 24, fontWeight: FontWeight.w700)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(date,
                      style: const TextStyle(
                          fontSize: 14, color: Color(0xFF5F5F5F))),
                  const SizedBox(height: 10),
                  Text(time,
                      style: TextStyle(
                          fontSize: 48,
                          fontWeight: FontWeight.bold,
                          color: clockColor)),
                ],
              ),
            ],
          ),
          const SizedBox(height: 24),
          Center(
            child: Opacity(
              opacity: 0.15,
              child: Image.asset(
                'assets/images/chracerh logo.png',
                height: 150,
                fit: BoxFit.contain,
              ),
            ),
          ),
          const SizedBox(height: 16),
          Align(
            alignment: Alignment.centerRight,
            child: ElevatedButton.icon(
              onPressed: onLogout,
              icon: const Icon(Icons.logout, size: 18),
              label: const Text('Déconnexion'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1976D2),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _AttendanceCard extends StatelessWidget {
  final List<dynamic> history;

  const _AttendanceCard({required this.history});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFF6F4F4),
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 24,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _DataHeader(label: 'Date'),
              _DataHeader(label: 'Heure'),
              _DataHeader(label: 'Status'),
            ],
          ),
          const SizedBox(height: 12),
          if (history.isEmpty)
            const Center(
                child: Padding(
                    padding: EdgeInsets.all(8.0),
                    child: Text('Aucun pointage récent.')))
          else
            ...history.map((item) => Padding(
                  padding: const EdgeInsets.only(bottom: 10),
                  child: _AttendanceRow(
                    date: item['date'],
                    time: item['time'],
                    status: item['status'],
                    dotColor: item['is_late'] == true
                        ? Colors.red
                        : Colors.transparent,
                  ),
                )),
        ],
      ),
    );
  }
}

class _DataHeader extends StatelessWidget {
  final String label;

  const _DataHeader({required this.label});

  @override
  Widget build(BuildContext context) {
    return Text(
      label,
      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
    );
  }
}

class _AttendanceRow extends StatelessWidget {
  final String date;
  final String time;
  final String status;
  final Color dotColor;

  const _AttendanceRow({
    required this.date,
    required this.time,
    required this.status,
    required this.dotColor,
  });

  Color _getStatusTextColor(String statusStr) {
    switch (statusStr.toUpperCase()) {
      case 'PRESENT':
        return const Color(0xFF47D534);
      case 'RETARD':
      case 'ABSENT':
        return const Color(0xFFE01010);
      default:
        return Colors.black87;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE6E6E6)),
      ),
      child: Row(
        children: [
          Expanded(
            flex: 3,
            child: Text(date, style: const TextStyle(fontSize: 15)),
          ),
          Expanded(
            flex: 2,
            child: Text(time,
                style: const TextStyle(fontSize: 15),
                textAlign: TextAlign.center),
          ),
          Expanded(
            flex: 3,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Text(
                  status,
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: _getStatusTextColor(status),
                  ),
                ),
                if (dotColor != Colors.transparent) ...[
                  const SizedBox(width: 8),
                  Container(
                    width: 10,
                    height: 10,
                    decoration:
                        BoxDecoration(color: dotColor, shape: BoxShape.circle),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _StatsCard extends StatelessWidget {
  final Map<String, dynamic> stats;

  const _StatsCard({required this.stats});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFF6F4F4),
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 24,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: SizedBox(
              height: 180,
              child: CustomPaint(
                painter: _DonutChartPainter(
                  presence: stats['presence'] ?? 0.0,
                  retard: stats['retard'] ?? 0.0,
                  absence: stats['absence'] ?? 0.0,
                ),
                child: Center(
                  child: CircleAvatar(
                    radius: 30,
                    backgroundColor: Colors.white,
                    child: Text('${(stats['presence'] ?? 0).toInt()}%',
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(width: 18),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _StatLabel(
                    color: const Color(0xFF47D534),
                    label: '${(stats['presence'] ?? 0).toInt()}% d’assiduité'),
                const SizedBox(height: 12),
                _StatLabel(
                    color: const Color(0xFFEF7E0D),
                    label: '${(stats['retard'] ?? 0).toInt()}% de retard'),
                const SizedBox(height: 12),
                _StatLabel(
                    color: const Color(0xFFE01010),
                    label: '${(stats['absence'] ?? 0).toInt()}% d’absences'),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _StatLabel extends StatelessWidget {
  final Color color;
  final String label;

  const _StatLabel({required this.color, required this.label});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
            width: 10,
            height: 10,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: TextStyle(
                fontSize: 15, fontWeight: FontWeight.w600, color: color),
          ),
        ),
      ],
    );
  }
}

class _DonutChartPainter extends CustomPainter {
  final double presence;
  final double retard;
  final double absence;

  _DonutChartPainter(
      {required this.presence, required this.retard, required this.absence});

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width * 0.38;
    final strokeWidth = size.width * 0.24;
    final rect = Rect.fromCircle(center: center, radius: radius);

    final paint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = strokeWidth
      ..strokeCap = StrokeCap.butt;

    double startAngle = -1.5708;

    paint.color = const Color(0xFF47D534);
    double sweepPresence = (presence / 100) * 6.2832;
    canvas.drawArc(rect, startAngle, sweepPresence, false, paint);
    startAngle += sweepPresence;

    paint.color = const Color(0xFFEF7E0D);
    double sweepRetard = (retard / 100) * 6.2832;
    canvas.drawArc(rect, startAngle, sweepRetard, false, paint);
    startAngle += sweepRetard;

    paint.color = const Color(0xFFE01010);
    double sweepAbsence = (absence / 100) * 6.2832;
    canvas.drawArc(rect, startAngle, sweepAbsence, false, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _PlaceholderScreen extends StatelessWidget {
  final String title;

  const _PlaceholderScreen({required this.title});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Text(
        title,
        style: const TextStyle(
            fontSize: 22, fontWeight: FontWeight.bold, color: Colors.black54),
      ),
    );
  }
}

class _NavBarItem extends StatefulWidget {
  final Widget child;
  final VoidCallback onTap;
  final bool isSelected;

  const _NavBarItem({
    required this.child,
    required this.onTap,
    required this.isSelected,
  });

  @override
  State<_NavBarItem> createState() => _NavBarItemState();
}

class _NavBarItemState extends State<_NavBarItem> {
  double _scale = 1.0;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => setState(() => _scale = 0.9),
      onTapUp: (_) {
        setState(() => _scale = 1.0);
        widget.onTap();
      },
      onTapCancel: () => setState(() => _scale = 1.0),
      child: AnimatedScale(
        scale: _scale,
        duration: const Duration(milliseconds: 100),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          curve: Curves.easeInOut,
          width: 64,
          height: 44,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: widget.isSelected
                ? const Color(0xFF171717)
                : Colors.transparent,
            borderRadius: BorderRadius.circular(22),
          ),
          child: widget.child,
        ),
      ),
    );
  }
}

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

    final bubbleRect = Rect.fromLTWH(0, 0, w, h * 0.85);
    final rrect =
        RRect.fromRectAndRadius(bubbleRect, Radius.circular(w * 0.45));

    final path = Path()..addRRect(rrect);

    final tailPath = Path()
      ..moveTo(w * 0.22, h * 0.7)
      ..lineTo(w * 0.08, h * 0.95)
      ..quadraticBezierTo(w * 0.16, h * 0.92, w * 0.35, h * 0.78)
      ..close();
    path.addPath(tailPath, Offset.zero);

    canvas.drawPath(path, paint);

    final dotPaint = Paint()
      ..color = dotColor
      ..style = PaintingStyle.fill;

    final centerY = h * 0.425;
    final dotRadius = w * 0.055;
    final spacing = w * 0.16;

    canvas.drawCircle(Offset(w / 2 - spacing, centerY), dotRadius, dotPaint);
    canvas.drawCircle(Offset(w / 2, centerY), dotRadius, dotPaint);
    canvas.drawCircle(Offset(w / 2 + spacing, centerY), dotRadius, dotPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class BiometricScannerIcon extends StatelessWidget {
  final Color color;
  final double size;

  const BiometricScannerIcon({
    super.key,
    required this.color,
    this.size = 24.0,
  });

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
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.2
      ..strokeCap = StrokeCap.round;

    final w = size.width;
    final h = size.height;

    final len = w * 0.22;
    final rad = w * 0.15;

    Path pathTL = Path()
      ..moveTo(0, len)
      ..lineTo(0, rad)
      ..quadraticBezierTo(0, 0, rad, 0)
      ..lineTo(len, 0);
    canvas.drawPath(pathTL, paint);

    Path pathTR = Path()
      ..moveTo(w - len, 0)
      ..lineTo(w - rad, 0)
      ..quadraticBezierTo(w, 0, w, rad)
      ..lineTo(w, len);
    canvas.drawPath(pathTR, paint);

    Path pathBL = Path()
      ..moveTo(0, h - len)
      ..lineTo(0, h - rad)
      ..quadraticBezierTo(0, h, rad, h)
      ..lineTo(len, h);
    canvas.drawPath(pathBL, paint);

    Path pathBR = Path()
      ..moveTo(w - len, h)
      ..lineTo(w - rad, h)
      ..quadraticBezierTo(w, h, w, h - rad)
      ..lineTo(w, h - len);
    canvas.drawPath(pathBR, paint);

    const pi = 3.141592653589793;

    final topRect = Rect.fromCenter(
      center: Offset(w / 2, h / 2 - 4),
      width: w * 0.38,
      height: h * 0.25,
    );
    canvas.drawArc(topRect, pi, pi, false, paint);

    canvas.drawLine(
      Offset(w * 0.22, h / 2),
      Offset(w * 0.78, h / 2),
      paint,
    );

    final bottomRect = Rect.fromCenter(
      center: Offset(w / 2, h / 2 + 4),
      width: w * 0.38,
      height: h * 0.25,
    );
    canvas.drawArc(bottomRect, 0, pi, false, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}