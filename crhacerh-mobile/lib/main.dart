import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'screens/matriculescreen.dart';
import 'home_screen.dart';

void main() async {
  // Assure l'initialisation des bindings Flutter avant l'asynchrone
  WidgetsFlutterBinding.ensureInitialized();

  // Vérifier si un employé est déjà enregistré en local
  final prefs = await SharedPreferences.getInstance();
  String? cachedEmployee = prefs.getString('cached_employee');

  Map<String, dynamic>? initialEmployee;
  if (cachedEmployee != null) {
    initialEmployee = jsonDecode(cachedEmployee);
  }

  runApp(MyApp(savedEmployee: initialEmployee));
}

class MyApp extends StatelessWidget {
  final Map<String, dynamic>? savedEmployee;

  const MyApp({super.key, this.savedEmployee});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'C.H.R.A.C.E.R.H',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      // Si un employé est trouvé en cache, on va direct à l'accueil, sinon au Matricule
      home: savedEmployee != null 
          ? HomeScreen(employee: savedEmployee!) 
          : const MatriculeScreen(),
      routes: {
        '/matricule': (context) => const MatriculeScreen(),
      },
    );
  }
}