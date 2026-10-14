import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_client.dart';
import '../services/auth_state.dart';
import '../theme/tei_theme.dart';

class DeparturesScreen extends StatefulWidget {
  const DeparturesScreen({super.key});

  @override
  State<DeparturesScreen> createState() => _DeparturesScreenState();
}

class _DeparturesScreenState extends State<DeparturesScreen> {
  bool _loading = true;
  String? _error;
  List<dynamic> _tree = [];
  int _count = 0;
  String? _date;
  String? _asOf;

  /// Staging a peu de programmes « aujourd'hui » : date de référence métier.
  static const _defaultDate = '2026-09-13';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load({String? date}) async {
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
        _error = 'Aucun périmètre entreprise';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await context.read<ApiClient>().departures(
            ekey: ekey,
            date: date ?? _date ?? _defaultDate,
          );
      if (!mounted) return;
      final tree = (data['tree'] as List?) ?? [];
      final flat = (data['departures'] as List?) ?? [];
      setState(() {
        _tree = tree.isNotEmpty ? tree : _buildTreeLocal(flat);
        _count = (data['count'] as num?)?.toInt() ?? flat.length;
        _date = data['date']?.toString();
        _asOf = data['as_of']?.toString();
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

  /// Fallback si ancienne API sans `tree`.
  List<Map<String, dynamic>> _buildTreeLocal(List<dynamic> items) {
    final byCie = <String, dynamic>{};
    for (final raw in items) {
      final m = Map<String, dynamic>.from(raw as Map);
      final cie = '${m['compagnie'] ?? '—'}';
      final agence = '${m['agence'] ?? m['gare_depart'] ?? '—'}';
      final gare = '${m['gare_depart'] ?? m['gare'] ?? '—'}';
      final escale = '${m['escale'] ?? m['destination'] ?? '—'}';
      final byAgence = byCie.putIfAbsent(cie, () => <String, dynamic>{}) as Map<String, dynamic>;
      final byGare = byAgence.putIfAbsent(agence, () => <String, dynamic>{}) as Map<String, dynamic>;
      final byEscale = byGare.putIfAbsent(gare, () => <String, dynamic>{}) as Map<String, dynamic>;
      final list = byEscale.putIfAbsent(escale, () => <Map<String, dynamic>>[]) as List<Map<String, dynamic>>;
      list.add(m);
    }
    return byCie.entries.map((cieE) {
      final byAgence = cieE.value as Map<String, dynamic>;
      final agences = byAgence.entries.map((agE) {
        final byGare = agE.value as Map<String, dynamic>;
        final gares = byGare.entries.map((gE) {
          final byEscale = gE.value as Map<String, dynamic>;
          final escales = byEscale.entries.map((esE) {
            final deps = List<Map<String, dynamic>>.from(esE.value as List);
            return {
              'escale': esE.key,
              'count': deps.length,
              'escales_itineraire': deps.isNotEmpty ? (deps.first['escales'] ?? '') : '',
              'departures': deps,
            };
          }).toList();
          final count = escales.fold<int>(0, (a, e) => a + (e['count'] as int));
          return {'gare': gE.key, 'count': count, 'escales': escales};
        }).toList();
        final count = gares.fold<int>(0, (a, g) => a + (g['count'] as int));
        return {'agence': agE.key, 'count': count, 'gares': gares};
      }).toList();
      final count = agences.fold<int>(0, (a, ag) => a + (ag['count'] as int));
      return {'compagnie': cieE.key, 'count': count, 'agences': agences};
    }).toList();
  }

  Color _alertColor(String? a) {
    switch (a) {
      case 'COMPLET':
      case 'prioritaire':
        return TeiColors.critique;
      case 'vigilance':
        return TeiColors.vigilance;
      default:
        return TeiColors.normal;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Départs'),
        actions: [
          IconButton(onPressed: _loading ? null : () => _load(), icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Text(_error!, textAlign: TextAlign.center),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: () => _load(),
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(12, 12, 12, 24),
                    children: [
                      Text(
                        'Jour $_date · $_count départs\n'
                        'Regroupés : Compagnie → Agence → Gare → Escale\n'
                        'Actualisé : $_asOf',
                        style: const TextStyle(color: TeiColors.muted, fontSize: 12),
                      ),
                      const SizedBox(height: 8),
                      if (_tree.isEmpty)
                        const Padding(
                          padding: EdgeInsets.all(32),
                          child: Center(child: Text('Aucun départ pour cette date')),
                        ),
                      ..._tree.map((raw) {
                        final cie = Map<String, dynamic>.from(raw as Map);
                        return _CieTile(cie: cie, alertColor: _alertColor);
                      }),
                    ],
                  ),
                ),
    );
  }
}

class _CieTile extends StatelessWidget {
  const _CieTile({required this.cie, required this.alertColor});
  final Map<String, dynamic> cie;
  final Color Function(String?) alertColor;

  @override
  Widget build(BuildContext context) {
    final agences = (cie['agences'] as List?) ?? [];
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      clipBehavior: Clip.antiAlias,
      child: ExpansionTile(
        initiallyExpanded: true,
        leading: const Icon(Icons.business, color: TeiColors.brandBlue),
        title: Text(
          '${cie['compagnie']}',
          style: const TextStyle(fontWeight: FontWeight.w800, color: TeiColors.brandBlue),
        ),
        subtitle: Text('${cie['count']} départ(s)'),
        children: agences.map((a) {
          final agence = Map<String, dynamic>.from(a as Map);
          return _AgenceTile(agence: agence, alertColor: alertColor);
        }).toList(),
      ),
    );
  }
}

class _AgenceTile extends StatelessWidget {
  const _AgenceTile({required this.agence, required this.alertColor});
  final Map<String, dynamic> agence;
  final Color Function(String?) alertColor;

  @override
  Widget build(BuildContext context) {
    final gares = (agence['gares'] as List?) ?? [];
    return Theme(
      data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
      child: ExpansionTile(
        initiallyExpanded: true,
        tilePadding: const EdgeInsets.only(left: 20, right: 12),
        leading: const Icon(Icons.storefront_outlined, size: 22, color: TeiColors.ink),
        title: Text('Agence · ${agence['agence']}', style: const TextStyle(fontWeight: FontWeight.w700)),
        subtitle: Text('${agence['count']} départ(s)'),
        children: gares.map((g) {
          final gare = Map<String, dynamic>.from(g as Map);
          return _GareTile(gare: gare, alertColor: alertColor);
        }).toList(),
      ),
    );
  }
}

class _GareTile extends StatelessWidget {
  const _GareTile({required this.gare, required this.alertColor});
  final Map<String, dynamic> gare;
  final Color Function(String?) alertColor;

  @override
  Widget build(BuildContext context) {
    final escales = (gare['escales'] as List?) ?? [];
    return ExpansionTile(
      initiallyExpanded: true,
      tilePadding: const EdgeInsets.only(left: 36, right: 12),
      leading: const Icon(Icons.location_city_outlined, size: 20, color: TeiColors.muted),
      title: Text('Gare · ${gare['gare']}', style: const TextStyle(fontWeight: FontWeight.w600)),
      subtitle: Text('${gare['count']} départ(s)'),
      children: escales.map((e) {
        final escale = Map<String, dynamic>.from(e as Map);
        return _EscaleBlock(escale: escale, alertColor: alertColor);
      }).toList(),
    );
  }
}

class _EscaleBlock extends StatelessWidget {
  const _EscaleBlock({required this.escale, required this.alertColor});
  final Map<String, dynamic> escale;
  final Color Function(String?) alertColor;

  @override
  Widget build(BuildContext context) {
    final deps = (escale['departures'] as List?) ?? [];
    final itin = '${escale['escales_itineraire'] ?? ''}';
    return Padding(
      padding: const EdgeInsets.fromLTRB(48, 0, 12, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.flag_outlined, size: 18, color: TeiColors.brandRed),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  'Escale / destination · ${escale['escale']}',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          if (itin.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              'Itinéraire : $itin',
              style: const TextStyle(color: TeiColors.muted, fontSize: 12),
            ),
          ],
          const SizedBox(height: 8),
          ...deps.map((raw) {
            final m = Map<String, dynamic>.from(raw as Map);
            final alerte = m['alerte']?.toString();
            final taux = m['taux_remplissage'];
            return Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: TeiColors.surface,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.black12),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Text(
                        '${m['heure']}',
                        style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          '${m['ligne']}',
                          style: const TextStyle(fontWeight: FontWeight.w600),
                        ),
                      ),
                      if (alerte != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          color: alertColor(alerte),
                          child: Text(
                            alerte,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Remplissage : ${m['places_occupees']}/${m['capacite']} '
                    '(${taux ?? '—'} %) · dispo ${m['places_disponibles'] ?? '—'} · '
                    'billets ${m['billets_vendus']}',
                    style: TextStyle(
                      color: alertColor(alerte),
                      fontWeight: FontWeight.w600,
                      fontSize: 13,
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }
}
