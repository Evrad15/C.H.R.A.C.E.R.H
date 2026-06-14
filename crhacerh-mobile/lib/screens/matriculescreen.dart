import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'matriculescreen_model.dart';
import '../home_screen.dart';
import 'package:flutter/foundation.dart';
import '../services/local_storage_service.dart';
import '../theme_manager.dart';

class MatriculeScreen extends StatefulWidget {
  const MatriculeScreen({super.key});

  @override
  State<MatriculeScreen> createState() => _MatriculeScreenState();
}

class _MatriculeScreenState extends State<MatriculeScreen>
    with SingleTickerProviderStateMixin {
  late MatriculeScreenModel _model;
  final scaffoldKey = GlobalKey<ScaffoldState>();
  late AnimationController _animCtrl;
  late Animation<double> _logoFade;
  late Animation<Offset> _formSlide;
  bool _isFocused = false;

  final String apiUrl = kIsWeb
      ? 'http://localhost:8000/api/matricule/check'
      : 'https://crhacerh-api.loca.lt/api/matricule/check';

  @override
  void initState() {
    super.initState();
    _model = MatriculeScreenModel();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
    _logoFade = CurvedAnimation(parent: _animCtrl, curve: const Interval(0.0, 0.6, curve: Curves.easeOut));
    _formSlide = Tween<Offset>(begin: const Offset(0, 0.15), end: Offset.zero)
        .animate(CurvedAnimation(parent: _animCtrl, curve: const Interval(0.3, 1.0, curve: Curves.easeOutCubic)));
    _animCtrl.forward();
  }

  @override
  void dispose() {
    _model.dispose();
    _animCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => FocusScope.of(context).unfocus(),
      child: Scaffold(
        key: scaffoldKey,
        backgroundColor: AppColors.bg,
        body: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [AppColors.bg, const Color(0xFF0E1128)],
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
            ),
          ),
          child: SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 28),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight: MediaQuery.of(context).size.height -
                      MediaQuery.of(context).padding.top -
                      MediaQuery.of(context).padding.bottom,
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 60),

                    // ── Logo + Titre ────────────────────────────────────────
                    FadeTransition(
                      opacity: _logoFade,
                      child: Column(
                        children: [
                          // Halo lumineux autour du logo
                          Container(
                            width: 140,
                            height: 140,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: AppColors.accent.withOpacity(0.20),
                                  blurRadius: 60,
                                  spreadRadius: 10,
                                ),
                              ],
                            ),
                            child: ClipOval(
                              child: Container(
                                decoration: BoxDecoration(
                                  gradient: LinearGradient(
                                    colors: [
                                      const Color(0xFF161A35),
                                      const Color(0xFF0E1128),
                                    ],
                                    begin: Alignment.topLeft,
                                    end: Alignment.bottomRight,
                                  ),
                                  border: Border.all(
                                    color: AppColors.accent.withOpacity(0.2),
                                    width: 1.5,
                                  ),
                                  borderRadius: BorderRadius.circular(70),
                                ),
                                padding: const EdgeInsets.all(16),
                                child: Image.asset(
                                  'assets/images/chracerh logo.png',
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 28),
                          Text(
                            'C.H.R.A.C.E.R.H',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.outfit(
                              fontSize: 22,
                              fontWeight: FontWeight.w800,
                              color: AppColors.textPri,
                              letterSpacing: 2.0,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Système de Gestion des Présences',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.inter(
                              fontSize: 13,
                              color: AppColors.textSec,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 56),

                    // ── Formulaire ──────────────────────────────────────────
                    SlideTransition(
                      position: _formSlide,
                      child: FadeTransition(
                        opacity: _logoFade,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Label
                            Text(
                              'Votre matricule',
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textSec,
                                letterSpacing: 0.3,
                              ),
                            ),
                            const SizedBox(height: 10),

                            // Champ matricule glassmorphic
                            AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              decoration: BoxDecoration(
                                color: AppColors.inputBg,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(
                                  color: _isFocused
                                      ? AppColors.accent.withOpacity(0.7)
                                      : AppColors.border,
                                  width: 1.5,
                                ),
                                boxShadow: _isFocused
                                    ? [
                                        BoxShadow(
                                          color: AppColors.accent.withOpacity(0.12),
                                          blurRadius: 20,
                                          spreadRadius: -2,
                                        )
                                      ]
                                    : [],
                              ),
                              child: Row(
                                children: [
                                  Padding(
                                    padding: const EdgeInsets.only(left: 18, right: 12),
                                    child: Icon(
                                      Icons.badge_rounded,
                                      color: _isFocused
                                          ? AppColors.accentSoft
                                          : AppColors.textSec,
                                      size: 22,
                                    ),
                                  ),
                                  Expanded(
                                    child: Focus(
                                      onFocusChange: (focused) =>
                                          setState(() => _isFocused = focused),
                                      child: TextFormField(
                                        controller: _model.matriculeController,
                                        focusNode: _model.matriculeFocusNode,
                                        textCapitalization: TextCapitalization.none,
                                        obscureText: false,
                                        maxLines: 1,
                                        enabled: !_model.isLoading,
                                        decoration: InputDecoration(
                                          isDense: true,
                                          hintText: 'Ex: chr-2026-000',
                                          hintStyle: GoogleFonts.robotoMono(
                                            color: AppColors.textSec.withOpacity(0.5),
                                            fontSize: 15,
                                          ),
                                          border: InputBorder.none,
                                          contentPadding: const EdgeInsets.symmetric(
                                              vertical: 18),
                                        ),
                                        style: GoogleFonts.robotoMono(
                                          color: AppColors.textPri,
                                          fontSize: 16,
                                          fontWeight: FontWeight.w500,
                                          letterSpacing: 0.5,
                                        ),
                                        textAlign: TextAlign.start,
                                        keyboardType: TextInputType.text,
                                        onChanged: (value) =>
                                            _model.matricule = value,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 16),
                                ],
                              ),
                            ),

                            const SizedBox(height: 28),

                            // Bouton Valider — gradient CTA
                            SizedBox(
                              width: double.infinity,
                              height: 58,
                              child: _model.isLoading
                                  ? Container(
                                      decoration: BoxDecoration(
                                        gradient: AppColors.accentGradient,
                                        borderRadius: BorderRadius.circular(16),
                                        boxShadow: [
                                          BoxShadow(
                                            color: AppColors.accentGlow,
                                            blurRadius: 20,
                                            offset: const Offset(0, 6),
                                          )
                                        ],
                                      ),
                                      child: const Center(
                                        child: SizedBox(
                                          width: 22,
                                          height: 22,
                                          child: CircularProgressIndicator(
                                            color: Colors.white,
                                            strokeWidth: 2.5,
                                          ),
                                        ),
                                      ),
                                    )
                                  : GestureDetector(
                                      onTap: () => _handleMatriculeSubmit(context),
                                      child: Container(
                                        decoration: BoxDecoration(
                                          gradient: AppColors.accentGradient,
                                          borderRadius: BorderRadius.circular(16),
                                          boxShadow: [
                                            BoxShadow(
                                              color: AppColors.accentGlow,
                                              blurRadius: 20,
                                              offset: const Offset(0, 6),
                                            )
                                          ],
                                        ),
                                        child: Center(
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Text(
                                                'Connexion',
                                                style: GoogleFonts.outfit(
                                                  color: Colors.white,
                                                  fontSize: 17,
                                                  fontWeight: FontWeight.w700,
                                                  letterSpacing: 0.5,
                                                ),
                                              ),
                                              const SizedBox(width: 10),
                                              const Icon(
                                                Icons.arrow_forward_rounded,
                                                color: Colors.white,
                                                size: 20,
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                            ),

                            const SizedBox(height: 20),

                            // Aide
                            Center(
                              child: Text(
                                'Votre matricule vous a été communiqué par les RH',
                                textAlign: TextAlign.center,
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: AppColors.textSec.withOpacity(0.6),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    const SizedBox(height: 60),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _handleMatriculeSubmit(BuildContext context) async {
    String matricule = _model.matricule.trim();

    if (matricule.isEmpty) {
      _showSnackBar(context, 'Veuillez entrer votre matricule', AppColors.red);
      return;
    }

    setState(() => _model.isLoading = true);

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Bypass-Tunnel-Reminder': 'true',
        },
        body: jsonEncode({'matricule': matricule}),
      );

      try {
        final responseData = jsonDecode(response.body);

        if (response.statusCode == 200) {
          var employee = responseData['employee'];

          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('cached_employee', jsonEncode(employee));
          await LocalStorageService()
              .saveEmployee(Map<String, dynamic>.from(employee));

          if (context.mounted) {
            _showSnackBar(
              context,
              'Bienvenue ${employee['first_name']} ${employee['last_name']}',
              AppColors.green,
            );
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(
                builder: (context) => HomeScreen(employee: employee),
              ),
            );
          }
        } else {
          if (context.mounted) {
            String errorMsg = responseData['message'] ?? 'Matricule invalide.';
            _showSnackBar(context, errorMsg, AppColors.red);
          }
        }
      } catch (e) {
        if (context.mounted) {
          _showSnackBar(
            context,
            'Erreur serveur (${response.statusCode}). Vérifiez Localtunnel.',
            AppColors.orange,
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        _showSnackBar(
          context,
          'Impossible de joindre le serveur. Vérifiez votre connexion.',
          AppColors.orange,
        );
      }
    } finally {
      if (mounted) setState(() => _model.isLoading = false);
    }
  }

  void _showSnackBar(BuildContext context, String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
          style: GoogleFonts.inter(fontWeight: FontWeight.w500),
        ),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        margin: const EdgeInsets.all(16),
        duration: const Duration(seconds: 3),
      ),
    );
  }
}