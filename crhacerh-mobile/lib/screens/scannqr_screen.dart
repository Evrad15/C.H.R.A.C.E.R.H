import 'package:flutter/material.dart';

class ScannQRScreen extends StatelessWidget {
  const ScannQRScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(24),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 14,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'Scannez votre QR code pour valider votre présence ou accéder aux stations spécifiques.',
                  style: TextStyle(fontSize: 16, height: 1.6),
                ),
                SizedBox(height: 12),
                Text(
                  'Ouvrez l’appareil photo pour scanner le QR code situé à l’entrée ou sur votre badge.',
                  style: TextStyle(fontSize: 14, color: Colors.black54, height: 1.6),
                ),
              ],
            ),
          ),
          const SizedBox(height: 40),
          Expanded(
            child: Center(
              child: Container(
                width: 220,
                height: 220,
                decoration: BoxDecoration(
                  color: const Color(0xFFF5F6FA),
                  borderRadius: BorderRadius.circular(32),
                  border: Border.all(color: const Color(0xFFCED4DA), width: 1.5),
                ),
                child: const Center(
                  child: Icon(
                    Icons.qr_code_scanner,
                    size: 80,
                    color: Color(0xFF1976D2),
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Center(
            child: Text(
              'Appuyez pour scanner',
              style: TextStyle(fontSize: 15, color: Colors.grey[700]),
            ),
          ),
        ],
      ),
    );
  }
}
