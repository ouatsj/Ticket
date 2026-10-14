import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../services/api_client.dart';
import '../services/auth_state.dart';
import '../theme/tei_theme.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _summary;
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    final auth = context.read<AuthState>();
    if (!auth.isAuthenticated) {
      try {
        await auth.ensureSession();
      } catch (e) {
        if (!mounted) return;
        setState(() {
          _loading = false;
          _error = e.toString();
        });
        return;
      }
    }
    final ekey = auth.primaryEkey;
    if (ekey == null) {
      setState(() {
        _loading = false;
        _error = 'Aucun périmètre entreprise configuré';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      // Staging : dernière période métier riche = 2026-09-13
      final data = await context.read<ApiClient>().dashboardSummary(
            ekey: ekey,
            date: '2026-09-13',
          );
      if (!mounted) return;
      setState(() {
        _summary = data;
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
    final n = (v is num) ? v.toDouble() : double.tryParse('$v') ?? 0;
    return NumberFormat.currency(locale: 'fr_FR', symbol: 'F', decimalDigits: 0).format(n);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthState>();
    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Image.asset(
              'assets/branding/rakieta_lab_logo.png',
              height: 32,
              fit: BoxFit.contain,
            ),
            const SizedBox(width: 10),
            const Flexible(
              child: Text(
                'Rakieta',
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(onPressed: _loading ? null : _load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Padding(padding: const EdgeInsets.all(24), child: Text(_error!, textAlign: TextAlign.center)))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      Text(
                        'Bonjour ${auth.username ?? ''}',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Actualisé : ${_summary?['as_of'] ?? '—'} · source ${_summary?['source'] ?? 'ventes'}',
                        style: const TextStyle(color: TeiColors.muted, fontSize: 12),
                      ),
                      const SizedBox(height: 16),
                      _kpi('CA du jour', _money(_summary?['ca_jour']), TeiColors.normal),
                      _kpi('CA du mois', _money(_summary?['ca_mois']), TeiColors.ink),
                      _kpi('Veille', _money(_summary?['ca_veille']), TeiColors.muted),
                      _kpi('Billets du jour', '${_summary?['billets_jour'] ?? 0}', TeiColors.ink),
                      _kpi('Bagages (jour)', _money(_summary?['ca_bagages_jour']), TeiColors.ink),
                      _kpi('Courrier (jour)', _money(_summary?['ca_courrier_jour']), TeiColors.ink),
                      _kpi(
                        'Écart caisse (jour)',
                        _money(_summary?['ecart_caisse_jour']),
                        ((_summary?['ecart_caisse_jour'] as num?) ?? 0).abs() > 0
                            ? TeiColors.vigilance
                            : TeiColors.normal,
                      ),
                      const SizedBox(height: 12),
                      const Text('Par compagnie', style: TextStyle(fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      ...(((_summary?['companies'] as List?) ?? []).map((c) {
                        final m = Map<String, dynamic>.from(c as Map);
                        return ListTile(
                          contentPadding: EdgeInsets.zero,
                          title: Text('${m['nom_compagnie']}'),
                          trailing: Text(_money(m['ca_jour']), style: const TextStyle(fontWeight: FontWeight.w600)),
                        );
                      })),
                    ],
                  ),
                ),
    );
  }

  Widget _kpi(String label, String value, Color accent) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(width: 4, height: 36, color: accent),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: const TextStyle(color: TeiColors.muted, fontSize: 13)),
                  const SizedBox(height: 2),
                  Text(value, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
