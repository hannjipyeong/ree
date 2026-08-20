import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/components/app_button.dart';
import 'package:bkj_app/core/components/app_dropdown.dart';
import 'package:bkj_app/core/components/app_text_field.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/all_in/models/container_entry.dart';

/// Dynamic list builder for container entries.
///
/// Renders a [ListView] of [_ContainerEntryCard] items with add/remove controls.
/// Enforces the [AppConstants.maxContainers] cap.
///
/// This widget is purely presentational — it delegates all mutations
/// to the provided callbacks (ViewModel-owned state).
///
/// Usage:
/// ```dart
/// ContainerListBuilder(
///   containers: vm.containers,
///   canAdd: vm.canAddContainer,
///   onAdd: vm.addContainer,
///   onRemove: vm.removeContainer,
///   onUpdate: vm.updateContainer,
/// )
/// ```
class ContainerListBuilder extends StatelessWidget {
  final List<ContainerEntry> containers;
  final bool canAdd;
  final VoidCallback onAdd;
  final void Function(int index) onRemove;
  final void Function(int index, ContainerEntry updated) onUpdate;

  const ContainerListBuilder({
    super.key,
    required this.containers,
    required this.canAdd,
    required this.onAdd,
    required this.onRemove,
    required this.onUpdate,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header row with count badge
        Row(
          children: [
            const Text('Daftar Container', style: AppTextStyles.heading3),
            const SizedBox(width: 8),
            _CountBadge(count: containers.length),
            const Spacer(),
          ],
        ),
        const SizedBox(height: 12),

        // List of container cards
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: containers.length,
          separatorBuilder: (_, _) => const SizedBox(height: 10),
          itemBuilder: (context, index) {
            return _ContainerEntryCard(
              index: index,
              entry: containers[index],
              showRemove: containers.length > 1,
              onRemove: () => onRemove(index),
              onChanged: (updated) => onUpdate(index, updated),
            );
          },
        ),

        const SizedBox(height: 12),

        // Add button — hidden when cap is reached
        if (canAdd)
          AppButton(
            label: 'Tambah Container',
            onPressed: onAdd,
            variant: AppButtonVariant.outline,
            leadingIcon: Icons.add_circle_outline,
          )
        else
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.warning.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: AppColors.warning.withValues(alpha: 0.3)),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline, color: AppColors.warning, size: 16),
                const SizedBox(width: 8),
                Text(
                  'Batas maksimal ${AppConstants.maxContainers} container telah tercapai',
                  style: AppTextStyles.caption.copyWith(color: AppColors.warning),
                ),
              ],
            ),
          ),
      ],
    );
  }
}

/// A single container entry card with all its input fields.
class _ContainerEntryCard extends StatefulWidget {
  final int index;
  final ContainerEntry entry;
  final bool showRemove;
  final VoidCallback onRemove;
  final void Function(ContainerEntry updated) onChanged;

  const _ContainerEntryCard({
    required this.index,
    required this.entry,
    required this.showRemove,
    required this.onRemove,
    required this.onChanged,
  });

  @override
  State<_ContainerEntryCard> createState() => _ContainerEntryCardState();
}

class _ContainerEntryCardState extends State<_ContainerEntryCard> {
  late final TextEditingController _numberController;

  @override
  void initState() {
    super.initState();
    _numberController = TextEditingController(
      text: widget.entry.containerNumber ?? '',
    );
  }

  @override
  void dispose() {
    _numberController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Card header
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  'Container ${widget.index + 1}',
                  style: AppTextStyles.label.copyWith(color: AppColors.primary),
                ),
              ),
              const Spacer(),
              if (widget.showRemove)
                AppIconButton(
                  icon: Icons.delete_outline,
                  onPressed: widget.onRemove,
                  color: AppColors.error,
                  backgroundColor: AppColors.error.withValues(alpha: 0.08),
                  tooltip: 'Hapus container ini',
                ),
            ],
          ),
          const SizedBox(height: 14),

          // Container Size
          AppDropdown<String>(
            label: 'Ukuran Container',
            value: widget.entry.containerSize,
            items: AppConstants.containerSizes,
            itemLabel: (s) => s,
            onChanged: (v) => widget.onChanged(
              widget.entry.copyWith(containerSize: v),
            ),
            validator: (v) => AppValidators.requiredDropdown(
              v,
              fieldName: 'Ukuran Container',
            ),
          ),
          const SizedBox(height: 12),

          // Container Number
          AppTextField(
            label: 'Nomor Container',
            hint: 'Contoh: ABCD 123456 7',
            controller: _numberController,
            onChanged: (v) => widget.onChanged(
              widget.entry.copyWith(containerNumber: v),
            ),
            validator: (v) => AppValidators.required(
              v,
              fieldName: 'Nomor Container',
            ),
          ),

        ],
      ),
    );
  }
}



class _CountBadge extends StatelessWidget {
  final int count;

  const _CountBadge({required this.count});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: AppColors.primaryLight.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        '$count',
        style: AppTextStyles.label.copyWith(color: AppColors.primaryLight),
      ),
    );
  }
}
