import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path/path.dart' as p;
import '../services/chat_service.dart';

class MessageScreen extends StatefulWidget {
  final int employeeId;

  const MessageScreen({super.key, required this.employeeId});

  @override
  State<MessageScreen> createState() => _MessageScreenState();
}

class _MessageScreenState extends State<MessageScreen> {
  final ChatService _chatService = ChatService();
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  List<dynamic> _messages = [];
  bool _isLoading = true;
  Timer? _refreshTimer;

  // Pièce jointe sélectionnée (en attente d'envoi)
  File? _selectedFile;
  String? _selectedFileName;
  bool _isSending = false;

  @override
  void initState() {
    super.initState();
    _loadMessages(initial: true);
    _refreshTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      _loadMessages(initial: false);
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadMessages({bool initial = false}) async {
    if (initial) setState(() => _isLoading = true);

    final result = await _chatService.fetchMessages(widget.employeeId);

    if (mounted) {
      if (result['success']) {
        final newMessages = result['messages'] ?? [];
        final hasNew = newMessages.length != _messages.length;
        setState(() {
          _messages = newMessages;
          _isLoading = false;
        });
        if (hasNew || initial) _scrollToBottom();
      } else {
        setState(() => _isLoading = false);
        if (initial) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(result['message'])),
          );
        }
      }
    }
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.any,
      allowMultiple: false,
    );
    if (result != null && result.files.single.path != null) {
      setState(() {
        _selectedFile = File(result.files.single.path!);
        _selectedFileName = result.files.single.name;
      });
    }
  }

  void _clearSelectedFile() {
    setState(() {
      _selectedFile = null;
      _selectedFileName = null;
    });
  }

  Future<void> _sendMessage() async {
    final text = _messageController.text.trim();
    if (text.isEmpty && _selectedFile == null) return;

    setState(() => _isSending = true);
    _messageController.clear();

    Map<String, dynamic> response;

    if (_selectedFile != null) {
      response = await _chatService.sendMessageWithAttachment(
        employeeId: widget.employeeId,
        body: text,
        file: _selectedFile!,
      );
      _clearSelectedFile();
    } else {
      response = await _chatService.sendMessage(widget.employeeId, text);
    }

    setState(() => _isSending = false);

    if (mounted) {
      if (response['success']) {
        await _loadMessages();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(response['message'] ?? 'Erreur lors de l\'envoi.')),
        );
      }
    }
  }

  Future<void> _downloadAndOpenAttachment(Map<String, dynamic> msg) async {
    final url = msg['attachment_url'] as String?;
    final attachment = msg['attachment'] as String?;
    if (url == null || url.isEmpty) return;

    final fileName = p.basename(attachment ?? url);

    // Indicateur de téléchargement
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(children: [
          const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),
          const SizedBox(width: 12),
          Expanded(child: Text('Téléchargement de $fileName…')),
        ]),
        duration: const Duration(seconds: 30),
      ),
    );

    final localPath = await _chatService.downloadAttachment(
      attachmentUrl: url,
      fileName: fileName,
    );

    if (!mounted) return;
    ScaffoldMessenger.of(context).hideCurrentSnackBar();

    if (localPath != null) {
      final result = await OpenFilex.open(localPath);
      if (result.type != ResultType.done) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Impossible d\'ouvrir le fichier : ${result.message}')),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Erreur lors du téléchargement du fichier.')),
      );
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  String _formatDate(DateTime dateTime) {
    final now = DateTime.now();
    if (dateTime.day == now.day && dateTime.month == now.month && dateTime.year == now.year) {
      return "Aujourd'hui";
    }
    final yesterday = now.subtract(const Duration(days: 1));
    if (dateTime.day == yesterday.day && dateTime.month == yesterday.month && dateTime.year == yesterday.year) {
      return "Hier";
    }
    const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    const months = [
      'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
      'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    return "${days[dateTime.weekday - 1]} ${dateTime.day} ${months[dateTime.month - 1]}";
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Zone des messages défilants
        Expanded(
          child: ListView(
            controller: _scrollController,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
            children: _buildMessageListWidgets(),
          ),
        ),

        // Aperçu du fichier sélectionné
        if (_selectedFile != null)
          _FilePreviewBar(
            fileName: _selectedFileName ?? 'Fichier',
            filePath: _selectedFile!.path,
            onRemove: _clearSelectedFile,
          ),

        // Zone de saisie fixe en bas
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          decoration: BoxDecoration(
            color: Colors.white,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 10,
                offset: const Offset(0, -5),
              ),
            ],
          ),
          child: SafeArea(
            top: false,
            child: Row(
              children: [
                // Bouton pièce jointe
                IconButton(
                  onPressed: _isSending ? null : _pickFile,
                  icon: Icon(
                    Icons.attach_file_rounded,
                    color: _selectedFile != null ? const Color(0xFF1976D2) : const Color(0xFF8A8A8A),
                  ),
                  tooltip: 'Joindre un fichier',
                ),
                // Champ de saisie
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF5F6F9),
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: const Color(0xFFE0E0E0)),
                    ),
                    child: TextField(
                      controller: _messageController,
                      onSubmitted: _isSending ? null : (_) => _sendMessage(),
                      enabled: !_isSending,
                      decoration: const InputDecoration(
                        hintText: 'Écrire un message…',
                        hintStyle: TextStyle(color: Color(0xFF9E9E9E)),
                        border: InputBorder.none,
                        isDense: true,
                        contentPadding: EdgeInsets.symmetric(vertical: 12),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                // Bouton envoyer
                _isSending
                    ? const Padding(
                        padding: EdgeInsets.all(10),
                        child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)),
                      )
                    : CircleAvatar(
                        backgroundColor: const Color(0xFF1976D2),
                        radius: 20,
                        child: IconButton(
                          onPressed: _sendMessage,
                          icon: const Icon(Icons.send_rounded, color: Colors.white, size: 18),
                        ),
                      ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  List<Widget> _buildMessageListWidgets() {
    List<Widget> children = [];

    // Message d'information de l'administration
    children.add(
      Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: const Color(0xFFEEF0F7),
          borderRadius: BorderRadius.circular(20),
        ),
        child: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Cet espace est exclusivement dédié à vos échanges professionnels avec l\'administration du CHRACERH.',
              style: TextStyle(fontSize: 14, height: 1.6, fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 12),
            Text('• Signaler une anomalie sur vos horaires ou un oubli de pointage.', style: TextStyle(fontSize: 14, height: 1.6)),
            Text('• Transmettre un justificatif médical (en cas d\'absence).', style: TextStyle(fontSize: 14, height: 1.6)),
            Text('Note : Les messages sont traités par l\'équipe des Ressources Humaines aux heures ouvrables de l\'hôpital.', style: TextStyle(fontSize: 14, height: 1.6)),
          ],
        ),
      ),
    );
    children.add(const SizedBox(height: 20));

    if (_isLoading) {
      children.add(const Center(child: Padding(padding: EdgeInsets.all(40.0), child: CircularProgressIndicator())));
    } else if (_messages.isEmpty) {
      children.add(Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 60.0),
          child: Column(
            children: [
              Icon(Icons.chat_bubble_outline, size: 48, color: Colors.grey[400]),
              const SizedBox(height: 12),
              Text('Aucun message pour le moment.', style: TextStyle(color: Colors.grey[600], fontSize: 15, fontWeight: FontWeight.w500)),
              const SizedBox(height: 6),
              Text('Envoyez un message pour démarrer la discussion.', style: TextStyle(color: Colors.grey[500], fontSize: 13), textAlign: TextAlign.center),
            ],
          ),
        ),
      ));
    } else {
      String? lastDateStr;
      for (var msg in _messages) {
        final DateTime createdAt = DateTime.parse(msg['created_at']).toLocal();
        final dateStr = _formatDate(createdAt);
        if (dateStr != lastDateStr) {
          lastDateStr = dateStr;
          children.add(Padding(
            padding: const EdgeInsets.symmetric(vertical: 16),
            child: Center(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Text(dateStr, style: const TextStyle(fontSize: 12, color: Color(0xFF5F5F5F), fontWeight: FontWeight.w500)),
              ),
            ),
          ));
        }

        final hasAttachment = msg['attachment_url'] != null && (msg['attachment_url'] as String).isNotEmpty;
        children.add(
          ChatBubble(
            text: msg['body'] ?? '',
            time: msg['time'] ?? '',
            isSentByMe: msg['is_sent_by_me'] ?? false,
            attachmentUrl: hasAttachment ? msg['attachment_url'] as String : null,
            attachmentName: hasAttachment ? p.basename(msg['attachment'] ?? '') : null,
            onAttachmentTap: hasAttachment ? () => _downloadAndOpenAttachment(msg) : null,
          ),
        );
        children.add(const SizedBox(height: 12));
      }
    }

    return children;
  }
}

// ────────────────────────────────────────────────────────
// Widget : Barre d'aperçu du fichier sélectionné
// ────────────────────────────────────────────────────────
class _FilePreviewBar extends StatelessWidget {
  final String fileName;
  final String filePath;
  final VoidCallback onRemove;

  const _FilePreviewBar({
    required this.fileName,
    required this.filePath,
    required this.onRemove,
  });

  IconData _iconForFile(String name) {
    final ext = p.extension(name).toLowerCase();
    if (['.jpg', '.jpeg', '.png', '.gif', '.webp', '.heic'].contains(ext)) return Icons.image_rounded;
    if (['.pdf'].contains(ext)) return Icons.picture_as_pdf_rounded;
    if (['.doc', '.docx'].contains(ext)) return Icons.article_rounded;
    if (['.xls', '.xlsx'].contains(ext)) return Icons.table_chart_rounded;
    if (['.mp4', '.mov', '.avi'].contains(ext)) return Icons.video_file_rounded;
    if (['.mp3', '.wav', '.aac'].contains(ext)) return Icons.audio_file_rounded;
    return Icons.insert_drive_file_rounded;
  }

  @override
  Widget build(BuildContext context) {
    final ext = p.extension(fileName).toLowerCase();
    final isImage = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.heic'].contains(ext);

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 0, 16, 4),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: const Color(0xFFE8F0FE),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF1976D2).withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          if (isImage)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.file(File(filePath), width: 44, height: 44, fit: BoxFit.cover),
            )
          else
            Container(
              width: 44, height: 44,
              decoration: BoxDecoration(color: const Color(0xFF1976D2).withValues(alpha: 0.12), borderRadius: BorderRadius.circular(10)),
              child: Icon(_iconForFile(fileName), color: const Color(0xFF1976D2), size: 24),
            ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(fileName, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
                const Text('Prêt à envoyer', style: TextStyle(fontSize: 12, color: Color(0xFF1976D2))),
              ],
            ),
          ),
          IconButton(
            onPressed: onRemove,
            icon: const Icon(Icons.close_rounded, color: Color(0xFF8A8A8A), size: 20),
            padding: EdgeInsets.zero,
            constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
          ),
        ],
      ),
    );
  }
}

// ────────────────────────────────────────────────────────
// Widget : Bulle de chat
// ────────────────────────────────────────────────────────
class ChatBubble extends StatelessWidget {
  final String text;
  final String time;
  final bool isSentByMe;
  final String? attachmentUrl;
  final String? attachmentName;
  final VoidCallback? onAttachmentTap;

  const ChatBubble({
    super.key,
    required this.text,
    required this.time,
    required this.isSentByMe,
    this.attachmentUrl,
    this.attachmentName,
    this.onAttachmentTap,
  });

  IconData _iconForName(String? name) {
    if (name == null) return Icons.insert_drive_file_rounded;
    final ext = p.extension(name).toLowerCase();
    if (['.jpg', '.jpeg', '.png', '.gif', '.webp', '.heic'].contains(ext)) return Icons.image_rounded;
    if (['.pdf'].contains(ext)) return Icons.picture_as_pdf_rounded;
    if (['.doc', '.docx'].contains(ext)) return Icons.article_rounded;
    if (['.xls', '.xlsx'].contains(ext)) return Icons.table_chart_rounded;
    if (['.mp4', '.mov', '.avi'].contains(ext)) return Icons.video_file_rounded;
    if (['.mp3', '.wav', '.aac'].contains(ext)) return Icons.audio_file_rounded;
    return Icons.insert_drive_file_rounded;
  }

  bool _isImageFile(String? name) {
    if (name == null) return false;
    final ext = p.extension(name).toLowerCase();
    return ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.heic'].contains(ext);
  }

  @override
  Widget build(BuildContext context) {
    final bubbleColor = isSentByMe ? const Color(0xFFE3F9E5) : Colors.white;
    final alignment = isSentByMe ? Alignment.centerRight : Alignment.centerLeft;
    final borderRadius = BorderRadius.only(
      topLeft: const Radius.circular(18),
      topRight: const Radius.circular(18),
      bottomLeft: Radius.circular(isSentByMe ? 18 : 4),
      bottomRight: Radius.circular(isSentByMe ? 4 : 18),
    );
    final hasAttachment = attachmentUrl != null && attachmentUrl!.isNotEmpty;
    final isImage = _isImageFile(attachmentName);

    return Align(
      alignment: alignment,
      child: Container(
        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.78),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: bubbleColor,
          borderRadius: borderRadius,
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            // Pièce jointe
            if (hasAttachment)
              GestureDetector(
                onTap: onAttachmentTap,
                child: Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.04),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: isImage
                      ? Stack(
                          children: [
                            Image.network(attachmentUrl!, height: 160, width: double.infinity, fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => _AttachmentFileTile(icon: _iconForName(attachmentName), name: attachmentName ?? 'Image'),
                            ),
                            Positioned(right: 8, bottom: 8,
                              child: Container(
                                padding: const EdgeInsets.all(6),
                                decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(20)),
                                child: const Icon(Icons.download_rounded, color: Colors.white, size: 16),
                              ),
                            ),
                          ],
                        )
                      : _AttachmentFileTile(icon: _iconForName(attachmentName), name: attachmentName ?? 'Fichier'),
                ),
              ),
            // Corps du message (si texte différent du nom de fichier)
            if (text.isNotEmpty && !text.startsWith('📎'))
              Align(
                alignment: Alignment.centerLeft,
                child: Text(text, style: const TextStyle(color: Colors.black87, fontSize: 15, height: 1.5)),
              ),
            if (text.isNotEmpty && !text.startsWith('📎')) const SizedBox(height: 6),
            Text(time, style: TextStyle(color: Colors.grey[600], fontSize: 11)),
          ],
        ),
      ),
    );
  }
}

class _AttachmentFileTile extends StatelessWidget {
  final IconData icon;
  final String name;
  const _AttachmentFileTile({required this.icon, required this.name});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: const Color(0xFF1976D2), size: 28),
          const SizedBox(width: 10),
          Flexible(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13), maxLines: 2, overflow: TextOverflow.ellipsis),
                const SizedBox(height: 2),
                const Row(children: [
                  Icon(Icons.download_rounded, size: 13, color: Color(0xFF1976D2)),
                  SizedBox(width: 4),
                  Text('Appuyer pour télécharger', style: TextStyle(fontSize: 11, color: Color(0xFF1976D2))),
                ]),
              ],
            ),
          ),
        ],
      ),
    );
  }
}