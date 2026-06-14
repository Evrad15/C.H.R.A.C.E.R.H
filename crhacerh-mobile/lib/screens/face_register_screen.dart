import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
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
  bool _isPermissionDenied = false;
  bool _isCapturing = false;
  XFile? _capturedImage;
  bool _isUploading = false;

  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  void initState() {
    super.initState();

    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 0.98, end: 1.05).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  Future<void> _capturePhoto() async {
    setState(() {
      _isCapturing = true;
    });

    try {
      final ImagePicker picker = ImagePicker();
      final XFile? image = await picker.pickImage(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.front,
      );
      
      if (image != null) {
        if (mounted) {
          setState(() {
            _capturedImage = image;
          });
        }
      }
    } catch (e) {
      print("Erreur capture : $e");
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text("Échec de la capture : $e"),
            backgroundColor: AppColors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isCapturing = false;
        });
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
        child: Padding(
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
                            : Container(
                                color: AppColors.accent.withOpacity(0.1),
                                child: Center(
                                  child: Icon(
                                    Icons.face_retouching_natural_rounded,
                                    size: 80,
                                    color: AppColors.accent,
                                  ),
                                ),
                              ),
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
                  onTap: _isCapturing ? null : _capturePhoto,
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
}
