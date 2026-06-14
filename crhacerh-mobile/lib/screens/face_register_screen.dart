import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:permission_handler/permission_handler.dart';
import '../theme_manager.dart';
import '../services/local_storage_service.dart';

class FaceRegisterScreen extends StatefulWidget {
  final Map<String, dynamic> employee;

  const FaceRegisterScreen({super.key, required this.employee});

  @override
  State<FaceRegisterScreen> createState() => _FaceRegisterScreenState();
}

class _FaceRegisterScreenState extends State<FaceRegisterScreen>
    with SingleTickerProviderStateMixin {
  CameraController? _cameraController;
  List<CameraDescription>? _cameras;
  bool _isCameraInitialized = false;
  String _debugStatus = "Démarrage...";
  bool _isPermissionDenied = false;
  bool _isCapturing = false;
  XFile? _capturedImage;
  bool _isUploading = false;

  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  @override
  void initState() {
    super.initState();
    _initCamera();

    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 0.98, end: 1.05).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  Future<void> _initCamera() async {
    try {
      if (mounted) setState(() => _debugStatus = "Demande permission...");
      final status = await Permission.camera.request();
      if (status.isDenied || status.isPermanentlyDenied) {
        if (mounted) {
          setState(() {
            _isPermissionDenied = true;
            _debugStatus = "Permission refusée.";
          });
        }
        return;
      }

      if (mounted) setState(() => _debugStatus = "Recherche caméras...");
      _cameras = await availableCameras();
      if (_cameras != null && _cameras!.isNotEmpty) {
        final frontCamera = _cameras!.firstWhere(
          (camera) => camera.lensDirection == CameraLensDirection.front,
          orElse: () => _cameras!.first,
        );

        _cameraController = CameraController(
          frontCamera,
          ResolutionPreset.medium,
          enableAudio: false,
        );

        if (mounted) setState(() => _debugStatus = "Initialisation...");
        await _cameraController!.initialize();
        if (mounted) {
          setState(() {
            _isCameraInitialized = true;
            _isPermissionDenied = false;
          });
        }
      } else {
        if (mounted) setState(() => _debugStatus = "Aucune caméra trouvée.");
      }
    } catch (e) {
      print("Erreur initialisation caméra: $e");
      if (mounted) setState(() => _debugStatus = "Erreur: $e");
      if (e is CameraException && e.code == 'cameraPermissionDenied') {
        if (mounted) {
          setState(() {
            _isPermissionDenied = true;
          });
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text("Impossible d'accéder à la caméra : $e"),
              backgroundColor: AppColors.red,
            ),
          );
        }
      }
    }
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _pulseController.dispose();
    super.dispose();
  }

  Future<void> _capturePhoto() async {
    if (_cameraController == null || !_cameraController!.value.isInitialized) {
      return;
    }

    setState(() {
      _isCapturing = true;
    });

    try {
      final XFile image = await _cameraController!.takePicture();
      setState(() {
        _capturedImage = image;
        _isCapturing = false;
      });
    } catch (e) {
      print("Erreur capture : $e");
      setState(() {
        _isCapturing = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Échec de la capture : $e"),
            backgroundColor: AppColors.red,
          ),
        );
      }
    }
  }

  Future<void> _uploadImage() async {
    if (_capturedImage == null) return;

    setState(() {
      _isUploading = true;
    });

    final String baseUrl = kIsWeb
        ? 'http://localhost:8000/api'
        : 'https://crhacerh-api.loca.lt/api';
    
    final String url = '$baseUrl/employee/${widget.employee['id']}/register-face';

    try {
      var request = http.MultipartRequest('POST', Uri.parse(url));
      request.headers.addAll({
        'Accept': 'application/json',
        'bypass-tunnel-reminder': 'true',
      });

      if (kIsWeb) {
        // Pour le web, lire les octets
        final bytes = await _capturedImage!.readAsBytes();
        request.files.add(http.MultipartFile.fromBytes(
          'image',
          bytes,
          filename: 'face_${widget.employee['id']}.jpg',
        ));
      } else {
        request.files.add(await http.MultipartFile.fromPath(
          'image',
          _capturedImage!.path,
        ));
      }

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        final consentFacialAt = responseData['consent_facial_at'];

        // Mettre à jour l'employé en local
        final prefs = await SharedPreferences.getInstance();
        var localEmp = Map<String, dynamic>.from(widget.employee);
        localEmp['consent_facial_at'] = consentFacialAt;

        await prefs.setString('cached_employee', jsonEncode(localEmp));
        await LocalStorageService().saveEmployee(localEmp);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text('Votre visage a été enregistré avec succès !'),
              backgroundColor: AppColors.green,
              behavior: SnackBarBehavior.floating,
            ),
          );
          Navigator.pop(context, true);
        }
      } else {
        String msg = responseData['message'] ?? 'Erreur lors de la sauvegarde.';
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(msg),
              backgroundColor: AppColors.red,
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      }
    } catch (e) {
      print("Erreur upload : $e");
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Impossible de contacter le serveur.'),
            backgroundColor: AppColors.orange,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isUploading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        systemOverlayStyle: AppColors.overlayStyle,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_rounded, color: AppColors.textPri),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Reconnaissance Faciale',
          style: GoogleFonts.outfit(
            color: AppColors.textPri,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        child: _isPermissionDenied
            ? _buildPermissionDeniedView()
            : Padding(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    const SizedBox(height: 10),
              Text(
                'Enregistrement de votre visage',
                style: GoogleFonts.outfit(
                  color: AppColors.textPri,
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Cadrez votre visage dans le cercle ci-dessous. Assurez-vous d\'être dans un endroit bien éclairé.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                  color: AppColors.textSec,
                  fontSize: 13,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 40),

              // Rendu Caméra / Image capturée
              Expanded(
                child: Center(
                  child: ScaleTransition(
                    scale: _pulseAnimation,
                    child: Container(
                      width: 280,
                      height: 280,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: _capturedImage != null ? AppColors.green : AppColors.accent,
                          width: 4,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: (_capturedImage != null ? AppColors.green : AppColors.accent)
                                .withOpacity(0.25),
                            blurRadius: 30,
                            offset: const Offset(0, 10),
                          )
                        ],
                      ),
                      child: ClipOval(
                        child: _capturedImage != null
                            ? (kIsWeb
                                ? Image.network(_capturedImage!.path, fit: BoxFit.cover)
                                : Image.file(File(_capturedImage!.path), fit: BoxFit.cover))
                            : (_isCameraInitialized
                                ? AspectRatio(
                                    aspectRatio: 1,
                                    child: CameraPreview(_cameraController!),
                                  )
                                : Container(
                                    color: Colors.black12,
                                    child: Center(
                                      child: Column(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          CircularProgressIndicator(
                                            color: AppColors.accent,
                                          ),
                                          const SizedBox(height: 16),
                                          Text(
                                            _debugStatus,
                                            style: TextStyle(
                                              color: AppColors.textSec,
                                              fontSize: 12,
                                            ),
                                            textAlign: TextAlign.center,
                                          ),
                                        ],
                                      ),
                                    ),
                                  )),
                      ),
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 40),

              // Actions
              if (_capturedImage == null)
                // Bouton Prendre la photo
                GestureDetector(
                  onTap: _isCapturing || !_isCameraInitialized ? null : _capturePhoto,
                  child: Container(
                    width: 70,
                    height: 70,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: AppColors.accent,
                      border: Border.all(color: Colors.white, width: 4),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.accent.withOpacity(0.4),
                          blurRadius: 16,
                          offset: const Offset(0, 6),
                        )
                      ],
                    ),
                    child: _isCapturing
                        ? const Center(
                            child: CircularProgressIndicator(
                              color: Colors.white,
                              strokeWidth: 3,
                            ),
                          )
                        : const Icon(
                            Icons.camera_alt_rounded,
                            color: Colors.white,
                            size: 30,
                          ),
                  ),
                )
              else
                // Boutons Recommencer et Valider
                Column(
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _isUploading
                                ? null
                                : () {
                                    setState(() {
                                      _capturedImage = null;
                                    });
                                  },
                            icon: const Icon(Icons.refresh_rounded, size: 18),
                            label: const Text('RECOMMENCER'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: AppColors.red,
                              side: BorderSide(color: AppColors.red.withOpacity(0.5)),
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: _isUploading ? null : _uploadImage,
                            icon: _isUploading
                                ? const SizedBox(
                                    width: 18,
                                    height: 18,
                                    child: CircularProgressIndicator(
                                      color: Colors.white,
                                      strokeWidth: 2,
                                    ),
                                  )
                                : const Icon(Icons.cloud_upload_rounded, size: 18),
                            label: const Text('ENREGISTRER'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.green,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              elevation: 0,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPermissionDeniedView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.red.withOpacity(0.15),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.videocam_off_rounded,
                color: AppColors.red,
                size: 60,
              ),
            ),
            const SizedBox(height: 24),
            Text(
              'Accès Caméra Requis',
              style: GoogleFonts.outfit(
                color: AppColors.textPri,
                fontSize: 22,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Pour enregistrer votre visage, l\'application doit pouvoir accéder à votre caméra. Veuillez autoriser cet accès dans les paramètres du système.',
              textAlign: TextAlign.center,
              style: GoogleFonts.inter(
                color: AppColors.textSec,
                fontSize: 14,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 32),
            ElevatedButton.icon(
              onPressed: () async {
                final success = await openAppSettings();
                if (success) {
                  _initCamera();
                }
              },
              icon: const Icon(Icons.settings_rounded, size: 18),
              label: const Text('OUVRIR LES PARAMÈTRES'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.accent,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                elevation: 0,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
