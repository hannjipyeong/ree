import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/components/app_button.dart';
import 'package:bkj_app/core/components/app_text_field.dart';
import 'package:bkj_app/core/components/app_file_upload_tile.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/all_in/models/cargo_entry.dart';

/// Dynamic list builder for cargo entries.
class CargoListBuilder extends StatelessWidget {
  final List<CargoEntry> cargos;
  final bool showContainerField;
  final VoidCallback onAdd;
  final void Function(int index) onRemove;
  final void Function(int index, CargoEntry updated) onUpdate;

  const CargoListBuilder({
    super.key,
    required this.cargos,
    this.showContainerField = false,
    required this.onAdd,
    required this.onRemove,
    required this.onUpdate,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Text('Daftar Rincian Cargo', style: AppTextStyles.heading3),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: AppColors.primaryLight.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                '${cargos.length} item',
                style: AppTextStyles.caption.copyWith(
                  color: AppColors.primary,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),

        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: cargos.length,
          separatorBuilder: (_, _) => const SizedBox(height: 12),
          itemBuilder: (context, index) {
            return _CargoEntryCard(
              index: index,
              entry: cargos[index],
              showContainerField: showContainerField,
              showRemove: cargos.length > 1,
              onRemove: () => onRemove(index),
              onChanged: (updated) => onUpdate(index, updated),
            );
          },
        ),

        const SizedBox(height: 12),

        AppButton(
          label: 'Tambah Item Cargo',
          onPressed: onAdd,
          variant: AppButtonVariant.outline,
          leadingIcon: Icons.add_circle_outline,
        ),
      ],
    );
  }
}

class _CargoEntryCard extends StatelessWidget {
  final int index;
  final CargoEntry entry;
  final bool showContainerField;
  final bool showRemove;
  final VoidCallback onRemove;
  final void Function(CargoEntry updated) onChanged;

  const _CargoEntryCard({
    required this.index,
    required this.entry,
    required this.showContainerField,
    required this.showRemove,
    required this.onRemove,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 26,
                height: 26,
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                alignment: Alignment.center,
                child: Text(
                  '${index + 1}',
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppColors.primary,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Text('Cargo #${index + 1}', style: AppTextStyles.body1.copyWith(fontWeight: FontWeight.w600)),
              const Spacer(),
              if (showRemove)
                IconButton(
                  icon: const Icon(Icons.delete_outline, color: AppColors.error, size: 20),
                  onPressed: onRemove,
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
                  tooltip: 'Hapus item cargo',
                ),
            ],
          ),
          const SizedBox(height: 12),

          if (showContainerField) ...[
            AppTextField(
              label: 'Nomor Container (Opsional)',
              hint: 'Contoh: TGHU1234567 (isi "-" jika tidak ada)',
              initialValue: entry.nomorContainerCargo,
              onChanged: (v) => onChanged(entry.copyWith(nomorContainerCargo: v)),
            ),
            const SizedBox(height: 12),
          ],

          AppTextField(
            label: 'Jenis Barang',
            hint: 'Contoh: Besi / Pallet (isi "-" jika tidak ada)',
            initialValue: entry.jenisBarang,
            onChanged: (v) => onChanged(entry.copyWith(jenisBarang: v)),
            validator: (v) => AppValidators.required(v, fieldName: 'Jenis Barang'),
          ),
          const SizedBox(height: 12),

          AppTextField(
            label: 'Jumlah Barang',
            hint: 'Contoh: 500 Dus (isi "-" jika tidak ada)',
            initialValue: entry.jumlahBarang,
            onChanged: (v) => onChanged(entry.copyWith(jumlahBarang: v)),
            validator: (v) => AppValidators.required(v, fieldName: 'Jumlah Barang'),
          ),
          const SizedBox(height: 12),

          AppTextField(
            label: 'Jumlah Tonase (Ton)',
            hint: 'Contoh: 10.5 (isi "-" jika tidak ada)',
            initialValue: entry.jumlahTonase,
            onChanged: (v) => onChanged(entry.copyWith(jumlahTonase: v)),
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            validator: (v) => AppValidators.required(v, fieldName: 'Jumlah Tonase'),
          ),
          const SizedBox(height: 12),

          AppTextField(
            label: 'Nomor BL',
            hint: 'Masukkan no. BL (isi "-" jika tidak ada)',
            initialValue: entry.nomorBl,
            onChanged: (v) => onChanged(entry.copyWith(nomorBl: v)),
            validator: (v) => AppValidators.required(v, fieldName: 'Nomor BL'),
          ),
          const SizedBox(height: 12),

          AppTextField(
            label: 'Vessel (Nama Kapal)',
            hint: 'Masukkan nama kapal (isi "-" jika tidak ada)',
            initialValue: entry.vessel,
            onChanged: (v) => onChanged(entry.copyWith(vessel: v)),
            validator: (v) => AppValidators.required(v, fieldName: 'Vessel / Nama Kapal'),
          ),
          const SizedBox(height: 12),

          AppTextField(
            label: 'Voyage (Kode Keberangkatan)',
            hint: 'Contoh: V.024N (isi "-" jika tidak ada)',
            initialValue: entry.voyage,
            onChanged: (v) => onChanged(entry.copyWith(voyage: v)),
            validator: (v) => AppValidators.required(v, fieldName: 'Voyage'),
          ),
          const SizedBox(height: 12),

          AppTextField(
            label: 'No. Surat Jalan',
            hint: 'Masukkan no. surat jalan (isi "-" jika tidak ada)',
            initialValue: entry.noSuratJalan,
            onChanged: (v) => onChanged(entry.copyWith(noSuratJalan: v)),
            validator: (v) => AppValidators.required(v, fieldName: 'No. Surat Jalan'),
          ),
          const SizedBox(height: 12),

          AppTextField(
            label: 'No. BP (Plat Nomor)',
            hint: 'Contoh: BP 1234 XY (isi "-" jika tidak ada)',
            initialValue: entry.noBp,
            onChanged: (v) => onChanged(entry.copyWith(noBp: v)),
            validator: (v) => AppValidators.required(v, fieldName: 'No. BP'),
          ),
          const SizedBox(height: 12),

          AppFileUploadTile(
            label: 'Manifest / Dokumen Cargo #${index + 1}',
            hint: 'Upload manifest cargo (PDF/JPG/PNG)',
            fileName: entry.cargoFileName,
            allowedExtensions: const ['pdf', 'jpg', 'jpeg', 'png'],
            onFileSelected: (name, bytes, path) => onChanged(
              entry.copyWith(
                cargoFileName: name,
                cargoFilePath: path ?? '',
                cargoFileBytes: bytes,
              ),
            ),
            onCleared: () => onChanged(entry.copyWith(clearFile: true)),
          ),
        ],
      ),
    );
  }
}
