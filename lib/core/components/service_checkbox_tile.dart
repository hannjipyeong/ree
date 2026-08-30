import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

/// A selectable service checkbox tile with support for conditional sub-widgets.
///
/// When [isSelected] is true, the tile expands to reveal [expandedChild]
/// (e.g., a file upload field, a radio group, or a numeric input).
///
/// Usage:
/// ```dart
/// ServiceCheckboxTile(
///   serviceKey: AppConstants.serviceRailing,
///   label: 'Railing',
///   description: 'Angkutan container dari/ke pelabuhan',
///   icon: Icons.local_shipping_outlined,
///   isSelected: vm.isServiceSelected(AppConstants.serviceRailing),
///   onToggle: () => vm.toggleService(AppConstants.serviceRailing),
///   expandedChild: AppFileUploadTile(...)
/// )
/// ```
class ServiceCheckboxTile extends StatelessWidget {
  final String serviceKey;
  final String label;

  final IconData icon;
  final bool isSelected;
  final VoidCallback onToggle;

  /// Optional widget revealed when the tile is selected.
  final Widget? expandedChild;

  const ServiceCheckboxTile({
    super.key,
    required this.serviceKey,
    required this.label,
    required this.icon,
    required this.isSelected,
    required this.onToggle,

    this.expandedChild,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onToggle,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        margin: const EdgeInsets.only(bottom: 10),
        decoration: BoxDecoration(
          color: isSelected
              ? AppColors.primary.withValues(alpha: 0.05)
              : AppColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.divider,
            width: isSelected ? 1.5 : 1,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.primary.withValues(alpha: 0.08),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  )
                ]
              : null,
        ),
        child: Column(
          children: [
            // Main row
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              child: Row(
                children: [
                  // Icon
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppColors.primary.withValues(alpha: 0.12)
                          : AppColors.background,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      icon,
                      color: isSelected
                          ? AppColors.primary
                          : AppColors.textSecondary,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 14),
                  // Text
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          label,
                          style: AppTextStyles.body1.copyWith(
                            fontWeight: FontWeight.w600,
                            color: isSelected
                                ? AppColors.primary
                                : AppColors.textPrimary,
                          ),
                        ),

                      ],
                    ),
                  ),
                  // Checkbox indicator
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    width: 24,
                    height: 24,
                    decoration: BoxDecoration(
                      color: isSelected ? AppColors.primary : Colors.transparent,
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                        color: isSelected
                            ? AppColors.primary
                            : AppColors.disabled,
                        width: 1.5,
                      ),
                    ),
                    child: isSelected
                        ? const Icon(Icons.check, size: 16, color: Colors.white)
                        : null,
                  ),
                ],
              ),
            ),

            // Expanded sub-content (revealed on selection)
            if (isSelected && expandedChild != null)
              AnimatedSize(
                duration: const Duration(milliseconds: 250),
                curve: Curves.easeInOut,
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
                  decoration: BoxDecoration(
                    border: Border(
                      top: BorderSide(
                        color: AppColors.primary.withValues(alpha: 0.15),
                      ),
                    ),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.only(top: 14),
                    // Intercept taps on the sub-content so they don't
                    // accidentally toggle the parent tile.
                    child: GestureDetector(
                      onTap: () {},
                      behavior: HitTestBehavior.opaque,
                      child: expandedChild!,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
