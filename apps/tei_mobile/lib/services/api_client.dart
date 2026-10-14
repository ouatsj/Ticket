import 'dart:convert';
import 'package:http/http.dart' as http;

/// URL de base de l'API séparée (surchargeable via --dart-define=TEI_API_BASE=...).
class ApiClient {
  ApiClient({String? baseUrl})
      : baseUrl = baseUrl ??
            const String.fromEnvironment(
              'TEI_API_BASE',
              defaultValue: 'http://127.0.0.1:8091/v1',
            );

  final String baseUrl;
  String? accessToken;

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (accessToken != null) 'Authorization': 'Bearer $accessToken',
      };

  Future<Map<String, dynamic>> login(String username, String password) async {
    final res = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: _headers,
      body: jsonEncode({'username': username, 'password': password}),
    );
    return _decode(res);
  }

  Future<Map<String, dynamic>> me() async {
    final res = await http.get(Uri.parse('$baseUrl/me'), headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> dashboardSummary({
    required int ekey,
    int? company,
    String? date,
  }) async {
    final q = {
      'ekey': '$ekey',
      if (company != null) 'company': '$company',
      if (date != null) 'date': date,
    };
    final uri = Uri.parse('$baseUrl/dashboard/summary').replace(queryParameters: q);
    final res = await http.get(uri, headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> departures({
    required int ekey,
    int? company,
    String? date,
  }) async {
    final q = {
      'ekey': '$ekey',
      if (company != null) 'company': '$company',
      if (date != null) 'date': date,
    };
    final uri = Uri.parse('$baseUrl/departures').replace(queryParameters: q);
    final res = await http.get(uri, headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> financeSales({
    required int ekey,
    required String from,
    required String to,
    int? company,
  }) async {
    final q = {
      'ekey': '$ekey',
      'from': from,
      'to': to,
      if (company != null) 'company': '$company',
    };
    final uri = Uri.parse('$baseUrl/finance/sales').replace(queryParameters: q);
    final res = await http.get(uri, headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> companies({required int ekey}) async {
    final uri = Uri.parse('$baseUrl/companies').replace(queryParameters: {'ekey': '$ekey'});
    final res = await http.get(uri, headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> insights({
    required int ekey,
    int? company,
    String? date,
  }) async {
    final q = {
      'ekey': '$ekey',
      if (company != null) 'company': '$company',
      if (date != null) 'date': date,
    };
    final uri = Uri.parse('$baseUrl/insights').replace(queryParameters: q);
    final res = await http.get(uri, headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> notifications({
    required int ekey,
    String? categorie,
    String? niveau,
  }) async {
    final q = {
      'ekey': '$ekey',
      if (categorie != null) 'categorie': categorie,
      if (niveau != null) 'niveau': niveau,
    };
    final uri = Uri.parse('$baseUrl/notifications').replace(queryParameters: q);
    final res = await http.get(uri, headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> notificationPrefs() async {
    final res = await http.get(Uri.parse('$baseUrl/notifications/prefs'), headers: _headers);
    return _decode(res);
  }

  Future<Map<String, dynamic>> saveNotificationPrefs(Map<String, dynamic> body) async {
    final res = await http.put(
      Uri.parse('$baseUrl/notifications/prefs'),
      headers: _headers,
      body: jsonEncode(body),
    );
    return _decode(res);
  }

  Future<Map<String, dynamic>> scanAlerts({required int ekey, String? date}) async {
    final res = await http.post(
      Uri.parse('$baseUrl/alerts/scan'),
      headers: _headers,
      body: jsonEncode({'ekey': ekey, if (date != null) 'date': date}),
    );
    return _decode(res);
  }

  Map<String, dynamic> _decode(http.Response res) {
    final body = res.body.isEmpty ? <String, dynamic>{} : jsonDecode(res.body);
    if (res.statusCode >= 400) {
      final msg = body is Map && body['error'] != null
          ? body['error'].toString()
          : 'Erreur HTTP ${res.statusCode}';
      throw ApiException(res.statusCode, msg);
    }
    return Map<String, dynamic>.from(body as Map);
  }
}

class ApiException implements Exception {
  ApiException(this.status, this.message);
  final int status;
  final String message;
  @override
  String toString() => message;
}
