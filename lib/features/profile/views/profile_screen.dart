import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/profile/viewmodels/profile_viewmodel.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart';
import 'package:bkj_app/core/components/app_button.dart';

/// Profile — main screen showing user info and navigation to sub-screens.
class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<ProfileViewModel>();
    final authVm = context.watch<AuthViewModel>();

    return Scaffold(
      backgroundColor: AppColors.background,
      body: CustomScrollView(
        slivers: [
          // ── Header ────────────────────────────────────────────────────────
          SliverAppBar(
            expandedHeight: 200,
            floating: false,
            pinned: true,
            automaticallyImplyLeading: false,
            title: const Text('Profil Saya'),
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [AppColors.primaryDark, AppColors.primaryLight],
                  ),
                ),
                child: SafeArea(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const SizedBox(height: 40),
                      CircleAvatar(
                        radius: 38,
                        backgroundColor: AppColors.accent,
                        child: Text(
                          AppFormatters.toInitials(authVm.fullName),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        authVm.fullName,
                        style: AppTextStyles.heading3.copyWith(color: Colors.white),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        authVm.userRole.toUpperCase(),
                        style: AppTextStyles.body2.copyWith(color: Colors.white70),
                      ),
                      Text(
                        authVm.email,
                        style: AppTextStyles.caption.copyWith(color: Colors.white60),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),

          // ── Menu Items ────────────────────────────────────────────────────
          SliverPadding(
            padding: const EdgeInsets.all(16),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                _ProfileInfoCard(authVm: authVm),
                const SizedBox(height: 16),
                _MenuGroup(
                  title: 'Aktivitas',
                  items: [
                    _MenuItem(
                      icon: Icons.history,
                      label: 'Riwayat Transaksi',
                      onTap: () {
                        // TODO: Navigate to history screen when built
                      },
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                _MenuGroup(
                  title: 'Akun',
                  items: [
                    _MenuItem(
                      icon: Icons.person_outline,
                      label: 'Edit Profil',
                      onTap: () => Navigator.pushNamed(context, AppRoutes.editProfile),
                    ),
                    _MenuItem(
                      icon: Icons.lock_outline,
                      label: 'Ganti Password',
                      onTap: () => Navigator.pushNamed(context, AppRoutes.changePassword),
                    ),
                    _MenuItem(
                      icon: Icons.pin_outlined,
                      label: 'Ganti PIN',
                      onTap: () => Navigator.pushNamed(context, AppRoutes.changePin),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                _MenuGroup(
                  title: 'Aplikasi',
                  items: [
                    _MenuItem(
                      icon: Icons.info_outline,
                      label: 'Info Aplikasi',
                      onTap: () => Navigator.pushNamed(context, AppRoutes.appInfo),
                    ),
                    _MenuItem(
                      icon: Icons.logout,
                      label: 'Keluar',
                      color: AppColors.error,
                      onTap: () => _confirmLogout(context, vm),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
              ]),
            ),
          ),
        ],
      ),
    );
  }

  void _confirmLogout(BuildContext context, ProfileViewModel vm) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: const Text('Konfirmasi Keluar', style: AppTextStyles.heading3),
        content: const Text(
          'Anda yakin ingin keluar dari aplikasi?',
          style: AppTextStyles.body1,
        ),
        actions: [
          Row(
            children: [
              Expanded(
                child: AppButton(
                  label: 'Batal',
                  variant: AppButtonVariant.outline,
                  onPressed: () => Navigator.pop(ctx),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: AppButton(
                  label: 'Keluar',
                  variant: AppButtonVariant.danger,
                  onPressed: () {
                    Navigator.pop(ctx);
                    context.read<AuthViewModel>().logout();
                    Navigator.pushNamedAndRemoveUntil(context, AppRoutes.login, (route) => false);
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ProfileInfoCard extends StatelessWidget {
  final AuthViewModel authVm;

  const _ProfileInfoCard({required this.authVm});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.06),
            blurRadius: 10,
            offset: const Offset(0, 3),
          )
        ],
      ),
      child: Column(
        children: [
          _InfoRow(icon: Icons.email_outlined, label: 'Email', value: authVm.email),
          const Divider(height: 20),
          _InfoRow(icon: Icons.phone_outlined, label: 'No. HP', value: authVm.phone),
          const Divider(height: 20),
          _InfoRow(icon: Icons.badge_outlined, label: 'Role', value: authVm.userRole),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoRow({required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 18, color: AppColors.primary),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: AppTextStyles.caption),
            Text(value, style: AppTextStyles.body1.copyWith(fontWeight: FontWeight.w600)),
          ],
        ),
      ],
    );
  }
}

class _MenuGroup extends StatelessWidget {
  final String title;
  final List<_MenuItem> items;

  const _MenuGroup({required this.title, required this.items});

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
                if (i < items.length - 1)
                  const Divider(height: 1, indent: 52),
              ]
            ],
          ),
        ),
      ],
    );
  }
}

class _MenuItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color? color;

  const _MenuItem({
    required this.icon,
    required this.label,
    required this.onTap,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final c = color ?? AppColors.textPrimary;
    return Material(
      color: Colors.transparent,
      child: ListTile(
        onTap: onTap,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: (color ?? AppColors.primary).withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color ?? AppColors.primary, size: 20),
        ),
        title: Text(label, style: AppTextStyles.body1.copyWith(color: c, fontWeight: FontWeight.w500)),
        trailing: Icon(Icons.arrow_forward_ios, size: 14, color: AppColors.textHint),
      ),
    );
  }
}
