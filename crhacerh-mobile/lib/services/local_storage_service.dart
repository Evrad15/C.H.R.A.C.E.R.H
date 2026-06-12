import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'auth_service.dart';

/// ─────────────────────────────────────────────────────────────────────────────
/// LocalStorageService
/// Gère le stockage local en JSON et la synchronisation automatique avec le
/// serveur. Toutes les données sont persistées dans
///   <appDocuments>/local_data/
/// ─────────────────────────────────────────────────────────────────────────────
class LocalStorageService {
  static final LocalStorageService _instance = LocalStorageService._internal();
  factory LocalStorageService() => _instance;
  LocalStorageService._internal();

  static const _kLastSyncKey = 'last_sync_timestamp';
  static const _kSyncIntervalMinutes = 15;

  final AuthService _api = AuthService();
  Timer? _syncTimer;
  bool _isSyncing = false;

  // ── Chemins ──────────────────────────────────────────────────────────────────

  Future<Directory> get _baseDir async {
    final appDir = await getApplicationDocumentsDirectory();
    final dir = Directory('${appDir.path}/local_data');
    if (!await dir.exists()) await dir.create(recursive: true);
    return dir;
  }

  Future<File> _jsonFile(String name) async {
    final base = await _baseDir;
    return File('${base.path}/$name.json');
  }

  // ── Lecture / Écriture générique ──────────────────────────────────────────

  Future<Map<String, dynamic>> _readJson(String name) async {
    try {
      final file = await _jsonFile(name);
      if (!await file.exists()) return {};
      final raw = await file.readAsString();
      return jsonDecode(raw) as Map<String, dynamic>;
    } catch (_) {
      return {};
    }
  }

  Future<void> _writeJson(String name, Map<String, dynamic> data) async {
    final file = await _jsonFile(name);
    await file.writeAsString(
      const JsonEncoder.withIndent('  ').convert(data),
      flush: true,
    );
  }

  // ── Employé local ─────────────────────────────────────────────────────────

  /// Sauvegarde les données d'un employé en local
  Future<void> saveEmployee(Map<String, dynamic> employee) async {
    await _writeJson('employee_${employee['id']}', {
      'employee': employee,
      'savedAt': DateTime.now().toIso8601String(),
    });
    // Aussi dans SharedPreferences pour un accès rapide au démarrage
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('cached_employee', jsonEncode(employee));
  }

  /// Lit les données de l'employé depuis le JSON local
  Future<Map<String, dynamic>?> loadEmployee(int employeeId) async {
    final data = await _readJson('employee_$employeeId');
    return data['employee'] as Map<String, dynamic>?;
  }

  // ── Historique de pointages ───────────────────────────────────────────────

  /// Sauvegarde l'historique de pointages + stats d'un employé
  Future<void> saveDashboardData(
      int employeeId, Map<String, dynamic> dashboardData) async {
    final existing = await _readJson('dashboard_$employeeId');
    await _writeJson('dashboard_$employeeId', {
      ...existing,
      'data': dashboardData,
      'updatedAt': DateTime.now().toIso8601String(),
    });
  }

  /// Lit le dashboard depuis le JSON local (fallback hors-ligne)
  Future<Map<String, dynamic>?> loadDashboardData(int employeeId) async {
    final data = await _readJson('dashboard_$employeeId');
    return data['data'] as Map<String, dynamic>?;
  }

  /// Ajoute un pointage local en attente de sync
  Future<void> addPendingPointage(Map<String, dynamic> pointage) async {
    final data = await _readJson('pending_pointages');
    final List<dynamic> pending =
        (data['items'] as List<dynamic>?) ?? [];
    pending.add({
      ...pointage,
      'createdLocally': DateTime.now().toIso8601String(),
      'synced': false,
    });
    await _writeJson('pending_pointages', {'items': pending});
  }

  /// Retourne les pointages locaux non encore synchronisés
  Future<List<Map<String, dynamic>>> getPendingPointages() async {
    final data = await _readJson('pending_pointages');
    final List<dynamic> items = (data['items'] as List<dynamic>?) ?? [];
    return items
        .cast<Map<String, dynamic>>()
        .where((p) => p['synced'] == false)
        .toList();
  }

  /// Marque un pointage local comme synchronisé
  Future<void> markPointageSynced(int index) async {
    final data = await _readJson('pending_pointages');
    final List<dynamic> items = (data['items'] as List<dynamic>?) ?? [];
    if (index < items.length) {
      (items[index] as Map<String, dynamic>)['synced'] = true;
    }
    await _writeJson('pending_pointages', {'items': items});
  }

  // ── Synchronisation automatique ───────────────────────────────────────────

  /// Démarre la sync automatique toutes les [_kSyncIntervalMinutes] minutes
  void startAutoSync(int employeeId) {
    _syncTimer?.cancel();
    _syncTimer = Timer.periodic(
      const Duration(minutes: _kSyncIntervalMinutes),
      (_) => syncNow(employeeId),
    );
    // Sync immédiate au démarrage
    syncNow(employeeId);
  }

  void stopAutoSync() {
    _syncTimer?.cancel();
    _syncTimer = null;
  }

  /// Synchronisation manuelle / automatique
  Future<SyncResult> syncNow(int employeeId) async {
    if (_isSyncing) return SyncResult.alreadyRunning;
    _isSyncing = true;

    try {
      // 1. Récupère le dashboard depuis le serveur
      final result = await _api.fetchDashboardData(employeeId);
      if (result['success'] == true) {
        await saveDashboardData(employeeId, result['data']);
        await _updateLastSync();
        _isSyncing = false;
        return SyncResult.success;
      }

      // 2. Échec réseau → on travaille hors-ligne, pas d'erreur fatale
      _isSyncing = false;
      return SyncResult.networkError;
    } catch (e) {
      _isSyncing = false;
      return SyncResult.error;
    }
  }

  Future<void> _updateLastSync() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
        _kLastSyncKey, DateTime.now().toIso8601String());
  }

  Future<DateTime?> getLastSyncTime() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kLastSyncKey);
    if (raw == null) return null;
    return DateTime.tryParse(raw);
  }

  /// Indique si une sync est nécessaire (> 15 min depuis la dernière)
  Future<bool> needsSync() async {
    final last = await getLastSyncTime();
    if (last == null) return true;
    return DateTime.now().difference(last).inMinutes >= _kSyncIntervalMinutes;
  }
}

enum SyncResult { success, networkError, error, alreadyRunning }
