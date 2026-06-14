import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/chat_service.dart';

// ═══════════════════════════════════════════════════════════════════════════════
// Design Tokens (Matching home/message dark theme)
// ═══════════════════════════════════════════════════════════════════════════════
import '../theme_manager.dart';

class ScannQRScreen extends StatefulWidget {
  final int employeeId;
  const ScannQRScreen({super.key, required this.employeeId});

  @override
  State<ScannQRScreen> createState() => _ScannQRScreenState();
}

class _ScannQRScreenState extends State<ScannQRScreen>
    with WidgetsBindingObserver {
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
    } else if (state == AppLifecycleState.inactive ||
        state == AppLifecycleState.paused) {
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

    setState(() => _isScanActive = false);
    _scannerController.stop();

    _showPointageDialog(raw);
  }

  void _showPointageDialog(String qrValue) {
    showGeneralDialog(
      context: context,
      barrierDismissible: false,
      barrierLabel: 'Pointage',
      barrierColor: Colors.black87,
      transitionDuration: const Duration(milliseconds: 250),
      pageBuilder: (ctx, anim1, anim2) {
        return _PointageDialog(
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
        );
      },
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
            content: Text(
              result['message'] ?? 'Erreur lors du pointage.',
              style: GoogleFonts.inter(fontWeight: FontWeight.w500),
            ),
            backgroundColor: AppColors.red,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
      }
    }

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

    showGeneralDialog(
      context: context,
      barrierDismissible: false,
      barrierColor: Colors.black87,
      transitionDuration: const Duration(milliseconds: 250),
      pageBuilder: (_, __, ___) => _SuccessDialog(
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
      backgroundColor: AppColors.bg,
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
            Center(child: CircularProgressIndicator(color: AppColors.accent)),

          // ── Overlay ─────────────────────────────────────────────
          if (_cameraReady) ...[
            _ScannerOverlay(isScanActive: _isScanActive),

            // Barre supérieure HUD
            Positioned(
              top: MediaQuery.of(context).padding.top + 16,
              left: 20,
              right: 20,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'SCANNER MODE',
                        style: GoogleFonts.outfit(
                          color: AppColors.textPri,
                          fontSize: 22,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 1.0,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          Container(
                            width: 6,
                            height: 6,
                            decoration: BoxDecoration(
                              color: AppColors.green,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            'SYSTEM ACTIVE',
                            style: GoogleFonts.outfit(
                              color: AppColors.green,
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 1.0,
                            ),
                          ),
                        ],
                      )
                    ],
                  ),
                  // Bouton Torche avec look néon
                  GestureDetector(
                    onTap: _toggleTorch,
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: _torchOn ? AppColors.accent : Colors.black.withOpacity(0.5),
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: _torchOn ? AppColors.accentSoft : Colors.white24,
                          width: 1.5,
                        ),
                        boxShadow: _torchOn
                            ? [BoxShadow(color: AppColors.accent.withOpacity(0.5), blurRadius: 10)]
                            : [],
                      ),
                      child: Icon(
                        _torchOn ? Icons.flash_on_rounded : Icons.flash_off_rounded,
                        color: Colors.white,
                        size: 20,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Indicateur HUD central (chargement)
            if (_isSubmitting)
              Container(
                color: Colors.black87,
                child: Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      CircularProgressIndicator(color: AppColors.accent, strokeWidth: 3),
                      const SizedBox(height: 24),
                      Text(
                        'ENREGISTREMENT DU POINTAGE…',
                        style: GoogleFonts.outfit(
                          color: Colors.white,
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 1.2,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

            // Panneau HUD bas (décalé du bottom nav)
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: SafeArea(
                top: false,
                child: Container(
                  margin: const EdgeInsets.fromLTRB(20, 0, 20, 110),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (_lastScanResult != null)
                        _LastScanCard(result: _lastScanResult!),
                      const SizedBox(height: 16),
                      // Instruction Banner
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.6),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.white.withOpacity(0.08), width: 1),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              _isScanActive ? Icons.qr_code_scanner_rounded : Icons.pause_circle_outline_rounded,
                              color: _isScanActive ? AppColors.accentSoft : AppColors.textSec,
                              size: 20,
                            ),
                            const SizedBox(width: 12),
                            Text(
                              _isScanActive ? 'Placer le QR code dans le cadre' : 'Scan suspendu…',
                              style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                              ),
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

// ═══════════════════════════════════════════════════════════════════════════════
// Scanner Overlay ( Futuristic sci-fi UI )
// ═══════════════════════════════════════════════════════════════════════════════
class _ScannerOverlay extends StatefulWidget {
  final bool isScanActive;
  const _ScannerOverlay({required this.isScanActive});

  @override
  State<_ScannerOverlay> createState() => _ScannerOverlayState();
}

class _ScannerOverlayState extends State<_ScannerOverlay>
    with SingleTickerProviderStateMixin {
  late AnimationController _lineController;
  late Animation<double> _lineAnim;

  @override
  void initState() {
    super.initState();
    _lineController =
        AnimationController(vsync: this, duration: const Duration(seconds: 2))
          ..repeat(reverse: true);
    _lineAnim = Tween<double>(begin: 0, end: 1).animate(
        CurvedAnimation(parent: _lineController, curve: Curves.easeInOut));
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
        // Assombrissement HUD autour du cadre
        CustomPaint(
          size: Size(size.width, size.height),
          painter: _OverlayPainter(
            frameRect: Rect.fromLTWH(
                (size.width - frameSize) / 2, frameTop, frameSize, frameSize),
          ),
        ),
        // Ligne de scan animée néon violet/vert
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
        // Coins du cadre néon
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
    final paint = Paint()..color = Colors.black.withOpacity(0.75);
    final fullRect = Rect.fromLTWH(0, 0, size.width, size.height);
    final path = Path()
      ..addRect(fullRect)
      ..addRRect(RRect.fromRectAndRadius(frameRect, const Radius.circular(24)))
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
      ..color = AppColors.accent
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.5
      ..strokeCap = StrokeCap.round;

    final glowPaint = Paint()
      ..color = AppColors.accent.withOpacity(0.3)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 8.0
      ..strokeCap = StrokeCap.round;

    const len = 28.0;
    const rad = 20.0;
    final w = size.width;
    final h = size.height;

    final paths = [
      // Coin TL
      Path()..moveTo(0, len)..lineTo(0, rad)..quadraticBezierTo(0, 0, rad, 0)..lineTo(len, 0),
      // Coin TR
      Path()..moveTo(w - len, 0)..lineTo(w - rad, 0)..quadraticBezierTo(w, 0, w, rad)..lineTo(w, len),
      // Coin BL
      Path()..moveTo(0, h - len)..lineTo(0, h - rad)..quadraticBezierTo(0, h, rad, h)..lineTo(len, h),
      // Coin BR
      Path()..moveTo(w - len, h)..lineTo(w - rad, h)..quadraticBezierTo(w, h, w, h - rad)..lineTo(w, h - len),
    ];

    for (final p in paths) {
      canvas.drawPath(p, glowPaint);
      canvas.drawPath(p, paint);
    }
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
        colors: [
          Colors.transparent,
          AppColors.accent.withOpacity(0.8),
          Colors.transparent
        ],
      ).createShader(Rect.fromLTWH(0, y - 1.5, size.width, 3))
      ..strokeWidth = 3
      ..style = PaintingStyle.stroke;
    canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
  }

  @override
  bool shouldRepaint(covariant _ScanLinePainter old) =>
      old.progress != progress;
}

// ═══════════════════════════════════════════════════════════════════════════════
// Glassmorphic Choice Dialog (Entrée / Sortie)
// ═══════════════════════════════════════════════════════════════════════════════
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
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 24),
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(28),
          border: Border.all(color: AppColors.border, width: 1.5),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF060818).withValues(alpha: 0.87),
              blurRadius: 30,
              offset: const Offset(0, 10),
            )
          ],
        ),
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Sci-fi Scan Logo
            Container(
              width: 76,
              height: 76,
              decoration: BoxDecoration(
                color: AppColors.accent.withOpacity(0.1),
                shape: BoxShape.circle,
                border: Border.all(color: AppColors.accent.withOpacity(0.25), width: 1.5),
              ),
              child: Icon(Icons.qr_code_2_rounded, size: 40, color: AppColors.accentSoft),
            ),
            const SizedBox(height: 20),
            Text(
              'QR CODE DÉTECTÉ',
              style: GoogleFonts.outfit(
                color: AppColors.textPri,
                fontSize: 18,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.0,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Enregistrez votre présence dans l\'établissement en sélectionnant l\'action ci-dessous.',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                color: AppColors.textSec,
                fontSize: 13,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 28),
            // Actions Buttons
            Row(
              children: [
                Expanded(
                  child: _ChoiceActionButton(
                    label: 'ENTRÉE',
                    icon: Icons.login_rounded,
                    baseColor: AppColors.green,
                    glowColor: AppColors.green.withOpacity(0.3),
                    onTap: () => onConfirm('ENTREE'),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: _ChoiceActionButton(
                    label: 'SORTIE',
                    icon: Icons.logout_rounded,
                    baseColor: AppColors.red,
                    glowColor: AppColors.red.withOpacity(0.3),
                    onTap: () => onConfirm('SORTIE'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            TextButton(
              onPressed: onCancel,
              style: TextButton.styleFrom(foregroundColor: AppColors.textSec),
              child: Text(
                'Annuler et reprendre',
                style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ChoiceActionButton extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color baseColor;
  final Color glowColor;
  final VoidCallback onTap;

  const _ChoiceActionButton({
    required this.label,
    required this.icon,
    required this.baseColor,
    required this.glowColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20),
        decoration: BoxDecoration(
          color: const Color(0xFF060818).withValues(alpha: 0.4),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: baseColor.withValues(alpha: 0.3), width: 1.5),
          boxShadow: [
            BoxShadow(color: glowColor, blurRadius: 10, spreadRadius: -2)
          ],
        ),
        child: Column(
          children: [
            Icon(icon, color: baseColor, size: 28),
            const SizedBox(height: 8),
            Text(
              label,
              style: GoogleFonts.outfit(
                color: baseColor,
                fontWeight: FontWeight.w800,
                fontSize: 14,
                letterSpacing: 0.8,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Futuristic Success Dialog
// ═══════════════════════════════════════════════════════════════════════════════
class _SuccessDialog extends StatelessWidget {
  final String employeeName;
  final String type;
  final String time;

  const _SuccessDialog({
    required this.employeeName,
    required this.type,
    required this.time,
  });

  @override
  Widget build(BuildContext context) {
    final isEntree = type == 'ENTREE';
    final accentColor = isEntree ? AppColors.green : AppColors.red;
    final accentGlow  = isEntree ? AppColors.green.withOpacity(0.3) : AppColors.red.withOpacity(0.3);
    final icon = isEntree ? Icons.login_rounded : Icons.logout_rounded;
    final headerText = isEntree ? 'ENTRÉE ENREGISTRÉE' : 'SORTIE ENREGISTRÉE';

    return Dialog(
      backgroundColor: Colors.transparent,
      child: Container(
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(28),
          border: Border.all(color: accentColor.withValues(alpha: 0.25), width: 1.5),
          boxShadow: [
            BoxShadow(color: accentGlow, blurRadius: 20),
            BoxShadow(color: const Color(0xFF060818).withValues(alpha: 0.6), blurRadius: 40)
          ],
        ),
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Glowing Success Circle
            Container(
              width: 84,
              height: 84,
              decoration: BoxDecoration(
                color: accentColor.withValues(alpha: 0.1),
                shape: BoxShape.circle,
                border: Border.all(color: accentColor.withValues(alpha: 0.4), width: 2),
              ),
              child: Icon(icon, color: accentColor, size: 40),
            ),
            const SizedBox(height: 24),
            Text(
              headerText,
              style: GoogleFonts.outfit(
                color: accentColor,
                fontSize: 18,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.0,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Bonjour, $employeeName',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                color: AppColors.textPri,
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
            if (time.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                'Pointage validé à $time',
                style: GoogleFonts.inter(color: AppColors.textSec, fontSize: 13),
              ),
            ],
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                SizedBox(
                  width: 12,
                  height: 12,
                  child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.textSec),
                ),
                const SizedBox(width: 8),
                Text(
                  'Reprise du scanner…',
                  style: GoogleFonts.inter(color: AppColors.textSec, fontSize: 11),
                ),
              ],
            )
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Last Scan Summary Card
// ═══════════════════════════════════════════════════════════════════════════════
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
    final accentColor = isEntree ? AppColors.green : AppColors.red;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
      decoration: BoxDecoration(
        color: const Color(0xFF060818).withValues(alpha: 0.6),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: accentColor.withValues(alpha: 0.3), width: 1.2),
      ),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: accentColor.withValues(alpha: 0.1),
              shape: BoxShape.circle,
              border: Border.all(color: accentColor.withValues(alpha: 0.2), width: 1),
            ),
            child: Icon(
              isEntree ? Icons.login_rounded : Icons.logout_rounded,
              color: accentColor,
              size: 20,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'DERNIER POINTAGE : $type',
                  style: GoogleFonts.outfit(
                    color: accentColor,
                    fontWeight: FontWeight.w800,
                    fontSize: 12,
                    letterSpacing: 0.6,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '$name — $time',
                  style: GoogleFonts.inter(color: AppColors.textPri, fontSize: 13, fontWeight: FontWeight.w500),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// Permission Denied view (Futuristic dark look)
// ═══════════════════════════════════════════════════════════════════════════════
class _PermissionDeniedView extends StatelessWidget {
  final VoidCallback onRetry;
  const _PermissionDeniedView({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.accent.withOpacity(0.4),
                shape: BoxShape.circle,
                border: Border.all(color: AppColors.red.withValues(alpha: 0.2), width: 1.5),
              ),
              child: Icon(Icons.no_photography_rounded, color: AppColors.red, size: 54),
            ),
            const SizedBox(height: 28),
            Text(
              'CAMÉRA INACCESSIBLE',
              style: GoogleFonts.outfit(
                color: Colors.white,
                fontSize: 20,
                fontWeight: FontWeight.w800,
                letterSpacing: 1.0,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Autorisez l\'accès à l\'appareil photo dans les paramètres système de votre mobile pour scanner les QR Codes.',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                color: AppColors.textSec,
                fontSize: 13,
                height: 1.6,
              ),
            ),
            const SizedBox(height: 36),
            ElevatedButton.icon(
              onPressed: () async {
                await openAppSettings();
                onRetry();
              },
              icon: const Icon(Icons.settings_rounded, size: 18),
              label: Text(
                'OUVRIR LES PARAMÈTRES',
                style: GoogleFonts.outfit(fontWeight: FontWeight.w800, fontSize: 13, letterSpacing: 0.8),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.accent,
                foregroundColor: Colors.white,
                elevation: 4,
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}


