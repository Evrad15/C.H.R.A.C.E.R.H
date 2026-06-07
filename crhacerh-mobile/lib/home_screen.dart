import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/message_screen.dart';
import 'screens/scannqr_screen.dart';

class HomeScreen extends StatefulWidget {
  final Map<String, dynamic> employee;

  const HomeScreen({super.key, required this.employee});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  static const List<IconData> _navIcons = [
    Icons.home,
    Icons.chat_bubble_outline,
    Icons.qr_code_scanner,
    Icons.folder_open,
    Icons.person,
  ];

  static const List<String> _navLabels = [
    'Accueil',
    'Messages',
    'Scan',
    'Docs',
    'Profil',
  ];

  void _onNavTap(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  Future<void> _logout(BuildContext context) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('cached_employee');

    if (context.mounted) {
      Navigator.pushNamedAndRemoveUntil(context, '/matricule', (route) => false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final pages = [
      HomeTab(employee: widget.employee, onLogout: () => _logout(context)),
      const MessageScreen(),
      const ScannQRScreen(),
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
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Color(0xFFF2F2F2),
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(30),
            topRight: Radius.circular(30),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black12,
              blurRadius: 10,
              offset: Offset(0, -2),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _selectedIndex,
          onTap: _onNavTap,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.transparent,
          elevation: 0,
          selectedItemColor: const Color(0xFF171717),
          unselectedItemColor: const Color(0xFF8A8A8A),
          showUnselectedLabels: false,
          showSelectedLabels: false,
          items: List.generate(
            _navIcons.length,
            (index) => BottomNavigationBarItem(
              icon: Icon(_navIcons[index], size: 24),
              label: _navLabels[index],
            ),
          ),
        ),
      ),
    );
  }
}

class HomeTab extends StatelessWidget {
  final Map<String, dynamic> employee;
  final VoidCallback onLogout;

  const HomeTab({super.key, required this.employee, required this.onLogout});

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final dateLabel = '${_twoDigits(now.day)} ${_monthName(now.month)} ${now.year}';
    final timeLabel = '${_twoDigits(now.hour)}:${_twoDigits(now.minute)}';

    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 90),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _HeaderCard(
            name: '${employee['first_name']} ${employee['last_name']}',
            date: dateLabel,
            time: timeLabel,
            onLogout: onLogout,
          ),
          const SizedBox(height: 20),
          const _AttendanceCard(),
          const SizedBox(height: 20),
          const _StatsCard(),
        ],
      ),
    );
  }

  static String _twoDigits(int value) => value.toString().padLeft(2, '0');

  static String _monthName(int month) {
    const frenchMonths = [
      'Janvier',
      'Février',
      'Mars',
      'Avril',
      'Mai',
      'Juin',
      'Juillet',
      'Août',
      'Septembre',
      'Octobre',
      'Novembre',
      'Décembre',
    ];
    return frenchMonths[month - 1];
  }
}

class _HeaderCard extends StatelessWidget {
  final String name;
  final String date;
  final String time;
  final VoidCallback onLogout;

  const _HeaderCard({
    required this.name,
    required this.date,
    required this.time,
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
                    const Text('Bonjour,', style: TextStyle(fontSize: 16, color: Color(0xFF4B4B4B))),
                    const SizedBox(height: 6),
                    Text(name, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(date, style: const TextStyle(fontSize: 14, color: Color(0xFF5F5F5F))),
                  const SizedBox(height: 10),
                  Text(time, style: const TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: Color(0xFFEC1C24))),
                ],
              ),
            ],
          ),
          const SizedBox(height: 24),
          Center(
            child: Opacity(
              opacity: 0.15,
              child: Text(
                'C.H.R.A.C.E.R.H',
                style: TextStyle(fontSize: 34, fontWeight: FontWeight.bold, color: Colors.blueGrey[900]),
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
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _AttendanceCard extends StatelessWidget {
  const _AttendanceCard();

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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: const [
              _DataHeader(label: 'Date'),
              _DataHeader(label: 'Heure'),
              _DataHeader(label: 'Status'),
            ],
          ),
          const SizedBox(height: 12),
          _AttendanceRow(
            date: '06/06/2026',
            time: '10:50',
            status: 'Arrivée',
            dotColor: Colors.red,
          ),
          const SizedBox(height: 10),
          _AttendanceRow(
            date: '06/06/2026',
            time: '18:25',
            status: 'Départ',
            dotColor: Colors.transparent,
          ),
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
            child: Text(time, style: const TextStyle(fontSize: 15), textAlign: TextAlign.center),
          ),
          Expanded(
            flex: 3,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Text(status, style: const TextStyle(fontSize: 15)),
                if (dotColor != Colors.transparent) ...[
                  const SizedBox(width: 8),
                  Container(
                    width: 10,
                    height: 10,
                    decoration: BoxDecoration(color: dotColor, shape: BoxShape.circle),
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
  const _StatsCard();

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
                painter: _DonutChartPainter(),
                child: const Center(
                  child: CircleAvatar(
                    radius: 30,
                    backgroundColor: Colors.white,
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
              children: const [
                _StatLabel(color: Color(0xFF47D534), label: '50 % d’assiduité'),
                SizedBox(height: 12),
                _StatLabel(color: Color(0xFFEF7E0D), label: '35 % de retard'),
                SizedBox(height: 12),
                _StatLabel(color: Color(0xFFE01010), label: '15 % d’absences'),
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
        Container(width: 10, height: 10, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: color),
          ),
        ),
      ],
    );
  }
}

class _DonutChartPainter extends CustomPainter {
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

    paint.color = const Color(0xFF47D534);
    canvas.drawArc(rect, -1.5708, 3.1416, false, paint);

    paint.color = const Color(0xFFEF7E0D);
    canvas.drawArc(rect, 1.5708, 2.1991, false, paint);

    paint.color = const Color(0xFFE01010);
    canvas.drawArc(rect, 3.7699, 0.9425, false, paint);
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
        style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.black54),
      ),
    );
  }
}
