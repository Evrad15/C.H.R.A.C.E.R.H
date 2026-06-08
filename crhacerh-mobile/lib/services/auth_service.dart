import 'dart:convert';
import 'package:http/http.dart' as http;

class AuthService {
  // URL de base de ton tunnel local de développement
  final String baseUrl = 'https://crhacerh-api.loca.lt/api';

  /// Les en-têtes globaux contenant la configuration de sécurité pour Localtunnel
  Map<String, String> _getHeaders() {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'bypass-tunnel-reminder': 'true', // Débloque définitivement le flux face à Localtunnel
    };
  }

  /// Vérification du matricule de l'employé
  Future<Map<String, dynamic>> checkMatricule(String matricule) async {
    final String url = '$baseUrl/matricule/check';

    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _getHeaders(),
        body: jsonEncode({'matricule': matricule}),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'employee': responseData['employee'],
        };
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Matricule invalide.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Impossible de joindre le serveur local.',
      };
    }
  }

  /// Récupération des données du tableau de bord d'un employé (Statistiques & Historique)
  Future<Map<String, dynamic>> fetchDashboardData(int employeeId) async {
    final String url = '$baseUrl/employee/$employeeId/dashboard';

    try {
      final response = await http.get(
        Uri.parse(url),
        headers: _getHeaders(),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'data': responseData,
        };
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Erreur lors du chargement des statistiques.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur réseau : Impossible de récupérer les données du tableau de bord.',
      };
    }
  }
}