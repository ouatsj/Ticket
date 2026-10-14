import 'package:flutter/material.dart';

/// Marque Rakieta + seuils (vert / orange / rouge).
class TeiColors {
  static const brandBlue = Color(0xFF0B3D91);
  static const brandRed = Color(0xFFE31E24);
  static const normal = Color(0xFF0B3D91);
  static const vigilance = Color(0xFFC77800);
  static const critique = Color(0xFFE31E24);
  static const ink = Color(0xFF1A1C1E);
  static const muted = Color(0xFF5C6370);
  static const surface = Color(0xFFF4F6F8);
  static const card = Color(0xFFFFFFFF);
}

class TeiTheme {
  static ThemeData light() {
    final base = ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      colorScheme: ColorScheme.fromSeed(
        seedColor: TeiColors.brandBlue,
        brightness: Brightness.light,
      ),
      scaffoldBackgroundColor: TeiColors.surface,
    );
    return base.copyWith(
      appBarTheme: const AppBarTheme(
        backgroundColor: TeiColors.card,
        foregroundColor: TeiColors.ink,
        elevation: 0,
        centerTitle: false,
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: TeiColors.card,
        indicatorColor: TeiColors.normal.withOpacity(0.12),
        labelTextStyle: WidgetStateProperty.all(
          const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }
}
