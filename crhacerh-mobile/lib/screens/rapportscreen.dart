import 'dart:io';

import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'report_folder_screen.dart';

class RapportsScreen extends StatefulWidget {
  const RapportsScreen({super.key});

  @override
  State<RapportsScreen> createState() => _RapportsScreenState();
}

class _RapportsScreenState extends State<RapportsScreen> {
  late Directory _reportsDir;
  List<Directory> _folders = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _initReports();
  }

  Future<void> _initReports() async {
    setState(() => _loading = true);
    final appDir = await getApplicationDocumentsDirectory();
    _reportsDir = Directory('${appDir.path}/reports');
    if (!await _reportsDir.exists()) {
      await _reportsDir.create(recursive: true);
    }
    await _refreshFolders();
  }

  Future<void> _refreshFolders() async {
    final children = _reportsDir.listSync();
    _folders = children.whereType<Directory>().toList();
    setState(() => _loading = false);
  }

  String _labelFromName(String name) {
    // Exemple simple : remplace les underscores et garde le texte
    return name.replaceAll('_', ' ');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Rapports'),
        elevation: 1,
        backgroundColor: Colors.white,
        foregroundColor: Colors.black,
      ),
      body: RefreshIndicator(
        onRefresh: _refreshFolders,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _folders.isEmpty
                ? ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    children: const [
                      SizedBox(height: 60),
                      Center(child: Text('Aucun dossier de rapports.'))
                    ],
                  )
                : GridView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 20, 16, 120),
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 3,
                      childAspectRatio: 0.9,
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 18,
                    ),
                    itemCount: _folders.length,
                    itemBuilder: (context, index) {
                      final dir = _folders[index];
                      final name = dir.path.split(Platform.pathSeparator).last;
                      return GestureDetector(
                        onTap: () async {
                          await Navigator.of(context).push(MaterialPageRoute(
                              builder: (_) => ReportFolderScreen(folder: dir)));
                          await _refreshFolders();
                        },
                        child: Column(
                          children: [
                            Container(
                              width: 72,
                              height: 56,
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFD54F),
                                borderRadius: BorderRadius.circular(8),
                                boxShadow: [
                                  BoxShadow(
                                      color: Colors.black.withOpacity(0.12),
                                      blurRadius: 6,
                                      offset: const Offset(0, 4)),
                                ],
                              ),
                              child: Align(
                                alignment: Alignment.topLeft,
                                child: Container(
                                  margin: const EdgeInsets.all(6),
                                  width: 34,
                                  height: 16,
                                  decoration: BoxDecoration(
                                      color: const Color(0xFFFFE082),
                                      borderRadius: BorderRadius.circular(4)),
                                ),
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              _labelFromName(name),
                              textAlign: TextAlign.center,
                              style: const TextStyle(fontSize: 12),
                            )
                          ],
                        ),
                      );
                    },
                  ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _refreshFolders,
        child: const Icon(Icons.search),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
    );
  }
}
