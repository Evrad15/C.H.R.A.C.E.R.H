import 'package:flutter/material.dart';
 
class FlutterFlowTheme {
  static FlutterFlowTheme of(BuildContext context) {
    return FlutterFlowTheme();
  }
 
  late Color onPrimary = const Color(0xFFF5F5F5);
  late Color secondaryBackground = const Color(0xFF1A1A1A);
  
  late TextStyle headlineMedium = const TextStyle(
    fontSize: 28,
    fontWeight: FontWeight.bold,
  );
  
  late TextStyle titleMedium = const TextStyle(
    fontSize: 18,
    fontWeight: FontWeight.w600,
  );
}