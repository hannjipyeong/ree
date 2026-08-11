import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

enum AppButtonVariant { primary, secondary, outline, danger, ghost }

/// Unified button component supporting 5 visual variants and a loading state.
///
/// All interactive buttons in the app MUST use this component.
/// Never use raw [ElevatedButton] or [TextButton] directly in feature screens.
///
/// Usage:
/// ```dart
/// AppButton(
///   label: 'Lanjut',
///   onPressed: _handleNext,
///   isLoading: vm.isSubmitting,
/// )
/// ```
class AppButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final AppButtonVariant variant;
  final IconData? leadingIcon;
  final IconData? trailingIcon;
  final double? width;
  final EdgeInsetsGeometry? padding;

  const AppButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.isLoading = false,
    this.variant = AppButtonVariant.primary,
    this.leadingIcon,
    this.trailingIcon,
    this.width,
    this.padding,
  });

  bool get _isDisabled => onPressed == null || isLoading;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width ?? double.infinity,
      child: _buildButton(),
    );
  }

  Widget _buildButton() {
    switch (variant) {
      case AppButtonVariant.primary:
        return _PrimaryButton(
          label: label,
          onPressed: _isDisabled ? null : onPressed,
          isLoading: isLoading,
          leadingIcon: leadingIcon,
          trailingIcon: trailingIcon,
          padding: padding,
        );
      case AppButtonVariant.secondary:
        return _SecondaryButton(
          label: label,
          onPressed: _isDisabled ? null : onPressed,
          isLoading: isLoading,
          leadingIcon: leadingIcon,
          trailingIcon: trailingIcon,
          padding: padding,
        );
      case AppButtonVariant.outline:
        return _OutlineButton(
          label: label,
          onPressed: _isDisabled ? null : onPressed,
          isLoading: isLoading,
          leadingIcon: leadingIcon,
          trailingIcon: trailingIcon,
          padding: padding,
        );
      case AppButtonVariant.danger:
        return _DangerButton(
          label: label,
          onPressed: _isDisabled ? null : onPressed,
          isLoading: isLoading,
          leadingIcon: leadingIcon,
          padding: padding,
        );
      case AppButtonVariant.ghost:
        return _GhostButton(
          label: label,
          onPressed: _isDisabled ? null : onPressed,
          leadingIcon: leadingIcon,
          padding: padding,
        );
    }
  }
}

/// Small icon-only button (e.g., remove row, add row).
class AppIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onPressed;
  final Color? color;
  final Color? backgroundColor;
  final String? tooltip;

  const AppIconButton({
    super.key,
    required this.icon,
    required this.onPressed,
    this.color,
    this.backgroundColor,
    this.tooltip,
  });

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip ?? '',
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(8),
        child: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: backgroundColor ?? AppColors.background,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            icon,
            size: 20,
            color: color ?? AppColors.textSecondary,
          ),
        ),
      ),
    );
  }
}

// ─── Private Variant Implementations ─────────────────────────────────────────

class _ButtonContent extends StatelessWidget {
  final String label;
  final bool isLoading;
  final IconData? leadingIcon;
  final IconData? trailingIcon;
  final Color contentColor;

  const _ButtonContent({
    required this.label,
    required this.isLoading,
    required this.contentColor,
    this.leadingIcon,
    this.trailingIcon,
  });

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return SizedBox(
        width: 20,
        height: 20,
        child: CircularProgressIndicator(
          strokeWidth: 2.5,
          color: contentColor,
        ),
      );
    }
    return Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (leadingIcon != null) ...[
          Icon(leadingIcon, size: 18, color: contentColor),
          const SizedBox(width: 8),
        ],
        Text(
          label,
          style: AppTextStyles.button.copyWith(color: contentColor),
        ),
        if (trailingIcon != null) ...[
          const SizedBox(width: 8),
          Icon(trailingIcon, size: 18, color: contentColor),
        ],
      ],
    );
  }
}

class _PrimaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? leadingIcon;
  final IconData? trailingIcon;
  final EdgeInsetsGeometry? padding;

  const _PrimaryButton({
    required this.label,
    required this.onPressed,
    required this.isLoading,
    this.leadingIcon,
    this.trailingIcon,
    this.padding,
  });

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onPressed,
      style: ElevatedButton.styleFrom(
        padding: padding ??
            const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
      ),
      child: _ButtonContent(
        label: label,
        isLoading: isLoading,
        contentColor: Colors.white,
        leadingIcon: leadingIcon,
        trailingIcon: trailingIcon,
      ),
    );
  }
}

class _SecondaryButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? leadingIcon;
  final IconData? trailingIcon;
  final EdgeInsetsGeometry? padding;

  const _SecondaryButton({
    required this.label,
    required this.onPressed,
    required this.isLoading,
    this.leadingIcon,
    this.trailingIcon,
    this.padding,
  });

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.accent,
        padding: padding ??
            const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
      ),
      child: _ButtonContent(
        label: label,
        isLoading: isLoading,
        contentColor: Colors.white,
        leadingIcon: leadingIcon,
        trailingIcon: trailingIcon,
      ),
    );
  }
}

class _OutlineButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? leadingIcon;
  final IconData? trailingIcon;
  final EdgeInsetsGeometry? padding;

  const _OutlineButton({
    required this.label,
    required this.onPressed,
    required this.isLoading,
    this.leadingIcon,
    this.trailingIcon,
    this.padding,
  });

  @override
  Widget build(BuildContext context) {
    return OutlinedButton(
      onPressed: onPressed,
      style: OutlinedButton.styleFrom(
        padding: padding ??
            const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
      ),
      child: _ButtonContent(
        label: label,
        isLoading: isLoading,
        contentColor: AppColors.primary,
        leadingIcon: leadingIcon,
        trailingIcon: trailingIcon,
      ),
    );
  }
}

class _DangerButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? leadingIcon;
  final EdgeInsetsGeometry? padding;

  const _DangerButton({
    required this.label,
    required this.onPressed,
    required this.isLoading,
    this.leadingIcon,
    this.padding,
  });

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onPressed,
      style: ElevatedButton.styleFrom(
        backgroundColor: AppColors.error,
        padding: padding ??
            const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
      ),
      child: _ButtonContent(
        label: label,
        isLoading: isLoading,
        contentColor: Colors.white,
        leadingIcon: leadingIcon,
      ),
    );
  }
}

class _GhostButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  final IconData? leadingIcon;
  final EdgeInsetsGeometry? padding;

  const _GhostButton({
    required this.label,
    required this.onPressed,
    this.leadingIcon,
    this.padding,
  });

  @override
  Widget build(BuildContext context) {
    return TextButton(
      onPressed: onPressed,
      style: TextButton.styleFrom(
        padding: padding ??
            const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (leadingIcon != null) ...[
            Icon(leadingIcon, size: 18),
            const SizedBox(width: 8),
          ],
          Text(label, style: AppTextStyles.button),
        ],
      ),
    );
  }
}
