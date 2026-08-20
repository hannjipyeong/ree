import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/pbm_lain/viewmodels/pbm_lain_viewmodel.dart';

/// LOLO — Page 1: Informasi Dasar Order (Wilayah, Nama PT, Lokasi).
class PbmLainPage1Screen extends StatefulWidget {
  const PbmLainPage1Screen({super.key});

  @override
  State<PbmLainPage1Screen> createState() => _PbmLainPage1ScreenState();
}

class _PbmLainPage1ScreenState extends State<PbmLainPage1Screen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _namaPtCtrl;
  late final TextEditingController _namaPbmCtrl;
  late final TextEditingController _noTelpCtrl;

  @override
  void initState() {
    super.initState();
    final vm = context.read<PbmLainViewModel>();
    _namaPtCtrl = TextEditingController(text: vm.namaPt ?? '');
    _namaPbmCtrl = TextEditingController(text: vm.namaPbm);
    _noTelpCtrl = TextEditingController(text: vm.noTelp ?? '');
  }

  @override
  void dispose() {
    _namaPtCtrl.dispose();
    _namaPbmCtrl.dispose();
    _noTelpCtrl.dispose();
    super.dispose();
  }

  void _handleNext() {
    final vm = context.read<PbmLainViewModel>();
    vm.setNamaPt(_namaPtCtrl.text);
    vm.setNamaPbm(_namaPbmCtrl.text);
    vm.setNoTelp(_noTelpCtrl.text);

    if (_formKey.currentState!.validate()) {
      Navigator.pushNamed(context, AppRoutes.pbmLainPage2);
    }
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<PbmLainViewModel>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Order LOLO'),
        leading: Navigator.canPop(context)
            ? IconButton(
                icon: const Icon(Icons.arrow_back_ios),
                onPressed: () => Navigator.pop(context),
              )
            : null,
      ),
      body: FormPageWrapper(
        formKey: _formKey,
        bottomBar: FormNavigationBar(onNext: _handleNext),
        children: [
          FormStepIndicator(
            currentStep: 1,
            totalSteps: AppConstants.totalStepsPbmLain,
            stepLabel: 'Informasi Order & Wilayah',
          ),
          const SizedBox(height: 4),
          const FormInfoBanner(
            message: 'Isi informasi dasar order dan wilayah operasional. '
                'Jenis kegiatan akan terisi otomatis.',
          ),

          // ── Section 1: Informasi Dasar ──────────────────────────────────────
          SectionCard(
            title: 'Informasi Dasar',
            icon: Icons.assignment_outlined,
            children: [
              AppDatePicker(
                label: 'Tanggal Order',
                selectedDate: vm.tanggalOrder,
                onDateSelected: vm.setTanggalOrder,
                formatDate: AppFormatters.toDDMMYYYY,
                validator: (d) => d == null ? 'Tanggal Order wajib diisi' : null,
              ),
              AppTextField(
                label: 'Nama PT',
                hint: 'Masukkan nama PT',
                controller: _namaPtCtrl,
                validator: (v) => AppValidators.required(v, fieldName: 'Nama PT'),
              ),
              AppTextField(
                label: 'Nama PBM',
                hint: 'Masukkan nama PBM',
                controller: _namaPbmCtrl,
                validator: (v) => AppValidators.required(v, fieldName: 'Nama PBM'),
              ),
              AppTextField(
                label: 'No Telp',
                hint: 'Contoh: 081234567890',
                controller: _noTelpCtrl,
                keyboardType: TextInputType.phone,
                validator: AppValidators.phoneNumber,
              ),
            ],
          ),

          // ── Section 2: Wilayah & Lokasi Fasilitas ───────────────────────────
          SectionCard(
            title: 'Wilayah Operasional',
            icon: Icons.location_on_outlined,
            children: [
              AppDropdown<String>(
                label: 'Wilayah Operasional',
                value: vm.wilayah,
                items: AppConstants.wilayahPbmLain,
                itemLabel: (v) => v,
                onChanged: vm.setWilayah,
                validator: (v) => AppValidators.requiredDropdown(
                  v,
                  fieldName: 'Wilayah Operasional',
                ),
              ),
              AppConditionalDropdown<String>(
                label: 'Lokasi Fasilitas',
                value: vm.lokasiFasilitas,
                items: vm.availableLokasi,
                itemLabel: (v) => v.toUpperCase(),
                onChanged: vm.setLokasiFasilitas,
                validator: (v) => AppValidators.requiredDropdown(
                  v,
                  fieldName: 'Lokasi Fasilitas',
                ),
                emptyHint: 'Pilih Wilayah Operasional terlebih dahulu',
              ),
              if (vm.jenisKegiatan != null)
                Padding(
                  padding: const EdgeInsets.only(top: 16),
                  child: AppTextField(
                    label: 'Jenis Kegiatan',
                    controller: TextEditingController(
                      text: vm.jenisKegiatan!.toUpperCase(),
                    ),
                    readOnly: true,
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
