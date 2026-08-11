import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

/// Generic, type-safe dropdown selector.
///
/// [T] is the value type (e.g. [String], enum). The [itemBuilder] callback
/// lets callers control the display label, keeping this component generic.
///
/// Usage:
/// ```dart
/// AppDropdown<String>(
///   label: 'Wilayah Operasional',
///   value: vm.wilayah,
///   items: AppConstants.wilayahAllIn,
///   itemLabel: (v) => v,
///   onChanged: vm.setWilayah,
///   validator: (v) => AppValidators.requiredDropdown(v, fieldName: 'Wilayah'),
/// )
/// ```
class AppDropdown<T> extends StatelessWidget {
  final String label;
  final T? value;
  final List<T> items;
  final String Function(T) itemLabel;
  final void Function(T?) onChanged;
  final String? Function(T?)? validator;
  final String? hint;
  final bool enabled;

  const AppDropdown({
    super.key,
    required this.label,
    required this.value,
    required this.items,
    required this.itemLabel,
    required this.onChanged,
    this.validator,
    this.hint,
    this.enabled = true,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(label, style: AppTextStyles.label),
        const SizedBox(height: 6),
        DropdownButtonFormField<T>(
          // Use initialValue + ValueKey so the widget rebuilds when the
          // external value (from the ViewModel) changes.
          key: ValueKey(value),
          initialValue: value,
          items: items.map((item) {
            return DropdownMenuItem<T>(
              value: item,
              child: Text(
                itemLabel(item),
                style: AppTextStyles.body1,
                overflow: TextOverflow.ellipsis,
              ),
            );
          }).toList(),
          onChanged: enabled ? onChanged : null,
          validator: validator,
          isExpanded: true,
          icon: const Icon(
            Icons.keyboard_arrow_down_rounded,
            color: AppColors.textSecondary,
          ),
          decoration: InputDecoration(
            hintText: hint ?? 'Pilih $label',
            filled: true,
            fillColor: enabled ? AppColors.surface : AppColors.background,
          ),
          dropdownColor: AppColors.surface,
          style: AppTextStyles.body1,
        ),
      ],
    );
  }
}

/// A dropdown whose items are only shown when [enabled] is true and
/// the [items] list is non-empty. Displays a disabled hint otherwise.
///
/// Used for the conditional "Jenis Kegiatan" that depends on "Wilayah".
class AppConditionalDropdown<T> extends StatelessWidget {
  final String label;
  final T? value;
  final List<T> items;
  final String Function(T) itemLabel;
  final void Function(T?) onChanged;
  final String? Function(T?)? validator;
  final String emptyHint;

  const AppConditionalDropdown({
    super.key,
    required this.label,
    required this.value,
    required this.items,
    required this.itemLabel,
    required this.onChanged,
    this.validator,
    this.emptyHint = 'Pilih opsi sebelumnya terlebih dahulu',
  });

  bool get _hasItems => items.isNotEmpty;

  @override
  Widget build(BuildContext context) {
    return AppDropdown<T>(
      label: label,
      value: _hasItems ? value : null,
      items: items,
      itemLabel: itemLabel,
      onChanged: onChanged,
      validator: _hasItems ? validator : null,
      hint: _hasItems ? 'Pilih $label' : emptyHint,
      enabled: _hasItems,
    );
  }
}

/// A radio-button group styled as a segmented selector.
/// Used for binary or small-set choices (e.g., TBKM options).
class AppRadioGroup<T> extends StatelessWidget {
  final String label;
  final T? groupValue;
  final List<T> options;
  final String Function(T) optionLabel;
  final void Function(T?) onChanged;
  final String? Function(T?)? validator;

  const AppRadioGroup({
    super.key,
    required this.label,
    required this.groupValue,
    required this.options,
    required this.optionLabel,
    required this.onChanged,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return FormField<T>(
      initialValue: groupValue,
      validator: validator,
      builder: (state) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(label, style: AppTextStyles.label),
            const SizedBox(height: 8),
            ...options.map((option) {
              final isSelected = groupValue == option;
              return GestureDetector(
                onTap: () {
                  onChanged(option);
                  state.didChange(option);
                },
                child: Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? AppColors.primary.withValues(alpha: 0.06)
                        : AppColors.surface,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isSelected
                          ? AppColors.primary
                          : AppColors.divider,
                      width: isSelected ? 1.5 : 1,
                    ),
                  ),
                  child: Row(
                    children: [
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        width: 20,
                        height: 20,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: isSelected
                                ? AppColors.primary
                                : AppColors.disabled,
                            width: 2,
                          ),
                        ),
                        child: isSelected
                            ? Center(
                                child: Container(
                                  width: 10,
                                  height: 10,
                                  decoration: const BoxDecoration(
                                    shape: BoxShape.circle,
                                    color: AppColors.primary,
                                  ),
                                ),
                              )
                            : null,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          optionLabel(option),
                          style: AppTextStyles.body1.copyWith(
                            color: isSelected
                                ? AppColors.primary
                                : AppColors.textPrimary,
                            fontWeight: isSelected
                                ? FontWeight.w600
                                : FontWeight.w400,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
            if (state.hasError)
              Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text(
                  state.errorText!,
                  style: AppTextStyles.caption.copyWith(
                    color: AppColors.error,
                  ),
                ),
              ),
          ],
        );
      },
    );
  }
}
