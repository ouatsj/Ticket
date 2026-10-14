import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'services/api_client.dart';
import 'services/auth_state.dart';
import 'services/update_service.dart';
import 'theme/tei_theme.dart';
import 'screens/shell_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const TeiApp());
}

class TeiApp extends StatelessWidget {
  const TeiApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider(create: (_) => ApiClient()),
        Provider(create: (_) => UpdateService()),
        ChangeNotifierProvider(
          create: (ctx) => AuthState(ctx.read<ApiClient>())..restore(),
        ),
      ],
      child: MaterialApp(
        title: 'Rakieta Lab',
        debugShowCheckedModeBanner: false,
        theme: TeiTheme.light(),
        locale: const Locale('fr', 'FR'),
        supportedLocales: const [Locale('fr', 'FR')],
        localizationsDelegates: const [
          GlobalMaterialLocalizations.delegate,
          GlobalWidgetsLocalizations.delegate,
          GlobalCupertinoLocalizations.delegate,
        ],
        home: Consumer<AuthState>(
          builder: (context, auth, _) {
            if (auth.loading) {
              return const _Splash();
            }
            // Pas d'écran de connexion : accueil direct (session silencieuse).
            return const ShellScreen();
          },
        ),
      ),
    );
  }
}

class _Splash extends StatelessWidget {
  const _Splash();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.asset(
              'assets/branding/rakieta_lab_logo.png',
              width: 240,
              fit: BoxFit.contain,
            ),
            const SizedBox(height: 24),
            const Text(
              'Rakieta Lab',
              style: TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: Color(0xFF0B3D91),
              ),
            ),
            const SizedBox(height: 16),
            const CircularProgressIndicator(color: Color(0xFF0B3D91)),
          ],
        ),
      ),
    );
  }
}
