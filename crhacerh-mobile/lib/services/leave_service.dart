import 'dart:convert';
import 'package:http/http.dart' as http;
import 'auth_service.dart';

class LeaveService {
  final AuthService _api = AuthService();

  // Mme base URL que l'AuthService
  String get baseUrl => _api.baseUrl;

  Map<String, String> _getHeaders() {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Bypass-Tunnel-Reminder': 'true',
    };
  }

  /// Rcuprer l'historique des congs d'un employ
  Future<Map<String, dynamic>> fetchLeaves(int employeeId) async {
    final String url = '$baseUrl/employee/$employeeId/leaves';

    try {
      final response = await http.get(Uri.parse(url), headers: _getHeaders());
      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200 && responseData['success'] == true) {
        return {
          'success': true,
          'data': responseData['data'],
        };
      } else {
        return {
          'success': false,
          'message': responseData['error'] ?? 'Erreur lors du chargement des congs.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur rseau: $e',
      };
    }
  }

  /// Soumettre une demande de cong ou permission
  Future<Map<String, dynamic>> submitLeave({
    required int employeeId,
    required String type,
    required String dateDebut,
    required String dateFin,
    required String motif,
  }) async {
    final String url = '$baseUrl/employee/$employeeId/leaves';

    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _getHeaders(),
        body: jsonEncode({
          'type': type,
          'date_debut': dateDebut,
          'date_fin': dateFin,
          'motif': motif,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 201 && responseData['success'] == true) {
        return {
          'success': true,
          'data': responseData['data'],
        };
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? responseData['error'] ?? 'Erreur lors de la soumission.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur rseau: $e',
      };
    }
  }
}
