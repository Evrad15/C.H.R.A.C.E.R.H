import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'screens/matriculescreen.dart';
import 'screens/face_register_screen.dart';
import 'home_screen.dart';
import 'theme_manager.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  await ThemeManager.init();

  final prefs = await SharedPreferences.getInstance();
  String? cachedEmployee = prefs.getString('cached_employee');

  Map<String, dynamic>? initialEmployee;
  if (cachedEmployee != null) {
    // Le .cast<String, dynamic>() assure la compatibilité parfaite Android, iOS et Chrome (Web)
    initialEmployee = jsonDecode(cachedEmployee).cast<String, dynamic>();
  }

  runApp(MyApp(savedEmployee: initialEmployee));
}

class MyApp extends StatelessWidget {
  final Map<String, dynamic>? savedEmployee;

  const MyApp({super.key, this.savedEmployee});

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<bool>(
      valueListenable: ThemeManager.isDarkNotifier,
      builder: (context, isDark, _) {
        return MaterialApp(
          title: 'C.H.R.A.C.E.R.H',
          debugShowCheckedModeBanner: false,
          theme: ThemeData(
            primarySwatch: Colors.blue,
            brightness: isDark ? Brightness.dark : Brightness.light,
            appBarTheme: AppBarTheme(
              systemOverlayStyle: AppColors.overlayStyle,
            ),
          ),
          home: savedEmployee != null 
              ? HomeScreen(employee: savedEmployee!) 
              : const MatriculeScreen(),
          routes: {
            '/matricule': (context) => const MatriculeScreen(),
            '/face_register': (context) {
              final employee = ModalRoute.of(context)!.settings.arguments as Map<String, dynamic>;
              return FaceRegisterScreen(employee: employee);
            },
          },
        );
      }
    );
  }
}