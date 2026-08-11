import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

/// A consistent card wrapper for grouping related form fields into sections.
///
/// Provides a labeled card with optional trailing action. Use this to
/// break long forms into visual sections.
///
/// Usage:
/// ```dart
/// SectionCard(
///   title: 'Informasi Kapal',
///   icon: Icons.directions_boat_outlined,
///   children: [
///     AppTextField(label: 'Nama Kapal', ...),
///     AppTextField(label: 'Nomor Voyage', ...),
///   ],
/// )
/// ```
class SectionCard extends StatelessWidget {
  final String title;
  final IconData? icon;
  final List<Widget> children;
  final Widget? trailing;
  final EdgeInsetsGeometry? padding;
  final double spacing;

  const SectionCard({
    super.key,
    required this.title,
    required this.children,
    this.icon,
    this.trailing,
    this.padding,
    this.spacing = 16,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          // Section header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.04),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(14),
              ),
              border: const Border(
                bottom: BorderSide(color: AppColors.divider),
              ),
            ),
            child: Row(
              children: [
                if (icon != null) ...[
                  Icon(icon, size: 18, color: AppColors.primary),
                  const SizedBox(width: 10),
                ],
                Expanded(
                  child: Text(title, style: AppTextStyles.heading3),
                ),
                ?trailing,
              ],
            ),
          ),

          // Content
          Padding(
            padding: padding ??
                const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: _buildSpacedChildren(),
            ),
          ),
        ],
      ),
    );
  }

  List<Widget> _buildSpacedChildren() {
    if (children.isEmpty) return [];
    final result = <Widget>[];
    for (int i = 0; i < children.length; i++) {
      result.add(children[i]);
      if (i < children.length - 1) result.add(SizedBox(height: spacing));
    }
    return result;
  }
}

/// A lightweight page wrapper for form screens.
/// Provides a [SingleChildScrollView] with a [Form] key, consistent padding,
/// and a bottom action bar for navigation buttons.
class FormPageWrapper extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final List<Widget> children;
  final Widget bottomBar;
  final EdgeInsetsGeometry? padding;

  const FormPageWrapper({
    super.key,
    required this.formKey,
    required this.children,
    required this.bottomBar,
    this.padding,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Expanded(
          child: Form(
            key: formKey,
            child: SingleChildScrollView(
              padding: padding ??
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: _buildSpacedChildren(children, spacing: 16),
              ),
            ),
          ),
        ),
        _BottomBar(child: bottomBar),
      ],
    );
  }

  List<Widget> _buildSpacedChildren(List<Widget> items, {required double spacing}) {
    if (items.isEmpty) return [];
    final result = <Widget>[];
    for (int i = 0; i < items.length; i++) {
      result.add(items[i]);
      if (i < items.length - 1) result.add(SizedBox(height: spacing));
    }
    return result;
  }
}

/// The sticky bottom bar used for form navigation (Back / Next / Submit).
class _BottomBar extends StatelessWidget {
  final Widget child;

  const _BottomBar({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(
        left: 16,
        right: 16,
        top: 12,
        bottom: MediaQuery.paddingOf(context).bottom + 12,
      ),
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.08),
            blurRadius: 16,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: child,
    );
  }
}

/// A two-button row (Back | Next) commonly used in multi-step form navigation.
class FormNavigationBar extends StatelessWidget {
  final VoidCallback? onBack;
  final VoidCallback? onNext;
  final String backLabel;
  final String nextLabel;
  final bool isLoading;

  const FormNavigationBar({
    super.key,
    this.onBack,
    this.onNext,
    this.backLabel = 'Kembali',
    this.nextLabel = 'Lanjut',
    this.isLoading = false,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        if (onBack != null) ...[
          Expanded(
            flex: 2,
            child: OutlinedButton.icon(
              onPressed: onBack,
              icon: const Icon(Icons.arrow_back_ios, size: 14),
              label: Text(backLabel),
              style: OutlinedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),
          const SizedBox(width: 12),
        ],
        Expanded(
          flex: 3,
          child: ElevatedButton.icon(
            onPressed: isLoading ? null : onNext,
            icon: isLoading
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Icon(Icons.arrow_forward_ios, size: 14),
            label: Text(nextLabel),
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 14),
              iconAlignment: IconAlignment.end,
            ),
          ),
        ),
      ],
    );
  }
}

/// An info banner displayed at the top of form pages for contextual guidance.
class FormInfoBanner extends StatelessWidget {
  final String message;
  final IconData icon;
  final Color? color;

  const FormInfoBanner({
    super.key,
    required this.message,
    this.icon = Icons.info_outline,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final c = color ?? AppColors.info;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: c.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: c.withValues(alpha: 0.25)),
      ),
      child: Row(
        children: [
          Icon(icon, color: c, size: 18),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: AppTextStyles.body2.copyWith(color: c),
            ),
          ),
        ],
      ),
    );
  }
}
