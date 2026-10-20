import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
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
  List<Map<String, dynamic>> _cartes = [];
  int _count = 0;
  late String _date;
  String? _asOf;
  Map<String, dynamic>? _compagnie;
  Map<String, dynamic>? _agence;

  @override
  void initState() {
    super.initState();
    _date = DateFormat('yyyy-MM-dd').format(DateTime.now());
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _pickDate() async {
    final current = DateTime.tryParse(_date) ?? DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: current,
      firstDate: DateTime(current.year - 1),
      lastDate: DateTime(current.year + 1, 12, 31),
      locale: const Locale('fr', 'FR'),
    );
    if (picked == null) return;
    setState(() {
      _date = DateFormat('yyyy-MM-dd').format(picked);
      _compagnie = null;
      _agence = null;
    });
    await _load();
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
        _error = 'Aucun périmètre entreprise';
      });
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await context.read<ApiClient>().departures(ekey: ekey, date: _date);
      if (!mounted) return;
      final flat = (data['departures'] as List?) ?? [];
      final cartes = (data['cartes'] as List?) ?? [];
      setState(() {
        _cartes = cartes.isNotEmpty
            ? cartes.map((e) => Map<String, dynamic>.from(e as Map)).toList()
            : _cartesDepuisListe(flat);
        _count = (data['count'] as num?)?.toInt() ?? flat.length;
        _date = data['date']?.toString() ?? _date;
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

  List<Map<String, dynamic>> _cartesDepuisListe(List<dynamic> items) {
    final byCie = <String, Map<String, dynamic>>{};
    for (final raw in items) {
      final m = Map<String, dynamic>.from(raw as Map);
      final cie = '${m['compagnie'] ?? '—'}';
      final agence = '${m['gare_depart'] ?? m['code_gare'] ?? '—'}';
      final bucket = byCie.putIfAbsent(cie, () => {
            'compagnie': cie,
            'lieux': <String, List<Map<String, dynamic>>>{},
          });
      final lieux = bucket['lieux'] as Map<String, List<Map<String, dynamic>>>;
      lieux.putIfAbsent(agence, () => []).add(m);
    }
    return byCie.values.map((cie) {
      final lieux = cie['lieux'] as Map<String, List<Map<String, dynamic>>>;
      final list = lieux.entries.map((e) => {
            'agence': e.key,
            'count': e.value.length,
            'complets': e.value.where((d) => d['alerte'] == 'COMPLET').length,
            'departures': e.value,
          }).toList();
      final count = list.fold<int>(0, (s, a) => s + (a['count'] as int));
      return {
        'compagnie': cie['compagnie'],
        'count': count,
        'agences': list.length,
        'lieux': list,
      };
    }).toList();
  }

  void _back() {
    setState(() {
      if (_agence != null) {
        _agence = null;
      } else {
        _compagnie = null;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final titre = _agence != null
        ? '${_agence!['agence']}'
        : _compagnie != null
            ? '${_compagnie!['compagnie']}'
            : 'Départs';
    return Scaffold(
      appBar: AppBar(
        leading: (_compagnie != null)
            ? IconButton(onPressed: _back, icon: const Icon(Icons.arrow_back))
            : null,
        title: Text(titre),
        actions: [
          IconButton(onPressed: _loading ? null : _load, icon: const Icon(Icons.refresh)),
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
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(12, 12, 12, 24),
                    children: [
                      _dateBar(),
                      const SizedBox(height: 8),
                      Text(
                        '$_count départs · choisissez une compagnie, puis une agence',
                        style: const TextStyle(color: TeiColors.muted, fontSize: 12),
                      ),
                      if (_asOf != null)
                        Text(
                          'Actualisé : $_asOf',
                          style: const TextStyle(color: TeiColors.muted, fontSize: 11),
                        ),
                      const SizedBox(height: 10),
                      ..._body(),
                    ],
                  ),
                ),
    );
  }

  Widget _dateBar() {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: _pickDate,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: Row(
            children: [
              const Icon(Icons.calendar_today, size: 18, color: TeiColors.brandBlue),
              const SizedBox(width: 10),
              Text(_date, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
              const Spacer(),
              const Text('Changer', style: TextStyle(color: TeiColors.brandBlue)),
            ],
          ),
        ),
      ),
    );
  }

  List<Widget> _body() {
    if (_cartes.isEmpty) {
      return const [
        Padding(
          padding: EdgeInsets.all(32),
          child: Center(child: Text('Aucun départ pour cette date')),
        ),
      ];
    }
    if (_agence != null) return _departures(_agence!);
    if (_compagnie != null) return _agences(_compagnie!);
    return _compagnies();
  }

  List<Widget> _compagnies() {
    return [
      Wrap(
        spacing: 8,
        runSpacing: 8,
        children: _cartes.map((cie) {
          return SizedBox(
            width: (MediaQuery.of(context).size.width - 40) / 2,
            child: _MiniCard(
              title: '${cie['compagnie']}',
              lines: [
                '${cie['count']} départs',
                '${cie['agences'] ?? (cie['lieux'] as List?)?.length ?? 0} agences',
              ],
              onTap: () => setState(() {
                _compagnie = cie;
                _agence = null;
              }),
            ),
          );
        }).toList(),
      ),
    ];
  }

  List<Widget> _agences(Map<String, dynamic> cie) {
    final lieux = ((cie['lieux'] as List?) ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
    if (lieux.isEmpty) {
      return const [Text('Aucune agence pour cette compagnie.')];
    }
    return lieux.map((lieu) {
      return Card(
        margin: const EdgeInsets.only(bottom: 8),
        child: ListTile(
          leading: const Icon(Icons.storefront_outlined, color: TeiColors.brandBlue),
          title: Text('${lieu['agence']}', style: const TextStyle(fontWeight: FontWeight.w700)),
          subtitle: Text('${lieu['count']} départs${(lieu['complets'] ?? 0) == 0 ? '' : ' · ${lieu['complets']} complet(s)'}'),
          trailing: const Icon(Icons.chevron_right),
          onTap: () => setState(() => _agence = lieu),
        ),
      );
    }).toList();
  }

  List<Widget> _departures(Map<String, dynamic> agence) {
    final deps = ((agence['departures'] as List?) ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
    deps.sort((a, b) => '${a['heure']}'.compareTo('${b['heure']}'));
    if (deps.isEmpty) return const [Text('Aucun départ dans cette agence.')];
    return deps.map((m) {
      final alerte = m['alerte']?.toString();
      final taux = m['taux_remplissage'];
      return Card(
        margin: const EdgeInsets.only(bottom: 8),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Text('${m['heure']}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
                  const Spacer(),
                  if (alerte != null && alerte.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: alerte == 'COMPLET' || alerte == 'prioritaire'
                            ? TeiColors.critique
                            : TeiColors.vigilance,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(alerte, style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w700)),
                    ),
                ],
              ),
              const SizedBox(height: 4),
              Text('${m['ligne']}', style: const TextStyle(fontWeight: FontWeight.w700)),
              Text('${m['gare_depart']} → ${m['destination']}', style: const TextStyle(color: TeiColors.muted)),
              const SizedBox(height: 6),
              Text('Places ${m['places_occupees']}/${m['capacite']} · ${taux ?? '—'} % · billets ${m['billets_vendus']}'),
            ],
          ),
        ),
      );
    }).toList();
  }
}

class _MiniCard extends StatelessWidget {
  const _MiniCard({required this.title, required this.lines, required this.onTap});
  final String title;
  final List<String> lines;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: const BorderSide(color: Color(0x14000000)),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w800, color: TeiColors.brandBlue)),
              const SizedBox(height: 8),
              for (final line in lines)
                Text(line, style: const TextStyle(fontSize: 12, color: TeiColors.muted)),
            ],
          ),
        ),
      ),
    );
  }
}
