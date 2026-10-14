import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:provider/provider.dart';
import '../services/api_client.dart';
import '../services/auth_state.dart';
import '../services/update_service.dart';
import '../theme/tei_theme.dart';
import 'notifications_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? _prefs;
  bool _loadingPrefs = true;
  String _versionLabel = '';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final info = await PackageInfo.fromPlatform();
      if (mounted) {
        setState(() => _versionLabel = '${info.version} (${info.buildNumber})');
      }
      await _loadPrefs();
    });
  }

  Future<void> _loadPrefs() async {
    try {
      final p = await context.read<ApiClient>().notificationPrefs();
      if (!mounted) return;
      setState(() {
        _prefs = p;
        _loadingPrefs = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loadingPrefs = false);
    }
  }

  Future<void> _toggle(String key, bool value) async {
    final next = Map<String, dynamic>.from(_prefs ?? {});
    next[key] = value ? 1 : 0;
    setState(() => _prefs = next);
    try {
      final saved = await context.read<ApiClient>().saveNotificationPrefs({
        'cat_depart': next['cat_depart'] == 1 || next['cat_depart'] == true,
        'cat_finance': next['cat_finance'] == 1 || next['cat_finance'] == true,
        'cat_courrier': next['cat_courrier'] == 1 || next['cat_courrier'] == true,
        'cat_bagages': next['cat_bagages'] == 1 || next['cat_bagages'] == true,
        'cat_systeme': next['cat_systeme'] == 1 || next['cat_systeme'] == true,
        'urgentes_seulement':
            next['urgentes_seulement'] == 1 || next['urgentes_seulement'] == true,
      });
      if (!mounted) return;
      setState(() => _prefs = saved);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$e')));
    }
  }

  bool _on(dynamic v) => v == 1 || v == true;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthState>();
    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Image.asset('assets/branding/rakieta_lab_logo.png', height: 72, fit: BoxFit.contain),
          const SizedBox(height: 12),
          Text(
            'Rakieta',
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.w700,
                  color: TeiColors.brandBlue,
                ),
          ),
          const SizedBox(height: 4),
          Text(
            _versionLabel.isEmpty ? '…' : 'Version $_versionLabel',
            style: const TextStyle(color: TeiColors.muted),
          ),
          const SizedBox(height: 8),
          Text(auth.username ?? '—', style: const TextStyle(fontWeight: FontWeight.w600)),
          const Text('Rôle : Decideur (mobile uniquement)', style: TextStyle(color: TeiColors.muted)),
          const SizedBox(height: 16),
          const Text('Périmètre', style: TextStyle(fontWeight: FontWeight.w700)),
          ...auth.scopes.map((s) {
            final m = Map<String, dynamic>.from(s as Map);
            final companies = (m['companies'] as List?) ?? [];
            final all = m['all'] == true || companies.isEmpty;
            return ListTile(
              contentPadding: EdgeInsets.zero,
              title: Text('Entreprise ekey ${m['ekey']}'),
              subtitle: Text(all ? 'Toutes les compagnies' : 'Compagnies : ${companies.join(', ')}'),
            );
          }),
          const Divider(),
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const Icon(Icons.notifications_outlined),
            title: const Text('Centre d’alertes'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const NotificationsScreen()),
              );
            },
          ),
          const SizedBox(height: 8),
          const Text('Préférences de notification', style: TextStyle(fontWeight: FontWeight.w700)),
          if (_loadingPrefs)
            const Padding(
              padding: EdgeInsets.all(12),
              child: LinearProgressIndicator(),
            )
          else ...[
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Départs'),
              value: _on(_prefs?['cat_depart'] ?? 1),
              onChanged: (v) => _toggle('cat_depart', v),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Finances'),
              value: _on(_prefs?['cat_finance'] ?? 1),
              onChanged: (v) => _toggle('cat_finance', v),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Courrier'),
              value: _on(_prefs?['cat_courrier'] ?? 1),
              onChanged: (v) => _toggle('cat_courrier', v),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Bagages'),
              value: _on(_prefs?['cat_bagages'] ?? 1),
              onChanged: (v) => _toggle('cat_bagages', v),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Urgentes seulement'),
              value: _on(_prefs?['urgentes_seulement'] ?? 0),
              onChanged: (v) => _toggle('urgentes_seulement', v),
            ),
          ],
          const SizedBox(height: 8),
          const Text(
            'Push Firebase : activez FIREBASE_SERVICE_ACCOUNT sur l’API. '
            'Sans projet Firebase, les alertes sont stockées et listées ici (mode mock).',
            style: TextStyle(color: TeiColors.muted, fontSize: 12),
          ),
          const SizedBox(height: 24),
          FilledButton.icon(
            onPressed: () => context
                .read<UpdateService>()
                .checkAndPrompt(context, notifyIfCurrent: true),
            icon: const Icon(Icons.system_update),
            label: const Text('Vérifier les mises à jour'),
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
              backgroundColor: TeiColors.brandBlue,
            ),
          ),
        ],
      ),
    );
  }
}
