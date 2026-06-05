import 'package:flutter/material.dart';

class MatriculeScreenModel {
  // Controllers
  late TextEditingController matriculeController;
  late FocusNode matriculeFocusNode;

  // Variables
  String matricule = '';
  bool isLoading = false; // <-- Ajout pour gérer l'état de l'API

  MatriculeScreenModel() {
    matriculeController = TextEditingController();
    matriculeFocusNode = FocusNode();
  }

  void dispose() {
    matriculeController.dispose();
    matriculeFocusNode.dispose();
  }

  // Valider format matricule
  bool validateMatricule(String value) {
    if (value.isEmpty) {
      return false;
    }
    final regex = RegExp(r'^[a-zA-Z0-9]+-[0-9]+-[0-9]+$');
    return regex.hasMatch(value);
  }

  // Formater le matricule
  String formatMatricule(String value) {
    return value.toLowerCase().trim();
  }
}