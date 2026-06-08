import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:http_parser/http_parser.dart';
import 'package:mime/mime.dart';
import 'package:path_provider/path_provider.dart';
import 'package:dio/dio.dart';

class ChatService {
  final String _baseUrl = kIsWeb
      ? 'http://localhost:8000/api'
      : 'https://crhacerh-api.loca.lt/api';

  Map<String, String> _getHeaders() {
    return {
      'Accept': 'application/json',
      'bypass-tunnel-reminder': 'true',
    };
  }

  Map<String, String> _getJsonHeaders() {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'bypass-tunnel-reminder': 'true',
    };
  }

  /// Récupère la liste des messages de la discussion
  Future<Map<String, dynamic>> fetchMessages(int employeeId) async {
    final String url = '$_baseUrl/employee/$employeeId/messages';

    try {
      final response = await http.get(
        Uri.parse(url),
        headers: _getJsonHeaders(),
      );

      if (response.statusCode == 200) {
        final responseData = jsonDecode(response.body);
        return {
          'success': true,
          'messages': responseData['messages'] ?? [],
          'thread_id': responseData['thread_id'],
        };
      } else {
        final responseData = jsonDecode(response.body);
        return {
          'success': false,
          'message': responseData['message'] ?? 'Erreur lors du chargement des messages.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur réseau : impossible de joindre le serveur.',
      };
    }
  }

  /// Envoie un message texte sans pièce jointe
  Future<Map<String, dynamic>> sendMessage(int employeeId, String body) async {
    final String url = '$_baseUrl/employee/$employeeId/messages';

    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _getJsonHeaders(),
        body: jsonEncode({'body': body}),
      );

      final responseData = jsonDecode(response.body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        return {'success': true, 'message': responseData['message']};
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Erreur lors de l\'envoi du message.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur réseau : impossible d\'envoyer le message.',
      };
    }
  }

  /// Envoie un message avec une pièce jointe (multipart/form-data)
  Future<Map<String, dynamic>> sendMessageWithAttachment({
    required int employeeId,
    String body = '',
    required File file,
    void Function(double progress)? onProgress,
  }) async {
    final String url = '$_baseUrl/employee/$employeeId/messages';

    try {
      final mimeType = lookupMimeType(file.path) ?? 'application/octet-stream';
      final mimeParts = mimeType.split('/');

      final request = http.MultipartRequest('POST', Uri.parse(url))
        ..headers.addAll(_getHeaders())
        ..fields['body'] = body
        ..files.add(await http.MultipartFile.fromPath(
          'attachment',
          file.path,
          contentType: MediaType(mimeParts[0], mimeParts[1]),
        ));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200 || response.statusCode == 201) {
        return {'success': true, 'message': responseData['message']};
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Erreur lors de l\'envoi de la pièce jointe.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur lors de l\'envoi : $e',
      };
    }
  }

  /// Télécharge une pièce jointe depuis l'URL et la sauvegarde localement
  /// Retourne le chemin local du fichier téléchargé ou null en cas d'erreur
  Future<String?> downloadAttachment({
    required String attachmentUrl,
    required String fileName,
    void Function(double progress)? onProgress,
  }) async {
    try {
      final dir = await getTemporaryDirectory();
      final filePath = '${dir.path}/$fileName';

      final dio = Dio();
      dio.options.headers['bypass-tunnel-reminder'] = 'true';

      await dio.download(
        attachmentUrl,
        filePath,
        onReceiveProgress: (received, total) {
          if (total > 0 && onProgress != null) {
            onProgress(received / total);
          }
        },
      );

      return filePath;
    } catch (e) {
      return null;
    }
  }

  /// Soumet un pointage via QR Code
  Future<Map<String, dynamic>> submitQRScan({
    required int employeeId,
    required String type, // 'ENTREE' ou 'SORTIE'
    double? latitude,
    double? longitude,
  }) async {
    final String url = '$_baseUrl/pointages/scan';

    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _getJsonHeaders(),
        body: jsonEncode({
          'employee_id': employeeId,
          'type': type,
          if (latitude != null) 'latitude': latitude,
          if (longitude != null) 'longitude': longitude,
        }),
      );

      final responseData = jsonDecode(response.body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        return {
          'success': true,
          'message': responseData['message'],
          'data': responseData['data'],
        };
      } else {
        return {
          'success': false,
          'message': responseData['message'] ?? 'Erreur lors du pointage.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur réseau : impossible d\'envoyer le pointage.',
      };
    }
  }
}
