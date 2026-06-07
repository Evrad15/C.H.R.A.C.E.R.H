import 'package:flutter/material.dart';

class MessageScreen extends StatelessWidget {
  const MessageScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: const Color(0xFFEEF0F7),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'Cet espace est exclusivement dédié à vos échanges professionnels avec l’administration du CHRACERH.',
                  style: TextStyle(fontSize: 14, height: 1.6),
                ),
                SizedBox(height: 12),
                Text('• Signaler une anomalie sur vos horaires ou un oubli de pointage.', style: TextStyle(fontSize: 14, height: 1.6)),
                Text('• Transmettre un justificatif médical (en cas d’absence).', style: TextStyle(fontSize: 14, height: 1.6)),
                Text('Note : Les messages sont traités par l’équipe des Ressources Humaines aux heures ouvrables de l’hôpital.', style: TextStyle(fontSize: 14, height: 1.6)),
              ],
            ),
          ),
          const SizedBox(height: 20),
          Center(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: const Text(
                'Samedi 06/06/26',
                style: TextStyle(fontSize: 12, color: Color(0xFF5F5F5F)),
              ),
            ),
          ),
          const SizedBox(height: 20),
          const ChatBubble(
            text: 'Bonjour Monsieur',
            time: '11:45',
            isSentByMe: false,
          ),
          const SizedBox(height: 12),
          const ChatBubble(
            text: 'Bonjour Madame',
            time: '17:47',
            isSentByMe: true,
          ),
          const SizedBox(height: 36),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFE0E0E0)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                IconButton(
                  onPressed: () {},
                  icon: const Icon(Icons.add, color: Color(0xFF1976D2)),
                ),
                const Expanded(
                  child: Text(
                    'Fichiers',
                    style: TextStyle(fontSize: 16, color: Color(0xFF1976D2)),
                  ),
                ),
                Expanded(
                  flex: 3,
                  child: TextField(
                    decoration: InputDecoration(
                      hintText: 'Écrire un message...',
                      hintStyle: TextStyle(color: Colors.grey[500]),
                      border: InputBorder.none,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class ChatBubble extends StatelessWidget {
  final String text;
  final String time;
  final bool isSentByMe;

  const ChatBubble({
    super.key,
    required this.text,
    required this.time,
    required this.isSentByMe,
  });

  @override
  Widget build(BuildContext context) {
    final bubbleColor = isSentByMe ? const Color(0xFFE3F9E5) : Colors.white;
    final textColor = Colors.black87;
    final alignment = isSentByMe ? Alignment.centerRight : Alignment.centerLeft;
    final borderRadius = BorderRadius.only(
      topLeft: const Radius.circular(18),
      topRight: const Radius.circular(18),
      bottomLeft: Radius.circular(isSentByMe ? 18 : 4),
      bottomRight: Radius.circular(isSentByMe ? 4 : 18),
    );

    return Align(
      alignment: alignment,
      child: Container(
        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.78),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: bubbleColor,
          borderRadius: borderRadius,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Text(text, style: TextStyle(color: textColor, fontSize: 15, height: 1.5)),
            const SizedBox(height: 6),
            Text(time, style: TextStyle(color: Colors.grey[600], fontSize: 11)),
          ],
        ),
      ),
    );
  }
}
