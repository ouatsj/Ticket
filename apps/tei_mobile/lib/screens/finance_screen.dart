import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../services/api_client.dart';
import '../services/auth_state.dart';
import '../theme/tei_theme.dart';

class FinanceScreen extends StatefulWidget {
  const FinanceScreen({super.key});

  @override
  State<FinanceScreen> createState() => _FinanceScreenState();
}

class _FinanceScreenState extends State<FinanceScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _data;

  static const _from = '2026-09-01';
  static const _to = '2026-09-13';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    final ekey = context.read<AuthState>().primaryEkey;
    if (ekey == null) {
      setState(() {
        _loading = false;
        _error = 'Aucun périmètre entreprise';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await context.read<ApiClient>().financeSales(
            ekey: ekey,
            from: _from,
            to: _to,
          );
      if (!mounted) return;
      setState(() {
        _data = data;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  String _money(dynamic v) {
    if (v == null) return 'n/d';
    final n = (v is num) ? v.toDouble() : double.tryParse('$v') ?? 0;
    return NumberFormat.currency(locale: 'fr_FR', symbol: 'F', decimalDigits: 0).format(n);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Finances'),
        actions: [
          IconButton(onPressed: _loading ? null : _load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Text(_error!))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      Text(
                        'Période $_from → $_to · source ${_data?['source']}',
                        style: const TextStyle(color: TeiColors.muted, fontSize: 12),
                      ),
                      const SizedBox(height: 12),
                      _row('CA billets', _money(_data?['ca_billets']), TeiColors.normal),
                      _row('Billets', '${_data?['billets'] ?? 0}', TeiColors.ink),
                      _row('CA bagages', _money(_data?['ca_bagages']), TeiColors.ink),
                      _row('CA courrier', _money(_data?['ca_courrier']), TeiColors.ink),
                      _row('Recettes caisse', _money(_data?['recettes_caisse']), TeiColors.ink),
                      _row('Écart caisse', _money(_data?['ecart_caisse']), TeiColors.vigilance),
                      const SizedBox(height: 16),
                      const Text('Par compagnie', style: TextStyle(fontWeight: FontWeight.w700)),
                      ...(((_data?['by_company'] as List?) ?? []).map((c) {
                        final m = Map<String, dynamic>.from(c as Map);
                        return ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: Text('${m['nom_compagnie']}'),
                          subtitle: Text('${m['billets']} billets'),
                          trailing: Text(_money(m['ca']), style: const TextStyle(fontWeight: FontWeight.w600)),
                        );
                      })),
                      const SizedBox(height: 12),
                      const Text('Top lignes', style: TextStyle(fontWeight: FontWeight.w700)),
                      ...(((_data?['by_ligne'] as List?) ?? []).take(10).map((c) {
                        final m = Map<String, dynamic>.from(c as Map);
                        return ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: Text('${m['nom_ligne']}'),
                          trailing: Text(_money(m['ca'])),
                        );
                      })),
                    ],
                  ),
                ),
    );
  }

  Widget _row(String label, String value, Color accent) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Container(width: 4, height: 28, color: accent),
        title: Text(label),
        trailing: Text(value, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
      ),
    );
  }
}
