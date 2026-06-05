import 'dart:convert';
import 'package:http/http.dart' as http;

class AuthService {
  // On utilise l'IP locale de ton PC qu'on a trouvée tout à l'heure
  static const String baseUrl = 'http://10.241.50.234:8000/api'; 

  /// Fonction pour vérifier le matricule de l'employé auprès de Laravel
  Future<Map<String, dynamic>> verifierMatricule(String matricule) async {
    final url = Uri.parse('$baseUrl/matricule/check');

    try {
      // Envoi de la requête à Laravel
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'matricule': matricule,
        }),
      );

      // Décodage de la réponse JSON
      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        // Si le matricule est valide (Succès)
        return {
          'success': true,
          'employee': responseData['employee'],
        };
      } else {
        // Si Laravel renvoie une erreur (ex: 404 introuvable)
        return {
          'success': false,
          'message': responseData['message'] ?? 'Erreur de validation.',
        };
      }
    } catch (e) {
      // Si le serveur est éteint, mauvaise IP, ou pas de réseau
      return {
        'success': false,
        'message': 'Impossible de joindre le serveur. Vérifiez votre connexion.',
      };
    }
  }
}