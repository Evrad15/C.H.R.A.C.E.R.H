import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:permission_handler/permission_handler.dart';
import '../services/chat_service.dart';

class ScannQRScreen extends StatefulWidget {
  final int employeeId;
  const ScannQRScreen({super.key, required this.employeeId});

  @override
  State<ScannQRScreen> createState() => _ScannQRScreenState();
}

class _ScannQRScreenState extends State<ScannQRScreen> with WidgetsBindingObserver {
  final ChatService _chatService = ChatService();
  final MobileScannerController _scannerController = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
    torchEnabled: false,
  );

  bool _cameraReady = false;
  bool _permissionDenied = false;
  bool _isScanActive = true;
  bool _torchOn = false;
  bool _isSubmitting = false;

  // Résultat du dernier pointage
  Map<String, dynamic>? _lastScanResult;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _requestCameraPermission();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _scannerController.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (!_cameraReady) return;
    if (state == AppLifecycleState.resumed) {
      _scannerController.start();
    } else if (state == AppLifecycleState.inactive || state == AppLifecycleState.paused) {
      _scannerController.stop();
    }
  }

  Future<void> _requestCameraPermission() async {
    final status = await Permission.camera.request();
    if (mounted) {
      setState(() {
        _cameraReady = status.isGranted;
        _permissionDenied = status.isPermanentlyDenied || status.isDenied;
      });
    }
  }

  void _toggleTorch() {
    _scannerController.toggleTorch();
    setState(() => _torchOn = !_torchOn);
  }

  void _onQRDetected(BarcodeCapture capture) {
    if (!_isScanActive || _isSubmitting) return;
    final barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;

    final raw = barcodes.first.rawValue;
    if (raw == null || raw.isEmpty) return;

    // Pause le scan pendant le dialogue
    setState(() => _isScanActive = false);
    _scannerController.stop();

    _showPointageDialog(raw);
  }

  void _showPointageDialog(String qrValue) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => _PointageDialog(
        qrValue: qrValue,
        employeeId: widget.employeeId,
        chatService: _chatService,
        onConfirm: (type) async {
          Navigator.of(ctx).pop();
          await _submitPointage(type);
        },
        onCancel: () {
          Navigator.of(ctx).pop();
          _resumeScan();
        },
      ),
    );
  }

  Future<void> _submitPointage(String type) async {
    setState(() => _isSubmitting = true);

    final result = await _chatService.submitQRScan(
      employeeId: widget.employeeId,
      type: type,
    );

    if (mounted) {
      setState(() {
        _isSubmitting = false;
        _lastScanResult = result;
      });

      if (result['success']) {
        _showSuccessAnimation(result);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message'] ?? 'Erreur lors du pointage.'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }

    // Dans tous les cas, reprendre le scan après 3 secondes
    Future.delayed(const Duration(seconds: 3), () {
      if (mounted) _resumeScan();
    });
  }

  void _resumeScan() {
    setState(() => _isScanActive = true);
    _scannerController.start();
  }

  void _showSuccessAnimation(Map<String, dynamic> result) {
    final data = result['data'] as Map<String, dynamic>?;
    final type = data?['type'] ?? '';
    final time = data?['time'] ?? '';
    final employee = data?['employee'] ?? '';

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => _SuccessDialog(
        employeeName: employee,
        type: type,
        time: time,
      ),
    );
    Future.delayed(const Duration(seconds: 3), () {
      if (mounted && Navigator.canPop(context)) Navigator.of(context).pop();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // ── Caméra ──────────────────────────────────────────────
          if (_cameraReady)
            MobileScanner(
              controller: _scannerController,
              onDetect: _onQRDetected,
            )
          else if (_permissionDenied)
            _PermissionDeniedView(onRetry: _requestCameraPermission)
          else
            const Center(child: CircularProgressIndicator(color: Colors.white)),

          // ── Overlay ─────────────────────────────────────────────
          if (_cameraReady) ...[
            // Obscurcissement autour du cadre
            _ScannerOverlay(isScanActive: _isScanActive),

            // Barre supérieure
            SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Titre
                    const Text(
                      'Scanner QR',
                      style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                    ),
                    // Torche
                    GestureDetector(
                      onTap: _toggleTorch,
                      child: Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: _torchOn ? Colors.white : Colors.white24,
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          _torchOn ? Icons.flash_on_rounded : Icons.flash_off_rounded,
                          color: _torchOn ? Colors.black : Colors.white,
                          size: 22,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Indicateur de chargement au centre
            if (_isSubmitting)
              const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(color: Colors.white, strokeWidth: 3),
                    SizedBox(height: 16),
                    Text('Enregistrement du pointage…', style: TextStyle(color: Colors.white, fontSize: 15)),
                  ],
                ),
              ),

            // ── Panneau bas ────────────────────────────────────────
            Positioned(
              bottom: 0, left: 0, right: 0,
              child: SafeArea(
                top: false,
                child: Container(
                  margin: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Dernier résultat
                      if (_lastScanResult != null)
                        _LastScanCard(result: _lastScanResult!),
                      const SizedBox(height: 12),
                      // Instruction
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.white24),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              _isScanActive ? Icons.qr_code_scanner : Icons.pause_circle_outline,
                              color: Colors.white, size: 20,
                            ),
                            const SizedBox(width: 10),
                            Text(
                              _isScanActive ? 'Placez le QR code dans le cadre' : 'Scan en pause…',
                              style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w500),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────────────────
// Overlay du scanner avec cadre de visée animé
// ──────────────────────────────────────────────────────────────────────────
class _ScannerOverlay extends StatefulWidget {
  final bool isScanActive;
  const _ScannerOverlay({required this.isScanActive});

  @override
  State<_ScannerOverlay> createState() => _ScannerOverlayState();
}

class _ScannerOverlayState extends State<_ScannerOverlay> with SingleTickerProviderStateMixin {
  late AnimationController _lineController;
  late Animation<double> _lineAnim;

  @override
  void initState() {
    super.initState();
    _lineController = AnimationController(vsync: this, duration: const Duration(seconds: 2))
      ..repeat(reverse: true);
    _lineAnim = Tween<double>(begin: 0, end: 1).animate(CurvedAnimation(parent: _lineController, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _lineController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final frameSize = size.width * 0.72;
    final frameTop = (size.height - frameSize) / 2 - 40;

    return Stack(
      children: [
        // Assombrissement autour du cadre
        CustomPaint(
          size: Size(size.width, size.height),
          painter: _OverlayPainter(
            frameRect: Rect.fromLTWH((size.width - frameSize) / 2, frameTop, frameSize, frameSize),
          ),
        ),
        // Ligne de scan animée
        if (widget.isScanActive)
          Positioned(
            top: frameTop,
            left: (size.width - frameSize) / 2,
            width: frameSize,
            height: frameSize,
            child: AnimatedBuilder(
              animation: _lineAnim,
              builder: (_, __) => CustomPaint(
                painter: _ScanLinePainter(progress: _lineAnim.value),
              ),
            ),
          ),
        // Coins du cadre
        Positioned(
          top: frameTop,
          left: (size.width - frameSize) / 2,
          width: frameSize,
          height: frameSize,
          child: CustomPaint(painter: _FrameCornersPainter()),
        ),
      ],
    );
  }
}

class _OverlayPainter extends CustomPainter {
  final Rect frameRect;
  _OverlayPainter({required this.frameRect});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = Colors.black.withValues(alpha: 0.62);
    final fullRect = Rect.fromLTWH(0, 0, size.width, size.height);
    final path = Path()
      ..addRect(fullRect)
      ..addRRect(RRect.fromRectAndRadius(frameRect, const Radius.circular(20)))
      ..fillType = PathFillType.evenOdd;
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _FrameCornersPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.stroke
      ..strokeWidth = 4.0
      ..strokeCap = StrokeCap.round;

    const len = 28.0;
    const rad = 16.0;
    final w = size.width;
    final h = size.height;

    // Coin TL
    canvas.drawPath(Path()..moveTo(0, len)..lineTo(0, rad)..quadraticBezierTo(0, 0, rad, 0)..lineTo(len, 0), paint);
    // Coin TR
    canvas.drawPath(Path()..moveTo(w - len, 0)..lineTo(w - rad, 0)..quadraticBezierTo(w, 0, w, rad)..lineTo(w, len), paint);
    // Coin BL
    canvas.drawPath(Path()..moveTo(0, h - len)..lineTo(0, h - rad)..quadraticBezierTo(0, h, rad, h)..lineTo(len, h), paint);
    // Coin BR
    canvas.drawPath(Path()..moveTo(w - len, h)..lineTo(w - rad, h)..quadraticBezierTo(w, h, w, h - rad)..lineTo(w, h - len), paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _ScanLinePainter extends CustomPainter {
  final double progress;
  _ScanLinePainter({required this.progress});

  @override
  void paint(Canvas canvas, Size size) {
    final y = size.height * progress;
    final paint = Paint()
      ..shader = LinearGradient(
        colors: [Colors.transparent, const Color(0xFF00C853).withValues(alpha: 0.9), Colors.transparent],
      ).createShader(Rect.fromLTWH(0, y - 1, size.width, 3))
      ..strokeWidth = 3
      ..style = PaintingStyle.stroke;
    canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
  }

  @override
  bool shouldRepaint(covariant _ScanLinePainter old) => old.progress != progress;
}

// ──────────────────────────────────────────────────────────────────────────
// Dialogue de choix ENTRÉE / SORTIE
// ──────────────────────────────────────────────────────────────────────────
class _PointageDialog extends StatelessWidget {
  final String qrValue;
  final int employeeId;
  final ChatService chatService;
  final Function(String type) onConfirm;
  final VoidCallback onCancel;

  const _PointageDialog({
    required this.qrValue,
    required this.employeeId,
    required this.chatService,
    required this.onConfirm,
    required this.onCancel,
  });

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
      backgroundColor: Colors.white,
      contentPadding: EdgeInsets.zero,
      content: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72, height: 72,
              decoration: BoxDecoration(color: const Color(0xFFF0F4FF), shape: BoxShape.circle),
              child: const Icon(Icons.qr_code_rounded, size: 38, color: Color(0xFF1976D2)),
            ),
            const SizedBox(height: 20),
            const Text('QR Code détecté', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            const Text(
              'Choisissez le type de pointage à enregistrer.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 14, color: Color(0xFF5F5F5F), height: 1.5),
            ),
            const SizedBox(height: 28),
            Row(
              children: [
                Expanded(
                  child: _TypeButton(
                    label: 'Entrée', icon: Icons.login_rounded,
                    color: const Color(0xFF43A047), bgColor: const Color(0xFFF1F9F1),
                    onTap: () => onConfirm('ENTREE'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _TypeButton(
                    label: 'Sortie', icon: Icons.logout_rounded,
                    color: const Color(0xFFE53935), bgColor: const Color(0xFFFFF3F3),
                    onTap: () => onConfirm('SORTIE'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextButton(
              onPressed: onCancel,
              child: const Text('Annuler', style: TextStyle(color: Color(0xFF8A8A8A))),
            ),
          ],
        ),
      ),
    );
  }
}

class _TypeButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color color;
  final Color bgColor;
  final VoidCallback onTap;

  const _TypeButton({
    required this.label, required this.icon, required this.color,
    required this.bgColor, required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 18),
        decoration: BoxDecoration(color: bgColor, borderRadius: BorderRadius.circular(18)),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(label, style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────────────────
// Dialogue de succès après pointage
// ──────────────────────────────────────────────────────────────────────────
class _SuccessDialog extends StatelessWidget {
  final String employeeName;
  final String type;
  final String time;

  const _SuccessDialog({required this.employeeName, required this.type, required this.time});

  @override
  Widget build(BuildContext context) {
    final isEntree = type == 'ENTREE';
    final color = isEntree ? const Color(0xFF43A047) : const Color(0xFFE53935);
    final icon = isEntree ? Icons.login_rounded : Icons.logout_rounded;
    final label = isEntree ? 'Entrée enregistrée' : 'Sortie enregistrée';

    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
      backgroundColor: Colors.white,
      contentPadding: const EdgeInsets.all(32),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 80, height: 80,
            decoration: BoxDecoration(color: color.withValues(alpha: 0.1), shape: BoxShape.circle),
            child: Icon(icon, color: color, size: 44),
          ),
          const SizedBox(height: 20),
          Text(label, style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 8),
          Text('Bonjour $employeeName !', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          if (time.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text('Pointage à $time', style: const TextStyle(fontSize: 14, color: Color(0xFF5F5F5F))),
          ],
          const SizedBox(height: 20),
          const Text('Le scan se reprend automatiquement…', style: TextStyle(fontSize: 12, color: Color(0xFFAAAAAA))),
        ],
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────────────────
// Carte du dernier pointage
// ──────────────────────────────────────────────────────────────────────────
class _LastScanCard extends StatelessWidget {
  final Map<String, dynamic> result;
  const _LastScanCard({required this.result});

  @override
  Widget build(BuildContext context) {
    final data = result['data'] as Map<String, dynamic>?;
    if (!result['success'] || data == null) return const SizedBox.shrink();

    final type = data['type'] as String? ?? '';
    final time = data['time'] as String? ?? '';
    final name = data['employee'] as String? ?? '';
    final isEntree = type == 'ENTREE';
    final color = isEntree ? const Color(0xFF43A047) : const Color(0xFFE53935);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: color.withValues(alpha: 0.5)),
      ),
      child: Row(
        children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(color: color.withValues(alpha: 0.2), shape: BoxShape.circle),
            child: Icon(isEntree ? Icons.login_rounded : Icons.logout_rounded, color: color, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Dernier pointage : $type', style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 13)),
                Text('$name — $time', style: const TextStyle(color: Colors.white70, fontSize: 12)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────────────────
// Vue permission refusée
// ──────────────────────────────────────────────────────────────────────────
class _PermissionDeniedView extends StatelessWidget {
  final VoidCallback onRetry;
  const _PermissionDeniedView({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(36),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.no_photography_rounded, color: Colors.white54, size: 72),
            const SizedBox(height: 24),
            const Text('Caméra inaccessible', style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            const Text(
              'Autorisez l\'accès à la caméra dans les paramètres pour utiliser le scanner QR.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.white70, fontSize: 14, height: 1.5),
            ),
            const SizedBox(height: 32),
            ElevatedButton.icon(
              onPressed: () async {
                await openAppSettings();
                onRetry();
              },
              icon: const Icon(Icons.settings_rounded),
              label: const Text('Ouvrir les paramètres'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: Colors.black87,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
