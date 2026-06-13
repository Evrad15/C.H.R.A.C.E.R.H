import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ThemeManager {
  static final ValueNotifier<bool> isDarkNotifier = ValueNotifier<bool>(true);

  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final isDark = prefs.getBool('isDark') ?? true;
    isDarkNotifier.value = isDark;
  }

  static Future<void> toggleTheme() async {
    isDarkNotifier.value = !isDarkNotifier.value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('isDark', isDarkNotifier.value);
  }
}

class AppColors {
  static bool get _isDark => ThemeManager.isDarkNotifier.value;

  static Color get bg         => _isDark ? const Color(0xFF0D0D0D) : const Color(0xFFF3F4F6);
  static Color get card       => _isDark ? const Color(0xFF1A1A1A) : const Color(0xFFFFFFFF);
  static Color get accent     => const Color(0xFF6C63FF);
  static Color get accentSoft => const Color(0xFF8B83FF);
  static Color get green      => const Color(0xFF00C48C);
  static Color get orange     => const Color(0xFFFF8C42);
  static Color get red        => const Color(0xFFFF4757);
  static Color get textPri    => _isDark ? const Color(0xFFFFFFFF) : const Color(0xFF1F2937);
  static Color get textSec    => _isDark ? const Color(0xFF9E9E9E) : const Color(0xFF6B7280);
  static Color get accentSft  => const Color(0xFF8B83FF);
  static Color get inputBg    => _isDark ? const Color(0xFF1E1E1E) : const Color(0xFFE5E7EB);
  static Color get meBubble   => const Color(0xFF6C63FF);
  static Color get themBubble => _isDark ? const Color(0xFF2C2C2E) : const Color(0xFFE5E7EB);
  static Color get surface    => _isDark ? const Color(0xFF1A1A1A) : const Color(0xFFFFFFFF);
  static Color get greenGlow  => const Color(0xFF00C48C).withOpacity(0.3);
  static Color get redGlow    => const Color(0xFFFF4757).withOpacity(0.3);
}
