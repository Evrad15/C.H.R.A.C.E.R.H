
import 'dart:io';
import 'package:excel/excel.dart';
import 'package:intl/intl.dart';
import 'package:path_provider/path_provider.dart';

/// ─────────────────────────────────────────────────────────────────────────────
/// WeeklyReportService
/// Génère des rapports hebdomadaires en JSON pour un employé.
/// Structure sur disque :
///   <appDocuments>/reports/<NomDuMois>/rapport_semaine_<N>_<annee>.json
/// ─────────────────────────────────────────────────────────────────────────────
class WeeklyReportService {
  static final WeeklyReportService _instance =
      WeeklyReportService._internal();
  factory WeeklyReportService() => _instance;
  WeeklyReportService._internal();

  // ── Noms des mois en français ─────────────────────────────────────────────

  static const _frenchMonths = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
  ];

  String _monthName(int month) => _frenchMonths[month - 1];

  // ── Chemins ──────────────────────────────────────────────────────────────────

  Future<Directory> get _reportsBaseDir async {
    final appDir = await getApplicationDocumentsDirectory();
    final dir = Directory('${appDir.path}/reports');
    if (!await dir.exists()) await dir.create(recursive: true);
    return dir;
  }

  Future<Directory> _monthDir(DateTime date) async {
    final base = await _reportsBaseDir;
    final monthLabel = _monthName(date.month);
    final dir = Directory('${base.path}/$monthLabel');
    if (!await dir.exists()) await dir.create(recursive: true);
    return dir;
  }

  // ── Utilitaires semaine ───────────────────────────────────────────────────

  /// Numéro de semaine ISO 8601
  int _weekNumber(DateTime date) {
    final startOfYear = DateTime(date.year, 1, 1);
    final diff = date.difference(startOfYear);
    return ((diff.inDays + startOfYear.weekday) / 7).ceil();
  }

  /// Lundi de la semaine contenant [date]
  DateTime _mondayOf(DateTime date) {
    return date.subtract(Duration(days: date.weekday - 1));
  }

  /// Vendredi de la semaine contenant [date]
  DateTime _fridayOf(DateTime date) {
    final monday = _mondayOf(date);
    return monday.add(const Duration(days: 4));
  }

  // ── Création d'un rapport ─────────────────────────────────────────────────

  /// Génère le rapport de la semaine courante à partir des données de
  /// pointages [history] (format identique à l'API dashboard).
  Future<File> generateWeeklyReport({
    required Map<String, dynamic> employee,
    required List<dynamic> history,
    DateTime? forDate,
  }) async {
    final date = forDate ?? DateTime.now();
    final monday = _mondayOf(date);
    final friday = _fridayOf(date);
    final weekNum = _weekNumber(date);

    // Filtre les pointages de la semaine
    final fmt = DateFormat('yyyy-MM-dd');
    final weekEntries = history.where((h) {
      try {
        final d = DateTime.parse(h['date'].toString());
        return !d.isBefore(monday) && !d.isAfter(friday);
      } catch (_) {
        return false;
      }
    }).toList();

    // Compte les statuts
    int presences = 0, retards = 0, absences = 0;
    for (final entry in weekEntries) {
      final status = (entry['status'] ?? '').toString().toUpperCase();
      if (status == 'PRESENT') {
        presences++;
      } else if (status == 'RETARD') {
        retards++;
      } else if (status == 'ABSENT') {
        absences++;
      }
    }

    final dir = await _monthDir(date);
    final excelFileName = 'rapport_semaine_${weekNum}_${date.year}.xlsx';
    final excelFile = File('${dir.path}/$excelFileName');

    // Écriture du fichier Excel (.xlsx)
    final excel = Excel.createExcel();
    final sheetName = 'Semaine $weekNum';
    final defaultSheet = excel.getDefaultSheet() ?? 'Sheet1';
    excel.rename(defaultSheet, sheetName);
    final sheet = excel[sheetName];

    // Titre
    sheet.appendRow([TextCellValue('RAPPORT HEBDOMADAIRE DE PRÉSENCE')]);
    sheet.appendRow([TextCellValue('Semaine $weekNum - ${date.year}')]);
    sheet.appendRow([TextCellValue('')]);

    // Employé
    sheet.appendRow([TextCellValue('INFORMATIONS EMPLOYÉ')]);
    sheet.appendRow([TextCellValue('Nom complet :'), TextCellValue('${employee['first_name']} ${employee['last_name']}')]);
    sheet.appendRow([TextCellValue('Matricule :'), TextCellValue('${employee['matricule']}')]);
    sheet.appendRow([TextCellValue('Département :'), TextCellValue('${employee['departement'] ?? ''}')]);
    sheet.appendRow([TextCellValue('')]);

    // Période
    sheet.appendRow([TextCellValue('PÉRIODE')]);
    sheet.appendRow([TextCellValue('Du :'), TextCellValue(fmt.format(monday))]);
    sheet.appendRow([TextCellValue('Au :'), TextCellValue(fmt.format(friday))]);
    sheet.appendRow([TextCellValue('Généré le :'), TextCellValue(DateTime.now().toIso8601String())]);
    sheet.appendRow([TextCellValue('')]);

    // Résumé
    sheet.appendRow([TextCellValue('RÉSUMÉ DE LA SEMAINE')]);
    sheet.appendRow([TextCellValue('Jours Présents :'), IntCellValue(presences)]);
    sheet.appendRow([TextCellValue('Jours Retards :'), IntCellValue(retards)]);
    sheet.appendRow([TextCellValue('Jours Absents :'), IntCellValue(absences)]);
    final rate = weekEntries.isEmpty ? 0 : (presences / 5 * 100).round().clamp(0, 100);
    sheet.appendRow([TextCellValue('Taux d\'Assiduité :'), TextCellValue('$rate%')]);
    sheet.appendRow([TextCellValue('')]);

    // Détails
    sheet.appendRow([
      TextCellValue('Date'),
      TextCellValue('Heure de pointage'),
      TextCellValue('Statut'),
      TextCellValue('En retard ?'),
    ]);

    for (final entry in weekEntries) {
      sheet.appendRow([
        TextCellValue((entry['date'] ?? '').toString()),
        TextCellValue((entry['time'] ?? '—').toString()),
        TextCellValue((entry['status'] ?? '').toString()),
        TextCellValue(entry['is_late'] == true ? 'Oui' : 'Non'),
      ]);
    }

    final excelBytes = excel.save();
    if (excelBytes == null) {
      throw Exception('Impossible de générer le fichier Excel (bytes nuls)');
    }
    await excelFile.writeAsBytes(excelBytes, flush: true);

    return excelFile;
  }

  // ── Les rapports d'exemple ont été retirés ───────────────────────────────

  // ── Lecture ───────────────────────────────────────────────────────────────

  /// Liste tous les dossiers-mois dans le répertoire reports
  Future<List<Directory>> listMonthFolders() async {
    final base = await _reportsBaseDir;
    if (!await base.exists()) return [];
    return base
        .listSync()
        .whereType<Directory>()
        .toList()
      ..sort((a, b) {
        // Trie par ordre des mois
        final aName = a.path.split(Platform.pathSeparator).last;
        final bName = b.path.split(Platform.pathSeparator).last;
        final aIdx = _frenchMonths.indexOf(aName);
        final bIdx = _frenchMonths.indexOf(bName);
        return aIdx.compareTo(bIdx);
      });
  }

  /// Liste tous les fichiers XLSX de rapports dans un dossier-mois
  Future<List<WeeklyReportFile>> listReportsInFolder(
      Directory folder) async {
    if (!await folder.exists()) return [];
    final files = folder
        .listSync()
        .whereType<File>()
        .where((f) => f.path.endsWith('.xlsx'))
        .toList();

    final result = <WeeklyReportFile>[];
    for (final f in files) {
      try {
        final data = await _parseExcelFile(f);
        result.add(WeeklyReportFile(file: f, data: data));
      } catch (e) {
        // ignore: avoid_print
        print('Erreur lors de la lecture Excel pour $f : $e');
        result.add(WeeklyReportFile(file: f, data: {}));
      }
    }
    result.sort((a, b) {
      final aW = a.weekNumber;
      final bW = b.weekNumber;
      return bW.compareTo(aW); // Plus récent en premier
    });
    return result;
  }

  /// Lit un rapport Excel
  Future<Map<String, dynamic>> readReport(File file) async {
    return _parseExcelFile(file);
  }

  dynamic _parseCellValue(CellValue? val) {
    if (val == null) return null;
    if (val is TextCellValue) return val.value;
    if (val is IntCellValue) return val.value;
    if (val is DoubleCellValue) return val.value;
    if (val is BoolCellValue) return val.value;
    try {
      return (val as dynamic).value;
    } catch (_) {
      return val.toString();
    }
  }

  CellValue? _getCell(Sheet sheet, int r, int c) {
    if (r < 0 || r >= sheet.rows.length) return null;
    final row = sheet.rows[r];
    if (c < 0 || c >= row.length) return null;
    return row[c]?.value;
  }

  Future<Map<String, dynamic>> _parseExcelFile(File f) async {
    final bytes = await f.readAsBytes();
    final excel = Excel.decodeBytes(bytes);
    if (excel.tables.isEmpty) return {};
    final sheet = excel.tables.values.first;

    // Extraire la semaine et l'année du nom du fichier
    final fileName = f.path.split(Platform.pathSeparator).last;
    final match = RegExp(r'rapport_semaine_(\d+)_(\d+)').firstMatch(fileName);
    int weekNum = 0;
    int year = 0;
    if (match != null) {
      weekNum = int.tryParse(match.group(1) ?? '') ?? 0;
      year = int.tryParse(match.group(2) ?? '') ?? 0;
    }

    final employeeName = _parseCellValue(_getCell(sheet, 4, 1))?.toString() ?? '';
    final matricule = _parseCellValue(_getCell(sheet, 5, 1))?.toString() ?? '';
    final dept = _parseCellValue(_getCell(sheet, 6, 1))?.toString() ?? '';

    final dateDu = _parseCellValue(_getCell(sheet, 9, 1))?.toString() ?? '';
    final dateAu = _parseCellValue(_getCell(sheet, 10, 1))?.toString() ?? '';
    final genereLe = _parseCellValue(_getCell(sheet, 11, 1))?.toString() ?? '';

    final pCount = int.tryParse(_parseCellValue(_getCell(sheet, 14, 1))?.toString() ?? '') ?? 0;
    final rCount = int.tryParse(_parseCellValue(_getCell(sheet, 15, 1))?.toString() ?? '') ?? 0;
    final aCount = int.tryParse(_parseCellValue(_getCell(sheet, 16, 1))?.toString() ?? '') ?? 0;
    final tauxText = _parseCellValue(_getCell(sheet, 17, 1))?.toString() ?? '0%';
    final rawTaux = int.tryParse(tauxText.replaceAll('%', '')) ?? 0;
    final taux = rawTaux > 100 ? 100 : rawTaux;

    final detailsList = <Map<String, dynamic>>[];
    for (int r = 20; r < sheet.rows.length; r++) {
      final dDate = _parseCellValue(_getCell(sheet, r, 0))?.toString();
      if (dDate == null || dDate.trim().isEmpty) continue;
      final dTime = _parseCellValue(_getCell(sheet, r, 1))?.toString() ?? '—';
      final dStatus = _parseCellValue(_getCell(sheet, r, 2))?.toString() ?? '—';
      final dIsLateStr = _parseCellValue(_getCell(sheet, r, 3))?.toString() ?? 'Non';
      detailsList.add({
        'date': dDate,
        'time': dTime,
        'status': dStatus,
        'is_late': dIsLateStr.toLowerCase() == 'oui',
      });
    }

    return {
      'meta': {
        'titre': 'Rapport hebdomadaire – Semaine $weekNum',
        'employe': {
          'nom': employeeName,
          'matricule': matricule,
          'departement': dept,
        },
        'periode': {
          'semaine': weekNum,
          'annee': year,
          'du': dateDu,
          'au': dateAu,
        },
        'genereLE': genereLe,
      },
      'resume': {
        'joursPresent': pCount,
        'joursRetard': rCount,
        'joursAbsent': aCount,
        'totalJours': detailsList.length,
        'tauxAssiduitePC': taux,
      },
      'detail': detailsList,
    };
  }

  // ── Génération automatique à partir des pointages ──────────────────────────

  /// Génère tous les rapports hebdomadaires à partir de l'historique complet [history].
  Future<void> generateAllReportsFromHistory({
    required Map<String, dynamic> employee,
    required List<dynamic> history,
  }) async {
    if (history.isEmpty) return;

    // Collecter les semaines uniques pour générer un rapport par semaine
    final Set<String> processedWeeks = {};
    for (final entry in history) {
      try {
        final date = DateTime.parse(entry['date'].toString());
        final weekNum = _weekNumber(date);
        final year = date.year;
        final weekKey = '${year}_$weekNum';
        
        if (!processedWeeks.contains(weekKey)) {
          processedWeeks.add(weekKey);
          
          await generateWeeklyReport(
            employee: employee,
            history: history,
            forDate: date,
          );
        }
      } catch (e) {
        // ignore: avoid_print
        print('Erreur lors de la génération du rapport historique: $e');
      }
    }
  }

  // ── Génération automatique (fin de semaine) ────────────────────────────────

  /// Vérifie s'il faut générer le rapport de fin de semaine.
  /// À appeler au démarrage et/ou via un timer quotidien.
  Future<void> checkAndGenerateIfWeekEnd({
    required Map<String, dynamic> employee,
    required List<dynamic> history,
  }) async {
    final now = DateTime.now();
    // Vendredi = weekday 5
    if (now.weekday == DateTime.friday && now.hour >= 17) {
      final weekNum = _weekNumber(now);
      final dir = await _monthDir(now);
      final file =
          File('${dir.path}/rapport_semaine_${weekNum}_${now.year}.xlsx');
      if (!await file.exists()) {
        await generateWeeklyReport(
          employee: employee,
          history: history,
          forDate: now,
        );
      }
    }
  }
}

/// Wrapper pratique pour un rapport XLSX sur disque
class WeeklyReportFile {
  final File file;
  final Map<String, dynamic> data;

  WeeklyReportFile({required this.file, required this.data});

  String get fileName =>
      file.path.split(Platform.pathSeparator).last;

  String get title {
    try {
      return data['meta']['titre'] as String;
    } catch (_) {
      return fileName.replaceAll('_', ' ').replaceAll('.xlsx', '');
    }
  }

  String get periode {
    try {
      final p = data['meta']['periode'] as Map<String, dynamic>;
      return 'du ${p['du']} au ${p['au']}';
    } catch (_) {
      return '';
    }
  }

  int get weekNumber {
    try {
      return data['meta']['periode']['semaine'] as int;
    } catch (_) {
      return 0;
    }
  }

  int get presences {
    try {
      return data['resume']['joursPresent'] as int;
    } catch (_) {
      return 0;
    }
  }

  int get retards {
    try {
      return data['resume']['joursRetard'] as int;
    } catch (_) {
      return 0;
    }
  }

  int get absences {
    try {
      return data['resume']['joursAbsent'] as int;
    } catch (_) {
      return 0;
    }
  }

  int get tauxAssiduite {
    try {
      return data['resume']['tauxAssiduitePC'] as int;
    } catch (_) {
      return 0;
    }
  }
}
