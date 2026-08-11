import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';

/// A tap-to-open date picker displayed as a read-only form field.
///
/// Tapping the field opens Flutter's [showDatePicker]. The selected date
/// is displayed using [AppFormatters.toDisplayDate].
///
/// Usage:
/// ```dart
/// AppDatePicker(
///   label: 'Tanggal Kapal',
///   selectedDate: vm.tanggalKapal,
///   onDateSelected: vm.setTanggalKapal,
///   validator: (d) => d == null ? 'Tanggal wajib diisi' : null,
/// )
/// ```
class AppDatePicker extends StatelessWidget {
  final String label;
  final DateTime? selectedDate;
  final void Function(DateTime?) onDateSelected;
  final String? Function(DateTime?)? validator;
  final String? hint;
  final DateTime? firstDate;
  final DateTime? lastDate;
  final DateTime? initialDate;
  final String Function(DateTime)? formatDate;

  const AppDatePicker({
    super.key,
    required this.label,
    required this.selectedDate,
    required this.onDateSelected,
    this.validator,
    this.hint,
    this.firstDate,
    this.lastDate,
    this.initialDate,
    this.formatDate,
  });



  @override
  Widget build(BuildContext context) {
    return FormField<DateTime>(
      initialValue: selectedDate,
      validator: validator,
      builder: (state) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(label, style: AppTextStyles.label),
            const SizedBox(height: 6),
            GestureDetector(
              onTap: () async {
                final now = DateTime.now();
                final picked = await showDatePicker(
                  context: context,
                  initialDate: initialDate ?? selectedDate ?? now,
                  firstDate: firstDate ?? DateTime(2000),
                  lastDate: lastDate ?? DateTime(2100),
                  builder: (context, child) {
                    return Theme(
                      data: Theme.of(context).copyWith(
                        colorScheme: const ColorScheme.light(
                          primary: AppColors.primary,
                          onPrimary: Colors.white,
                          surface: AppColors.surface,
                        ),
                      ),
                      child: child!,
                    );
                  },
                );
                if (picked != null) {
                  state.didChange(picked);
                  onDateSelected(picked);
                }
              },
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 14,
                ),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: state.hasError
                        ? AppColors.error
                        : AppColors.divider,
                    width: state.hasError ? 1.5 : 1,
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.calendar_today_outlined,
                      size: 18,
                      color: selectedDate != null
                          ? AppColors.primary
                          : AppColors.textHint,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        selectedDate != null
                            ? (formatDate != null ? formatDate!(selectedDate!) : AppFormatters.toDisplayDate(selectedDate!))
                            : (hint ?? 'Pilih tanggal'),
                        style: AppTextStyles.body1.copyWith(
                          color: selectedDate != null
                              ? AppColors.textPrimary
                              : AppColors.textHint,
                        ),
                      ),
                    ),
                    const Icon(
                      Icons.arrow_drop_down,
                      color: AppColors.textSecondary,
                    ),
                  ],
                ),
              ),
            ),
            if (state.hasError)
              Padding(
                padding: const EdgeInsets.only(top: 6, left: 14),
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
