import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

/// App Info screen — version, developer info, and links.
class AppInfoScreen extends StatelessWidget {
  const AppInfoScreen({super.key});

  static const String _version = '1.0.0';
  static const String _buildNumber = '1';
  static const String _developer = 'BKJ Technology Team';
  static const String _copyright = '© 2026 PT Berkah Karya Jaya';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Info Aplikasi')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // App logo/icon area
            Container(
              padding: const EdgeInsets.all(28),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.primary.withValues(alpha: 0.08),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  )
                ],
              ),
              child: Column(
                children: [
                  Container(
                    width: 80, height: 80,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [AppColors.primaryDark, AppColors.primaryLight],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Icon(Icons.anchor, color: Colors.white, size: 44),
                  ),
                  const SizedBox(height: 16),
                  const Text('BKJ App', style: AppTextStyles.heading1),
                  const SizedBox(height: 4),
                  Text(
                    'Versi $_version (Build $_buildNumber)',
                    style: AppTextStyles.body2,
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppColors.success.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Versi Terbaru',
                      style: AppTextStyles.caption.copyWith(
                        color: AppColors.success,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            _InfoSection(
              title: 'Tentang Aplikasi',
              items: const [
                _InfoItem(label: 'Nama Aplikasi', value: 'BKJ App'),
                _InfoItem(label: 'Versi', value: _version),
                _InfoItem(label: 'Build', value: _buildNumber),
                _InfoItem(label: 'Platform', value: 'Android'),
              ],
            ),
            const SizedBox(height: 16),
            _InfoSection(
              title: 'Pengembang',
              items: const [
                _InfoItem(label: 'Tim', value: _developer),
                _InfoItem(label: 'Hak Cipta', value: _copyright),
              ],
            ),
            const SizedBox(height: 32),
            Text(
              _copyright,
              style: AppTextStyles.caption,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}

class _InfoSection extends StatelessWidget {
  final String title;
  final List<_InfoItem> items;

  const _InfoSection({required this.title, required this.items});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 8),
          child: Text(title, style: AppTextStyles.label),
        ),
        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            boxShadow: [
              BoxShadow(
                color: AppColors.primary.withValues(alpha: 0.05),
                blurRadius: 8,
                offset: const Offset(0, 2),
              )
            ],
          ),
          child: Column(
            children: [
              for (int i = 0; i < items.length; i++) ...[
                items[i],
                if (i < items.length - 1) const Divider(height: 1, indent: 16),
              ]
            ],
          ),
        ),
      ],
    );
  }
}

class _InfoItem extends StatelessWidget {
  final String label;
  final String value;

  const _InfoItem({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: AppTextStyles.body2),
          Text(value, style: AppTextStyles.body1.copyWith(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
