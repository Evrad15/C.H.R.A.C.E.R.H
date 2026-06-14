import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';

// ═══════════════════════════════════════════════════════════════════════════════
// ThemeManager — persistance du thème choisi par l'utilisateur
// ═══════════════════════════════════════════════════════════════════════════════
class ThemeManager {
  static final ValueNotifier<bool> isDarkNotifier = ValueNotifier<bool>(true);

  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final isDark = prefs.getBool('isDark') ?? true;
    isDarkNotifier.value = isDark;
    SystemChrome.setSystemUIOverlayStyle(AppColors.overlayStyle);
  }

  static Future<void> toggleTheme() async {
    isDarkNotifier.value = !isDarkNotifier.value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('isDark', isDarkNotifier.value);
    SystemChrome.setSystemUIOverlayStyle(AppColors.overlayStyle);
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// AppColors — système de tokens premium (dark-first)
// Palette : Indigo #6366F1 / Violet #8B5CF6 / Dark Navy
// ═══════════════════════════════════════════════════════════════════════════════
class AppColors {
  static bool get _isDark => ThemeManager.isDarkNotifier.value;

  // ── Fonds ────────────────────────────────────────────────────────────────────
  /// Fond principal de l'application
  static Color get bg =>
      _isDark ? const Color(0xFF060818) : const Color(0xFFF0F2FF);

  /// Fond des cards et surfaces secondaires
  static Color get card =>
      _isDark ? const Color(0xFF0E1128) : const Color(0xFFFFFFFF);

  /// Surface légèrement élevée (sections, groupes)
  static Color get surface =>
      _isDark ? const Color(0xFF0E1128) : const Color(0xFFFFFFFF);

  /// Surface élevée (modals, overlays légers)
  static Color get surfaceHigh =>
      _isDark ? const Color(0xFF161A35) : const Color(0xFFF5F7FF);

  // ── Statut Bar ───────────────────────────────────────────────────────────────
  static SystemUiOverlayStyle get overlayStyle =>
      _isDark ? SystemUiOverlayStyle.light : SystemUiOverlayStyle.dark;

  // ── Couleurs d'accent ────────────────────────────────────────────────────────
  /// Indigo — couleur primaire / CTA
  static const Color accent = Color(0xFF6366F1);

  /// Violet clair — hover, icônes secondaires
  static const Color accentSoft = Color(0xFF818CF8);

  /// Alias pour compatibilité
  static Color get accentSft => accentSoft;

  // ── Couleurs sémantiques ─────────────────────────────────────────────────────
  /// Vert — présence, succès
  static Color get green =>
      _isDark ? const Color(0xFF10B981) : const Color(0xFF059669);

  /// Orange — retards, avertissements
  static Color get orange =>
      _isDark ? const Color(0xFFF59E0B) : const Color(0xFFD97706);

  /// Rouge — absences, erreurs, danger
  static Color get red =>
      _isDark ? const Color(0xFFEF4444) : const Color(0xFFDC2626);

  // ── Glows (halos lumineux) ───────────────────────────────────────────────────
  static Color get accentGlow => accent.withValues(alpha: 0.28);
  static Color get greenGlow  => green.withValues(alpha: 0.28);
  static Color get redGlow    => red.withValues(alpha: 0.28);
  static Color get orangeGlow => orange.withValues(alpha: 0.28);

  // ── Textes ───────────────────────────────────────────────────────────────────
  /// Texte principal
  static Color get textPri =>
      _isDark ? const Color(0xFFF8FAFC) : const Color(0xFF0F172A);

  /// Texte secondaire / placeholders
  static Color get textSec =>
      _isDark ? const Color(0xFF64748B) : const Color(0xFF64748B);

  // ── Inputs & borders ─────────────────────────────────────────────────────────
  static Color get inputBg =>
      _isDark ? const Color(0xFF161A35) : const Color(0xFFEEF0FF);

  static Color get border =>
      _isDark ? Colors.white.withValues(alpha: 0.06) : Colors.black.withValues(alpha: 0.07);

  // ── Messages chat ────────────────────────────────────────────────────────────
  static const Color meBubble  = Color(0xFF6366F1);
  static Color get themBubble  =>
      _isDark ? const Color(0xFF161A35) : const Color(0xFFEEF0FF);

  // ── Gradients statiques ───────────────────────────────────────────────────────
  static const LinearGradient accentGradient = LinearGradient(
    colors: [Color(0xFF6366F1), Color(0xFF8B5CF6)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient heroGradient = LinearGradient(
    colors: [Color(0xFF0E1128), Color(0xFF1E1B4B)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient bgGradient = LinearGradient(
    colors: [Color(0xFF060818), Color(0xFF0E1128)],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );
}
