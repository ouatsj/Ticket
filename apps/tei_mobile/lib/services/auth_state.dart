import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'api_client.dart';

/// Session silencieuse : pas d'écran de login.
/// Bootstrap via --dart-define (staging) ou jeton déjà stocké.
class AuthState extends ChangeNotifier {
  AuthState(this._api);

  final ApiClient _api;
  final _storage = const FlutterSecureStorage();

  static const bootstrapUser = String.fromEnvironment(
    'TEI_BOOTSTRAP_USER',
    defaultValue: 'tei_decideur',
  );
  static const bootstrapPass = String.fromEnvironment(
    'TEI_BOOTSTRAP_PASS',
    defaultValue: 'TeiDecideur!2026',
  );

  bool loading = true;
  bool isAuthenticated = false;
  String? username;
  List<dynamic> scopes = [];
  String? refreshToken;
  String? error;

  Future<void> restore() async {
    loading = true;
    notifyListeners();
    try {
      final access = await _storage.read(key: 'tei_access');
      refreshToken = await _storage.read(key: 'tei_refresh');
      if (access != null && access.isNotEmpty) {
        _api.accessToken = access;
        try {
          final me = await _api.me();
          username = me['username']?.toString();
          scopes = (me['scopes'] as List?) ?? [];
          isAuthenticated = true;
          return;
        } catch (_) {
          // jeton expiré → bootstrap silencieux
        }
      }
      await _silentBootstrap();
    } catch (e) {
      error = e.toString();
      isAuthenticated = false;
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  Future<void> _silentBootstrap() async {
    if (bootstrapUser.isEmpty || bootstrapPass.isEmpty) {
      throw Exception('Configuration session manquante');
    }
    final data = await _api.login(bootstrapUser, bootstrapPass);
    _api.accessToken = data['access_token']?.toString();
    refreshToken = data['refresh_token']?.toString();
    await _storage.write(key: 'tei_access', value: _api.accessToken);
    await _storage.write(key: 'tei_refresh', value: refreshToken);
    final u = data['user'] as Map<String, dynamic>?;
    username = u?['username']?.toString() ?? bootstrapUser;
    scopes = (u?['scopes'] as List?) ?? [];
    isAuthenticated = true;
  }

  Future<void> ensureSession() async {
    if (isAuthenticated && _api.accessToken != null) return;
    await _silentBootstrap();
    notifyListeners();
  }

  Future<void> logout({bool silent = false}) async {
    // Pas de retour login : on re-bootstrap immédiatement.
    await _storage.delete(key: 'tei_access');
    await _storage.delete(key: 'tei_refresh');
    try {
      await _silentBootstrap();
    } catch (e) {
      error = e.toString();
      isAuthenticated = false;
    }
    if (!silent) notifyListeners();
  }

  int? get primaryEkey {
    if (scopes.isEmpty) return null;
    final s = scopes.first;
    if (s is Map && s['ekey'] != null) return int.tryParse('${s['ekey']}');
    return null;
  }
}
