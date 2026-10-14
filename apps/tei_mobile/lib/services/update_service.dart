import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/tei_theme.dart';

/// Vérifie version.json sur le serveur et propose l'installation de l'APK.
class UpdateService {
  UpdateService({
    this.manifestUrl =
        'https://essaiticket.rakietabus.com/assets/tei/app/version.json',
  });

  final String manifestUrl;
  bool _dialogOpen = false;
  DateTime? _lastCheck;

  Future<void> checkAndPrompt(
    BuildContext context, {
    bool notifyIfCurrent = false,
    bool force = false,
  }) async {
    if (_dialogOpen) return;
    final now = DateTime.now();
    if (!force &&
        !notifyIfCurrent &&
        _lastCheck != null &&
        now.difference(_lastCheck!) < const Duration(minutes: 5)) {
      return;
    }
    _lastCheck = now;

    try {
      final info = await PackageInfo.fromPlatform();
      final currentCode = int.tryParse(info.buildNumber) ?? 0;
      // Anti-cache navigateur / proxy
      final uri = Uri.parse(manifestUrl).replace(queryParameters: {
        'v': '${now.millisecondsSinceEpoch}',
      });
      final res = await http
          .get(
            uri,
            headers: const {
              'Cache-Control': 'no-cache, no-store, must-revalidate',
              'Pragma': 'no-cache',
              'Accept': 'application/json',
            },
          )
          .timeout(const Duration(seconds: 12));
      if (res.statusCode != 200) {
        if (notifyIfCurrent && context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Impossible de vérifier la mise à jour')),
          );
        }
        return;
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      final remoteCode = int.tryParse('${data['versionCode'] ?? 0}') ?? 0;
      final remoteName = data['versionName']?.toString() ?? '';
      final apkUrl = data['apkUrl']?.toString();
      final notes = data['notes']?.toString() ?? '';
      if (apkUrl == null || apkUrl.isEmpty) return;
      if (remoteCode <= currentCode) {
        if (notifyIfCurrent && context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Rakieta Lab est à jour (${info.version})')),
          );
        }
        return;
      }
      if (!context.mounted) return;

      _dialogOpen = true;
      try {
        await showDialog<void>(
          context: context,
          barrierDismissible: false,
          builder: (ctx) => AlertDialog(
            title: const Text('Mise à jour Rakieta Lab'),
            content: Text(
              'Une nouvelle version est disponible ($remoteName, code $remoteCode).\n'
              'Version installée : ${info.version} (${info.buildNumber}).\n\n'
              '${notes.isEmpty ? 'Installez la mise à jour pour continuer.' : notes}',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(ctx).pop(),
                child: const Text('Plus tard'),
              ),
              FilledButton(
                style: FilledButton.styleFrom(backgroundColor: TeiColors.brandBlue),
                onPressed: () async {
                  final download = Uri.parse(apkUrl);
                  final ok = await launchUrl(download, mode: LaunchMode.externalApplication);
                  if (!ok && ctx.mounted) {
                    ScaffoldMessenger.of(ctx).showSnackBar(
                      const SnackBar(content: Text('Impossible d’ouvrir le téléchargement')),
                    );
                  }
                  if (ctx.mounted) Navigator.of(ctx).pop();
                },
                child: const Text('Télécharger et installer'),
              ),
            ],
          ),
        );
      } finally {
        _dialogOpen = false;
      }
    } catch (_) {
      if (notifyIfCurrent && context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Vérification de mise à jour impossible (réseau)')),
        );
      }
    }
  }
}
