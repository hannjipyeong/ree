import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/pbm_lain/viewmodels/pbm_lain_viewmodel.dart';

/// LOLO — Page 2: Payload selection (Container or Cargo).
class PbmLainPage2Screen extends StatefulWidget {
  const PbmLainPage2Screen({super.key});

  @override
  State<PbmLainPage2Screen> createState() => _PbmLainPage2ScreenState();
}

class _PbmLainPage2ScreenState extends State<PbmLainPage2Screen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _jenisBarangCtrl;
  late final TextEditingController _jumlahBarangCtrl;
  late final TextEditingController _jumlahTonaseCtrl;
  late final TextEditingController _nomorBlCtrl;
  late final TextEditingController _vesselCtrl;
  late final TextEditingController _voyageCtrl;
  late final TextEditingController _noSuratJalanCtrl;
  late final TextEditingController _noBpCtrl;
  late final TextEditingController _nomorContainerCargoCtrl;

  @override
  void initState() {
    super.initState();
    final vm = context.read<PbmLainViewModel>();
    _jenisBarangCtrl = TextEditingController(text: vm.jenisBarang ?? '');
    _jumlahBarangCtrl = TextEditingController(text: vm.jumlahBarang ?? '');
    _jumlahTonaseCtrl = TextEditingController(text: vm.jumlahTonase ?? '');
    _nomorBlCtrl = TextEditingController(text: vm.nomorBl ?? '');
    _vesselCtrl = TextEditingController(text: vm.vessel ?? '');
    _voyageCtrl = TextEditingController(text: vm.voyage ?? '');
    _noSuratJalanCtrl = TextEditingController(text: vm.noSuratJalan ?? '');
    _noBpCtrl = TextEditingController(text: vm.noBp ?? '');
    _nomorContainerCargoCtrl = TextEditingController(text: vm.nomorContainerCargo ?? '');
  }

  @override
  void dispose() {
    _jenisBarangCtrl.dispose();
    _jumlahBarangCtrl.dispose();
    _jumlahTonaseCtrl.dispose();
    _nomorBlCtrl.dispose();
    _vesselCtrl.dispose();
    _voyageCtrl.dispose();
    _noSuratJalanCtrl.dispose();
    _noBpCtrl.dispose();
    _nomorContainerCargoCtrl.dispose();
    super.dispose();
  }

  void _handleNext() {
    final vm = context.read<PbmLainViewModel>();

    if (!vm.hasContainer && !vm.hasCargo) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih minimal satu tipe muatan (Container atau Cargo).'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    // Validate Cargo if chosen
    if (vm.hasCargo) {
      if (vm.cargoFileName == null || vm.cargoFileName!.isEmpty) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Silakan upload dokumen manifest cargo terlebih dahulu.'),
            backgroundColor: AppColors.error,
          ),
        );
        return;
      }
      
      vm.setJenisBarang(_jenisBarangCtrl.text);
      vm.setJumlahBarang(_jumlahBarangCtrl.text);
      vm.setJumlahTonase(_jumlahTonaseCtrl.text);
      vm.setNomorBl(_nomorBlCtrl.text);
      vm.setVessel(_vesselCtrl.text);
      vm.setVoyage(_voyageCtrl.text);
      vm.setNoSuratJalan(_noSuratJalanCtrl.text);
      vm.setNoBp(_noBpCtrl.text);
      vm.setNomorContainerCargo(_nomorContainerCargoCtrl.text);
    }

    if (_formKey.currentState!.validate()) {
      Navigator.pushNamed(context, AppRoutes.pbmLainPage3);
    }
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<PbmLainViewModel>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Order LOLO'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: FormPageWrapper(
        formKey: _formKey,
        bottomBar: FormNavigationBar(
          onBack: () => Navigator.pop(context),
          onNext: _handleNext,
        ),
        children: [
          FormStepIndicator(
            currentStep: 2,
            totalSteps: AppConstants.totalStepsPbmLain,
            stepLabel: 'Pilihan Muatan',
          ),
          
          // ── Payload Type Selector ──────────────────────────────────────────
          SectionCard(
            title: 'Tipe Muatan',
            icon: Icons.inventory_2_outlined,
            children: [
              _PayloadTypeMultiSelector(
                selectedTypes: vm.payloadTypes,
                onToggle: vm.togglePayloadType,
              ),
            ],
          ),

          // ── Dynamic Content based on Payload Type ─────────────────────────
          if (vm.hasContainer)
            SectionCard(
              title: 'Detail Container',
              icon: Icons.view_module_outlined,
              children: [
                ContainerListBuilder(
                  containers: vm.containers,
                  canAdd: vm.canAddContainer,
                  onAdd: vm.addContainer,
                  onRemove: vm.removeContainer,
                  onUpdate: vm.updateContainer,
                ),
              ],
            ),
          
          if (vm.hasCargo)
            SectionCard(
              title: 'Dokumen & Rincian Cargo',
              icon: Icons.description_outlined,
              children: [
                if (vm.wilayah == AppConstants.wilayahEximen && vm.lokasiFasilitas?.toLowerCase() == 'gudang')
                  AppTextField(
                    label: 'Nomor Container (Opsional)',
                    hint: 'Masukkan nomor container',
                    controller: _nomorContainerCargoCtrl,
                  ),
                AppTextField(
                  label: 'Jenis Barang',
                  hint: 'Masukkan jenis barang',
                  controller: _jenisBarangCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'Jenis Barang'),
                ),
                AppTextField(
                  label: 'Jumlah Barang',
                  hint: 'Contoh: 500 Dus / 20 Pallet',
                  controller: _jumlahBarangCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'Jumlah Barang'),
                ),
                AppTextField(
                  label: 'Jumlah Tonase (Ton)',
                  hint: 'Contoh: 10.5',
                  controller: _jumlahTonaseCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  validator: (v) => AppValidators.required(v, fieldName: 'Jumlah Tonase'),
                ),
                AppTextField(
                  label: 'Nomor BL',
                  hint: 'Masukkan nomor Bill of Lading',
                  controller: _nomorBlCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'Nomor BL'),
                ),
                AppTextField(
                  label: 'Vessel (Nama Kapal)',
                  hint: 'Masukkan nama kapal',
                  controller: _vesselCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'Vessel / Nama Kapal'),
                ),
                AppTextField(
                  label: 'Voyage (Kode Keberangkatan)',
                  hint: 'Contoh: V.024N',
                  controller: _voyageCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'Voyage'),
                ),
                AppTextField(
                  label: 'No. Surat Jalan',
                  hint: 'Masukkan no. surat jalan',
                  controller: _noSuratJalanCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'No. Surat Jalan'),
                ),
                AppTextField(
                  label: 'No. BP (Plat Nomor)',
                  hint: 'Contoh: BP 1234 XY',
                  controller: _noBpCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'No. BP'),
                ),
                const FormInfoBanner(
                  message: 'Upload manifest atau dokumen cargo dalam format PDF, JPG, atau PNG.',
                  icon: Icons.info_outline,
                ),
                const SizedBox(height: 8),
                AppFileUploadTile(
                  label: 'Manifest / Dokumen Cargo',
                  hint: 'Upload manifest cargo',
                  fileName: vm.cargoFileName,
                  allowedExtensions: const ['pdf', 'jpg', 'jpeg', 'png'],
                  onFileSelected: (name, bytes, path) =>
                      vm.setCargoFile(name: name, path: path ?? '', bytes: bytes),
                  onCleared: vm.clearCargoFile,
                ),
              ],
            ),
        ],
      ),
    );
  }
}

/// Multi-select toggle between Container and Cargo payload types.
class _PayloadTypeMultiSelector extends StatelessWidget {
  final Set<String> selectedTypes;
  final void Function(String) onToggle;

  const _PayloadTypeMultiSelector({
    required this.selectedTypes,
    required this.onToggle,
  });

  @override
  Widget build(BuildContext context) {
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
        Expanded(
          child: _PayloadOption(
            label: 'Container',
            icon: Icons.view_module_outlined,
            description: 'Satuan container',
            isSelected: selectedTypes.contains(AppConstants.payloadContainer),
            onTap: () => onToggle(AppConstants.payloadContainer),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _PayloadOption(
            label: 'Cargo',
            icon: Icons.inventory_outlined,
            description: 'Muatan curah /\nUpload manifest',
            isSelected: selectedTypes.contains(AppConstants.payloadCargo),
            onTap: () => onToggle(AppConstants.payloadCargo),
          ),
        ),
      ],
    ));
  }
}

class _PayloadOption extends StatelessWidget {
  final String label;
  final IconData icon;
  final String description;
  final bool isSelected;
  final VoidCallback onTap;

  const _PayloadOption({
    required this.label,
    required this.icon,
    required this.description,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isSelected
              ? AppColors.primary.withValues(alpha: 0.06)
              : AppColors.background,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? AppColors.primary : AppColors.divider,
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Column(
          children: [
            Icon(
              icon,
              size: 32,
              color: isSelected ? AppColors.primary : AppColors.textSecondary,
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: AppTextStyles.body1.copyWith(
                fontWeight: FontWeight.w700,
                color: isSelected ? AppColors.primary : AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              description,
              style: AppTextStyles.caption,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 10),
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(6),
                color: isSelected ? AppColors.primary : Colors.transparent,
                border: Border.all(
                  color: isSelected ? AppColors.primary : AppColors.disabled,
                  width: 2,
                ),
              ),
              child: isSelected
                  ? const Icon(Icons.check, size: 16, color: Colors.white)
                  : null,
            ),
          ],
        ),
      ),
    );
  }
}
