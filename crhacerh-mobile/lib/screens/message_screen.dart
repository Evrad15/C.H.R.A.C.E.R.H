import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path/path.dart' as p;
import '../services/chat_service.dart';

// ─────────────────────────────────────────────────────────────────────────────
// Design tokens (cohérents avec home_screen.dart)
// ─────────────────────────────────────────────────────────────────────────────
import '../theme_manager.dart';

// ─────────────────────────────────────────────────────────────────────────────
// MessageScreen
// ─────────────────────────────────────────────────────────────────────────────
class MessageScreen extends StatefulWidget {
  final int employeeId;
  const MessageScreen({super.key, required this.employeeId});

  @override
  State<MessageScreen> createState() => _MessageScreenState();
}

class _MessageScreenState extends State<MessageScreen>
    with SingleTickerProviderStateMixin {
  final ChatService _chatService = ChatService();
  final TextEditingController _msgCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();
  final FocusNode _focusNode = FocusNode();

  List<dynamic> _messages = [];
  bool _isLoading = true;
  Timer? _refreshTimer;

  File? _selectedFile;
  String? _selectedFileName;
  bool _isSending = false;

  late AnimationController _fadeCtrl;
  late Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();
    _fadeCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 500));
    _fadeAnim =
        CurvedAnimation(parent: _fadeCtrl, curve: Curves.easeIn);
    _loadMessages(initial: true);
    _refreshTimer =
        Timer.periodic(const Duration(seconds: 5), (_) => _loadMessages());
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _msgCtrl.dispose();
    _scrollCtrl.dispose();
    _focusNode.dispose();
    _fadeCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadMessages({bool initial = false}) async {
    if (initial) {
      if (mounted) {
        setState(() => _isLoading = true);
      } else {
        _isLoading = true;
      }
    }
    final result = await _chatService.fetchMessages(widget.employeeId);
    if (mounted) {
      if (result['success']) {
        final newMsgs = result['messages'] ?? [];
        final hasNew = newMsgs.length != _messages.length;
        setState(() {
          _messages = newMsgs;
          _isLoading = false;
        });
        if (hasNew || initial) {
          _scrollToBottom();
          if (!_fadeCtrl.isCompleted) _fadeCtrl.forward();
        }
      } else {
        setState(() => _isLoading = false);
        if (initial && mounted) {
          _showSnack(result['message'] ?? 'Erreur de connexion', isError: true);
        }
      }
    }
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform
        .pickFiles(type: FileType.any, allowMultiple: false);
    if (result != null && result.files.single.path != null) {
      setState(() {
        _selectedFile = File(result.files.single.path!);
        _selectedFileName = result.files.single.name;
      });
    }
  }

  void _clearFile() =>
      setState(() => _selectedFile = _selectedFileName = null);

  Future<void> _sendMessage() async {
    final text = _msgCtrl.text.trim();
    if (text.isEmpty && _selectedFile == null) return;
    setState(() => _isSending = true);
    _msgCtrl.clear();

    Map<String, dynamic> response;
    if (_selectedFile != null) {
      response = await _chatService.sendMessageWithAttachment(
          employeeId: widget.employeeId, body: text, file: _selectedFile!);
      _clearFile();
    } else {
      response = await _chatService.sendMessage(widget.employeeId, text);
    }
    setState(() => _isSending = false);
    if (mounted) {
      if (response['success']) {
        await _loadMessages();
      } else {
        _showSnack(response['message'] ?? 'Erreur lors de l\'envoi.',
            isError: true);
      }
    }
  }

  Future<void> _downloadAndOpen(Map<String, dynamic> msg) async {
    final url = msg['attachment_url'] as String?;
    if (url == null || url.isEmpty) return;
    final fileName = p.basename(msg['attachment'] ?? url);

    _showSnack('Téléchargement de $fileName…');
    final localPath = await _chatService.downloadAttachment(
        attachmentUrl: url, fileName: fileName);
    if (!mounted) return;
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    if (localPath != null) {
      final res = await OpenFilex.open(localPath);
      if (res.type != ResultType.done && mounted) {
        _showSnack('Impossible d\'ouvrir : ${res.message}', isError: true);
      }
    } else {
      _showSnack('Erreur de téléchargement', isError: true);
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor:
          isError ? const Color(0xFFFF4757) : const Color(0xFF2D2D2D),
      behavior: SnackBarBehavior.floating,
      shape:
          RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ));
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollCtrl.hasClients) {
        _scrollCtrl.animateTo(
          _scrollCtrl.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  String _formatDate(DateTime dt) {
    final now = DateTime.now();
    if (dt.day == now.day && dt.month == now.month && dt.year == now.year) {
      return "Aujourd'hui";
    }
    final yd = now.subtract(const Duration(days: 1));
    if (dt.day == yd.day && dt.month == yd.month && dt.year == yd.year) {
      return "Hier";
    }
    const days = [
      'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'
    ];
    const months = [
      'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun',
      'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'
    ];
    return '${days[dt.weekday - 1]} ${dt.day} ${months[dt.month - 1]}';
  }

  // ── Build ─────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bg,
      body: Column(
        children: [
          _buildHeader(),
          Expanded(child: _buildBody()),
          if (_selectedFile != null) _buildFilePreview(),
          _buildInputBar(),
        ],
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      padding: EdgeInsets.fromLTRB(
          20, MediaQuery.of(context).padding.top + 16, 20, 16),
      decoration: BoxDecoration(
        color: AppColors.card,
        border: Border(
            bottom:
                BorderSide(color: Colors.white.withValues(alpha: 0.06))),
      ),
      child: Row(
        children: [
          // Avatar / icon
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E1B4B), Color(0xFF2D1B69)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(Icons.support_agent_rounded,
                color: AppColors.accentSft, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Ressources Humaines',
                    style: TextStyle(
                      color: AppColors.textPri,
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                    )),
                SizedBox(height: 2),
                Row(
                  children: [
                    _OnlineDot(),
                    SizedBox(width: 5),
                    Text('Aux heures ouvrables',
                        style:
                            TextStyle(color: AppColors.textSec, fontSize: 11)),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Body (messages list) ──────────────────────────────────────────────────
  Widget _buildBody() {
    if (_isLoading) {
      return Center(
        child: CircularProgressIndicator(color: AppColors.accent),
      );
    }

    final widgets = <Widget>[];

    // Info banner
    widgets.add(_InfoBanner());
    widgets.add(const SizedBox(height: 16));

    if (_messages.isEmpty) {
      widgets.add(_buildEmpty());
    } else {
      String? lastDateStr;
      for (final msg in _messages) {
        final dt = DateTime.parse(msg['created_at']).toLocal();
        final dateStr = _formatDate(dt);
        if (dateStr != lastDateStr) {
          lastDateStr = dateStr;
          widgets.add(_DateDivider(label: dateStr));
        }
        final hasAttach = (msg['attachment_url'] as String?) != null &&
            (msg['attachment_url'] as String).isNotEmpty;
        widgets.add(FadeTransition(
          opacity: _fadeAnim,
          child: _ChatBubble(
            text: msg['body'] ?? '',
            time: msg['time'] ?? '',
            isSentByMe: msg['is_sent_by_me'] ?? false,
            attachmentUrl: hasAttach ? msg['attachment_url'] as String : null,
            attachmentName:
                hasAttach ? p.basename(msg['attachment'] ?? '') : null,
            onAttachmentTap:
                hasAttach ? () => _downloadAndOpen(msg) : null,
          ),
        ));
        widgets.add(const SizedBox(height: 6));
      }
    }

    return ListView(
      controller: _scrollCtrl,
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
      children: widgets,
    );
  }

  Widget _buildEmpty() {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 60),
      child: Column(
        children: [
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              color: AppColors.accent.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(Icons.chat_bubble_outline_rounded,
                size: 32, color: AppColors.accent),
          ),
          const SizedBox(height: 16),
          Text('Aucun message',
              style: TextStyle(
                  color: AppColors.textPri,
                  fontSize: 16,
                  fontWeight: FontWeight.w600)),
          const SizedBox(height: 6),
          Text('Envoyez un message pour démarrer.',
              style: TextStyle(color: AppColors.textSec, fontSize: 13),
              textAlign: TextAlign.center),
        ],
      ),
    );
  }

  // ── File preview bar ──────────────────────────────────────────────────────
  Widget _buildFilePreview() {
    final ext = p.extension(_selectedFileName ?? '').toLowerCase();
    final isImg = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.heic']
        .contains(ext);
    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 4),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: AppColors.card,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
            color: AppColors.accent.withValues(alpha: 0.25)),
      ),
      child: Row(
        children: [
          if (isImg)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.file(_selectedFile!,
                  width: 44, height: 44, fit: BoxFit.cover),
            )
          else
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                  color: AppColors.accent.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10)),
              child: Icon(Icons.insert_drive_file_rounded,
                  color: AppColors.accentSft, size: 22),
            ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(_selectedFileName ?? 'Fichier',
                    style: TextStyle(
                        color: AppColors.textPri,
                        fontWeight: FontWeight.w600,
                        fontSize: 13),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis),
                Text('Prêt à envoyer',
                    style: TextStyle(color: AppColors.accentSft, fontSize: 11)),
              ],
            ),
          ),
          GestureDetector(
            onTap: _clearFile,
            child: Icon(Icons.close_rounded,
                color: AppColors.textSec, size: 20),
          ),
        ],
      ),
    );
  }

  // ── Input bar ─────────────────────────────────────────────────────────────
  Widget _buildInputBar() {
    return Container(
      padding: EdgeInsets.fromLTRB(
          12, 10, 12, MediaQuery.of(context).padding.bottom + 10),
      decoration: BoxDecoration(
        color: AppColors.card,
        border: Border(
            top:
                BorderSide(color: Colors.white.withValues(alpha: 0.06))),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          // Attach
          _InputActionButton(
            icon: Icons.attach_file_rounded,
            onTap: _isSending ? null : _pickFile,
            active: _selectedFile != null,
          ),
          const SizedBox(width: 8),
          // Text field
          Expanded(
            child: Container(
              constraints: const BoxConstraints(maxHeight: 120),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: AppColors.inputBg,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(
                    color: _focusNode.hasFocus
                        ? AppColors.accent.withValues(alpha: 0.4)
                        : Colors.white.withValues(alpha: 0.08)),
              ),
              child: TextField(
                controller: _msgCtrl,
                focusNode: _focusNode,
                onChanged: (_) => setState(() {}),
                onSubmitted: _isSending ? null : (_) => _sendMessage(),
                enabled: !_isSending,
                maxLines: null,
                style: TextStyle(color: AppColors.textPri, fontSize: 14),
                decoration: InputDecoration(
                  hintText: 'Écrire un message…',
                  hintStyle: TextStyle(color: AppColors.textSec, fontSize: 14),
                  border: InputBorder.none,
                  isDense: true,
                  contentPadding: EdgeInsets.symmetric(vertical: 10),
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          // Send button
          _isSending
              ? Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppColors.accent.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                          color: AppColors.accent, strokeWidth: 2),
                    ),
                  ),
                )
              : _SendButton(
                  enabled:
                      _msgCtrl.text.trim().isNotEmpty || _selectedFile != null,
                  onTap: _sendMessage,
                ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Info banner
// ─────────────────────────────────────────────────────────────────────────────
class _InfoBanner extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1A1A2E),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
            color: AppColors.accent.withValues(alpha: 0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.info_outline_rounded,
                  color: AppColors.accentSft, size: 16),
              SizedBox(width: 8),
              Text('Espace professionnel',
                  style: TextStyle(
                      color: AppColors.accentSft,
                      fontSize: 13,
                      fontWeight: FontWeight.w700)),
            ],
          ),
          SizedBox(height: 10),
          Text(
            'Cet espace est dédié à vos échanges avec l\'administration du CHRACERH.',
            style: TextStyle(
                color: AppColors.textSec, fontSize: 12, height: 1.5),
          ),
          SizedBox(height: 8),
          _InfoLine(text: 'Signaler une anomalie de pointage'),
          _InfoLine(text: 'Transmettre un justificatif médical'),
          _InfoLine(text: 'Traitement par les RH aux heures ouvrables'),
        ],
      ),
    );
  }
}

class _InfoLine extends StatelessWidget {
  final String text;
  const _InfoLine({required this.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('•  ',
              style: TextStyle(color: AppColors.accent, fontSize: 12)),
          Expanded(
            child: Text(text,
                style: TextStyle(
                    color: AppColors.textSec, fontSize: 12, height: 1.4)),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Date divider
// ─────────────────────────────────────────────────────────────────────────────
class _DateDivider extends StatelessWidget {
  final String label;
  const _DateDivider({required this.label});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Row(
        children: [
          Expanded(
              child: Divider(
                  color: Colors.white.withValues(alpha: 0.08),
                  thickness: 1)),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: Text(label,
                style: TextStyle(
                    color: AppColors.textSec,
                    fontSize: 11,
                    fontWeight: FontWeight.w500)),
          ),
          Expanded(
              child: Divider(
                  color: Colors.white.withValues(alpha: 0.08),
                  thickness: 1)),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Chat bubble
// ─────────────────────────────────────────────────────────────────────────────
class _ChatBubble extends StatelessWidget {
  final String text, time;
  final bool isSentByMe;
  final String? attachmentUrl, attachmentName;
  final VoidCallback? onAttachmentTap;

  const _ChatBubble({
    required this.text,
    required this.time,
    required this.isSentByMe,
    this.attachmentUrl,
    this.attachmentName,
    this.onAttachmentTap,
  });

  bool get _hasAttach =>
      attachmentUrl != null && attachmentUrl!.isNotEmpty;
  bool get _isImage {
    if (attachmentName == null) return false;
    final ext = p.extension(attachmentName!).toLowerCase();
    return ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.heic']
        .contains(ext);
  }

  IconData _iconFor(String? name) {
    if (name == null) return Icons.insert_drive_file_rounded;
    final ext = p.extension(name).toLowerCase();
    if (['.jpg', '.jpeg', '.png', '.gif', '.webp'].contains(ext)) {
      return Icons.image_rounded;
    }
    if (ext == '.pdf') return Icons.picture_as_pdf_rounded;
    if (['.doc', '.docx'].contains(ext)) return Icons.article_rounded;
    if (['.xls', '.xlsx'].contains(ext)) return Icons.table_chart_rounded;
    return Icons.insert_drive_file_rounded;
  }

  @override
  Widget build(BuildContext context) {
    final align =
        isSentByMe ? Alignment.centerRight : Alignment.centerLeft;
    final bubbleColor = isSentByMe ? AppColors.meBubble : AppColors.themBubble;
    final radius = BorderRadius.only(
      topLeft: const Radius.circular(18),
      topRight: const Radius.circular(18),
      bottomLeft: Radius.circular(isSentByMe ? 18 : 4),
      bottomRight: Radius.circular(isSentByMe ? 4 : 18),
    );

    return Align(
      alignment: align,
      child: Container(
        constraints: BoxConstraints(
            maxWidth: MediaQuery.of(context).size.width * 0.76),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: bubbleColor,
          borderRadius: radius,
          border: isSentByMe
              ? Border.all(
                  color: AppColors.accent.withValues(alpha: 0.3))
              : null,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.2),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            // Attachment
            if (_hasAttach)
              GestureDetector(
                onTap: onAttachmentTap,
                child: Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.06),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: _isImage
                      ? Stack(children: [
                          Image.network(attachmentUrl!,
                              height: 160,
                              width: double.infinity,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => _FileTile(
                                    icon: _iconFor(attachmentName),
                                    name: attachmentName ?? 'Image',
                                  )),
                          Positioned(
                            right: 8,
                            bottom: 8,
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                  color: Colors.black54,
                                  borderRadius:
                                      BorderRadius.circular(20)),
                              child: const Icon(Icons.download_rounded,
                                  color: Colors.white, size: 16),
                            ),
                          ),
                        ])
                      : _FileTile(
                          icon: _iconFor(attachmentName),
                          name: attachmentName ?? 'Fichier'),
                ),
              ),

            // Text
            if (text.isNotEmpty && !text.startsWith('📎'))
              Align(
                alignment: Alignment.centerLeft,
                child: Text(text,
                    style: TextStyle(
                        color: isSentByMe ? Colors.white : AppColors.textPri, fontSize: 14, height: 1.5)),
              ),
            if (text.isNotEmpty && !text.startsWith('📎'))
              const SizedBox(height: 4),

            // Time + check
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(time,
                    style: TextStyle(
                        color: isSentByMe ? Colors.white.withValues(alpha: 0.7) : AppColors.textSec,
                        fontSize: 10)),
                if (isSentByMe) ...[
                  const SizedBox(width: 4),
                  Icon(Icons.done_all_rounded,
                      size: 12,
                      color:
                          Colors.white.withValues(alpha: 0.7)),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// File tile inside bubble
// ─────────────────────────────────────────────────────────────────────────────
class _FileTile extends StatelessWidget {
  final IconData icon;
  final String name;
  const _FileTile({required this.icon, required this.name});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: AppColors.accentSft, size: 26),
          const SizedBox(width: 10),
          Flexible(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name,
                    style: TextStyle(
                        color: AppColors.textPri,
                        fontWeight: FontWeight.w600,
                        fontSize: 12),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis),
                const SizedBox(height: 3),
                Row(children: [
                  Icon(Icons.download_rounded,
                      size: 12, color: AppColors.accentSft),
                  SizedBox(width: 4),
                  Text('Télécharger',
                      style: TextStyle(
                          color: AppColors.accentSft, fontSize: 11)),
                ]),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Misc widgets
// ─────────────────────────────────────────────────────────────────────────────
class _OnlineDot extends StatelessWidget {
  const _OnlineDot();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 7,
      height: 7,
      decoration: BoxDecoration(
        color: AppColors.green,
        shape: BoxShape.circle,
        boxShadow: [BoxShadow(color: AppColors.green, blurRadius: 4)],
      ),
    );
  }
}

class _InputActionButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onTap;
  final bool active;
  const _InputActionButton(
      {required this.icon, this.onTap, this.active = false});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          color: active
              ? AppColors.accent.withValues(alpha: 0.15)
              : AppColors.surface,
          borderRadius: BorderRadius.circular(14),
        ),
        child: Icon(icon,
            color: active ? AppColors.accent : AppColors.textSec, size: 20),
      ),
    );
  }
}

class _SendButton extends StatelessWidget {
  final bool enabled;
  final VoidCallback onTap;
  const _SendButton({required this.enabled, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: enabled ? onTap : null,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          gradient: enabled
              ? LinearGradient(
                  colors: [AppColors.accent, AppColors.accentSft],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                )
              : null,
          color: enabled ? null : AppColors.surface,
          borderRadius: BorderRadius.circular(14),
          boxShadow: enabled
              ? [
                  BoxShadow(
                      color: AppColors.accent.withValues(alpha: 0.35),
                      blurRadius: 10,
                      offset: const Offset(0, 4))
                ]
              : [],
        ),
        child: Icon(
          Icons.send_rounded,
          color: enabled ? Colors.white : AppColors.textSec,
          size: 18,
        ),
      ),
    );
  }
}


