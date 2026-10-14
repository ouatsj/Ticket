import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_client.dart';
import '../services/auth_state.dart';
import '../theme/tei_theme.dart';

class InsightsScreen extends StatefulWidget {
  const InsightsScreen({super.key});

  @override
  State<InsightsScreen> createState() => _InsightsScreenState();
}

class _InsightsScreenState extends State<InsightsScreen> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _data;

  static const _date = '2026-09-13';

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
      final data = await context.read<ApiClient>().insights(ekey: ekey, date: _date);
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

  Color _prioColor(String? p) {
    switch (p) {
      case 'haute':
        return TeiColors.critique;
      case 'moyenne':
        return TeiColors.vigilance;
      default:
        return TeiColors.normal;
    }
  }

  @override
  Widget build(BuildContext context) {
    final items = (_data?['recommendations'] as List?) ?? [];
    return Scaffold(
      appBar: AppBar(
        title: const Text('Conseiller'),
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
                        _data?['avertissement']?.toString() ?? '',
                        style: const TextStyle(color: TeiColors.muted, fontSize: 12),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Méthode : ${_data?['methode']} · ${items.length} conseil(s) · jour $_date',
                        style: const TextStyle(fontSize: 12, color: TeiColors.muted),
                      ),
                      const SizedBox(height: 16),
                      if (items.isEmpty)
                        const Text('Aucune recommandation pour cette période.'),
                      ...items.map((raw) {
                        final m = Map<String, dynamic>.from(raw as Map);
                        final prio = m['priorite']?.toString() ?? 'basse';
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          child: Padding(
                            padding: const EdgeInsets.all(14),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      color: _prioColor(prio),
                                      child: Text(
                                        prio.toUpperCase(),
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 11,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      '${m['categorie']}',
                                      style: const TextStyle(color: TeiColors.muted, fontSize: 12),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  '${m['probleme']}',
                                  style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  'Action : ${m['action']}',
                                  style: const TextStyle(fontSize: 13),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  'Chiffres : ${m['chiffres']}',
                                  style: const TextStyle(fontSize: 12, color: TeiColors.muted),
                                ),
                              ],
                            ),
                          ),
                        );
                      }),
                    ],
                  ),
                ),
    );
  }
}
