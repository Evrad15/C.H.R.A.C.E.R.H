import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'dart:convert'; 
import 'package:http/http.dart' as http; 
import 'package:shared_preferences/shared_preferences.dart';
import 'matriculescreen_model.dart';
import '../home_screen.dart';
import 'package:flutter/foundation.dart';

class MatriculeScreen extends StatefulWidget {
  const MatriculeScreen({super.key});

  @override
  State<MatriculeScreen> createState() => _MatriculeScreenState();
}

class _MatriculeScreenState extends State<MatriculeScreen> {
  late MatriculeScreenModel _model;
  final scaffoldKey = GlobalKey<ScaffoldState>();

  // URL dynamique : utilise localhost pour Chrome (Web) et le tunnel pour le Mobile
  final String apiUrl = kIsWeb 
    ? 'http://localhost:8000/api/matricule/check' 
    : 'https://crhacerh-api.loca.lt/api/matricule/check';

  @override
  void initState() {
    super.initState();
    _model = MatriculeScreenModel();
  }

  @override
  void dispose() {
    _model.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => FocusScope.of(context).unfocus(),
      child: Scaffold(
        key: scaffoldKey,
        backgroundColor: const Color(0xFFF5F5F5),
        body: SafeArea(
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(32),
              child: Column(
                mainAxisSize: MainAxisSize.max,
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Logo et Titre de l'application
                  Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Container(
                        width: 280,
                        height: 200,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Image.asset(
                          'assets/images/chracerh logo.png',
                          fit: BoxFit.contain,
                        ),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Système de Pointages',
                        textAlign: TextAlign.center,
                        style: GoogleFonts.interTight(
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF1A1A1A),
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 48),

                  // Formulaire de saisie
                  Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Text(
                        'Veuillez entrer votre matricule',
                        style: GoogleFonts.interTight(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF424242),
                          height: 1.4,
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Champ de saisie du matricule
                      Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFFFFF),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: const Color(0xFFE0E0E0),
                            width: 2,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.05),
                              blurRadius: 4,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 4,
                          ),
                          child: TextFormField(
                            controller: _model.matriculeController,
                            focusNode: _model.matriculeFocusNode,
                            textCapitalization: TextCapitalization.none,
                            obscureText: false,
                            maxLines: 1,
                            decoration: InputDecoration(
                              isDense: true,
                              hintText: 'chr-2026-000',
                              hintStyle: GoogleFonts.roboto(
                                color: const Color(0xFF9E9E9E),
                                fontSize: 18,
                                fontWeight: FontWeight.w500,
                              ),
                              border: InputBorder.none,
                              contentPadding:
                                  const EdgeInsets.symmetric(vertical: 12),
                            ),
                            style: GoogleFonts.roboto(
                              color: const Color(0xFF1A1A1A),
                              fontSize: 18,
                              fontWeight: FontWeight.w500,
                            ),
                            textAlign: TextAlign.center,
                            keyboardType: TextInputType.text,
                            enabled: !_model.isLoading, 
                            onChanged: (value) {
                              _model.matricule = value;
                            },
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Bouton Valider avec indicateur de chargement adaptatif
                      ElevatedButton(
                        onPressed: _model.isLoading
                            ? null 
                            : () => _handleMatriculeSubmit(context),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF1976D2),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 48,
                            vertical: 16,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                          elevation: 2,
                        ),
                        child: _model.isLoading
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              )
                            : Text(
                                'Valider',
                                style: GoogleFonts.roboto(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 120),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // Traitement de la soumission et communication API
  void _handleMatriculeSubmit(BuildContext context) async {
    String matricule = _model.matricule.trim();

    if (matricule.isEmpty) {
      _showSnackBar(context, 'Veuillez entrer votre matricule', Colors.red);
      return;
    }

    setState(() {
      _model.isLoading = true;
    });

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'bypass-tunnel-reminder': 'true', // Force le bypass de la sécurité Localtunnel sur mobile
        },
        body: jsonEncode({'matricule': matricule}),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        var employee = responseData['employee'];
        
        // 1. Enregistrer les informations de l'employé en cache local JSON
        final prefs = await SharedPreferences.getInstance();
        String employeeJson = jsonEncode(employee);
        await prefs.setString('cached_employee', employeeJson);

        if (mounted) {
          _showSnackBar(
            context, 
            'Bienvenue ${employee['first_name']} ${employee['last_name']}', 
            Colors.green
          );

          // 2. Redirection vers la page d'accueil (en écrasant l'historique)
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => HomeScreen(employee: employee),
            ),
          );
        }
      } else {
        // Gestion des erreurs renvoyées par Laravel (ex: 404 introuvable)
        if (mounted) {
          String errorMsg = responseData['message'] ?? 'Matricule invalide.';
          _showSnackBar(context, errorMsg, Colors.red);
        }
      }
    } catch (e) {
      // Gestion des pannes réseaux globales
      _showSnackBar(
        context, 
        'Impossible de joindre le serveur. Vérifiez que votre serveur local et le tunnel sont actifs.', 
        Colors.orange
      );
    } finally {
      if (mounted) {
        setState(() {
          _model.isLoading = false;
        });
      }
    }
  }

  void _showSnackBar(BuildContext context, String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: color,
        duration: const Duration(seconds: 3),
      ),
    );
  }
}