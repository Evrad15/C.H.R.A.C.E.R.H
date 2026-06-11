import 'dart:io';

import 'package:flutter/material.dart';
import 'package:open_filex/open_filex.dart';

class ReportFolderScreen extends StatefulWidget {
  final Directory folder;

  const ReportFolderScreen({super.key, required this.folder});

  @override
  State<ReportFolderScreen> createState() => _ReportFolderScreenState();
}

class _ReportFolderScreenState extends State<ReportFolderScreen> {
  List<File> _files = [];

  @override
  void initState() {
    super.initState();
    _loadFiles();
  }

  void _loadFiles() {
    final children = widget.folder.listSync();
    _files = children.whereType<File>().where((f) {
      final ext = f.path.split('.').last.toLowerCase();
      return ext == 'xlsx' || ext == 'xls' || ext == 'csv';
    }).toList();
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final title = widget.folder.path.split(Platform.pathSeparator).last;
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
        elevation: 1,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Text(title),
      ),
      body: Padding(
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 120),
        child: _files.isEmpty
            ? const Center(child: Text('Aucun fichier trouvé dans ce dossier.'))
            : GridView.builder(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 3,
                  childAspectRatio: 0.75,
                  mainAxisSpacing: 18,
                  crossAxisSpacing: 12,
                ),
                itemCount: _files.length,
                itemBuilder: (context, index) {
                  final file = _files[index];
                  final name = file.path.split(Platform.pathSeparator).last;
                  return GestureDetector(
                    onTap: () async {
                      await OpenFilex.open(file.path);
                    },
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Container(
                          width: 72,
                          height: 80,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFE6E6E6)),
                          ),
                          child: Stack(
                            children: [
                              Positioned(
                                right: 8,
                                top: 8,
                                child: Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 6, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF2EBD73),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Text('XLSX',
                                      style: TextStyle(
                                          color: Colors.white, fontSize: 11)),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          name,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontSize: 12),
                        ),
                      ],
                    ),
                  );
                },
              ),
      ),
    );
  }
}
