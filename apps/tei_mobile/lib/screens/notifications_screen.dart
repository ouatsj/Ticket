import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_client.dart';
import '../services/auth_state.dart';
import '../theme/tei_theme.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _loading = true;
  String? _error;
  List<dynamic> _items = [];
  String _filtre = 'toutes';

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
        _error = 'Aucun périmètre';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await context.read<ApiClient>().notifications(
            ekey: ekey,
            categorie: _filtre == 'urgentes' ? null : (_filtre == 'toutes' ? null : _filtre),
            niveau: _filtre == 'urgentes' ? 'urgentes' : null,
          );
      if (!mounted) return;
      setState(() {
        _items = (data['notifications'] as List?) ?? [];
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

  Future<void> _scan() async {
    final ekey = context.read<AuthState>().primaryEkey;
    if (ekey == null) return;
    try {
      final r = await context.read<ApiClient>().scanAlerts(ekey: ekey, date: '2026-09-13');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Scan : ${r['emitted']} nouvelle(s) alerte(s) / ${r['scanned']} départs')),
      );
      await _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
    }
  }

  Color _nivColor(String? n) {
    if (n == 'critique') return TeiColors.critique;
    if (n == 'vigilance') return TeiColors.vigilance;
    return TeiColors.normal;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Alertes'),
        actions: [
          IconButton(onPressed: _scan, tooltip: 'Scanner maintenant', icon: const Icon(Icons.radar)),
          IconButton(onPressed: _loading ? null : _load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: Column(
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Row(
              children: [
                for (final f in ['toutes', 'urgentes', 'depart', 'finance', 'courrier', 'bagages'])
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(f),
                      selected: _filtre == f,
                      onSelected: (_) {
                        setState(() => _filtre = f);
                        _load();
                      },
                    ),
                  ),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _error != null
                    ? Center(child: Text(_error!))
                    : _items.isEmpty
                        ? const Center(child: Text('Aucune alerte pour ce filtre.'))
                        : ListView.builder(
                            padding: const EdgeInsets.all(16),
                            itemCount: _items.length,
                            itemBuilder: (context, i) {
                              final m = Map<String, dynamic>.from(_items[i] as Map);
                              return Card(
                                margin: const EdgeInsets.only(bottom: 10),
                                child: ListTile(
                                  leading: Container(
                                    width: 8,
                                    height: 40,
                                    color: _nivColor(m['niveau']?.toString()),
                                  ),
                                  title: Text('${m['titre']}', style: const TextStyle(fontWeight: FontWeight.w700)),
                                  subtitle: Text('${m['corps']}\n${m['emitted_at']} · ${m['categorie']}'),
                                  isThreeLine: true,
                                ),
                              );
                            },
                          ),
          ),
        ],
      ),
    );
  }
}
