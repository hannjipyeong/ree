import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/all_in/viewmodels/all_in_viewmodel.dart';

/// ALL IN — Page 2: Payload selection (Container or Cargo).
/// Container path: dynamic list builder (up to 60 items).
/// Cargo path: single file upload.
class AllInPage2Screen extends StatefulWidget {
  const AllInPage2Screen({super.key});

  @override
  State<AllInPage2Screen> createState() => _AllInPage2ScreenState();
}

class _AllInPage2ScreenState extends State<AllInPage2Screen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _jenisBarangCtrl;
  late final TextEditingController _jumlahTonaseCtrl;
  late final TextEditingController _nomorContainerCargoCtrl;

  @override
  void initState() {
    super.initState();
    final vm = context.read<AllInViewModel>();
    _jenisBarangCtrl = TextEditingController(text: vm.jenisBarang ?? '');
    _jumlahTonaseCtrl = TextEditingController(text: vm.jumlahTonase ?? '');
    _nomorContainerCargoCtrl = TextEditingController(text: vm.nomorContainerCargo ?? '');
  }

  @override
  void dispose() {
    _jenisBarangCtrl.dispose();
    _jumlahTonaseCtrl.dispose();
    _nomorContainerCargoCtrl.dispose();
    super.dispose();
  }

  void _handleNext() {
    final vm = context.read<AllInViewModel>();

    // Validate Cargo if chosen
    if (vm.payloadType == AppConstants.payloadCargo) {
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
      vm.setJumlahTonase(_jumlahTonaseCtrl.text);
      vm.setNomorContainerCargo(_nomorContainerCargoCtrl.text);
    }

    if (_formKey.currentState!.validate()) {
      Navigator.pushNamed(context, AppRoutes.allInPage3);
    }
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<AllInViewModel>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Order ALL IN'),
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
            totalSteps: AppConstants.totalStepsAllIn,
            stepLabel: 'Pilihan Muatan',
          ),

          // ── Payload Type Selector ──────────────────────────────────────────
          SectionCard(
            title: 'Tipe Muatan',
            icon: Icons.inventory_2_outlined,
            children: [
              _PayloadTypeSelector(
                selected: vm.payloadType,
                onSelected: vm.setPayloadType,
              ),
            ],
          ),

          // ── Dynamic Content based on Payload Type ─────────────────────────
          if (vm.payloadType == AppConstants.payloadContainer)
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
            )
          else
            SectionCard(
              title: 'Dokumen Cargo',
              icon: Icons.description_outlined,
              children: [
                if (vm.wilayah == AppConstants.wilayahEximen && vm.lokasiFasilitas?.toLowerCase() == 'gudang')
                  Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: AppTextField(
                      label: 'Nomor Container (Opsional)',
                      hint: 'Masukkan nomor container',
                      controller: _nomorContainerCargoCtrl,
                    ),
                  ),
                AppTextField(
                  label: 'Jenis Barang',
                  hint: 'Masukkan jenis barang',
                  controller: _jenisBarangCtrl,
                  validator: (v) => AppValidators.required(v, fieldName: 'Jenis Barang'),
                ),
                const SizedBox(height: 16),
                AppTextField(
                  label: 'Jumlah Tonase (Ton)',
                  hint: 'Contoh: 10.5',
                  controller: _jumlahTonaseCtrl,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  validator: (v) => AppValidators.required(v, fieldName: 'Jumlah Tonase'),
                ),
                const SizedBox(height: 16),
                const FormInfoBanner(
                  message: 'Upload manifest atau dokumen cargo dalam format PDF, JPG, atau PNG.',
                  icon: Icons.info_outline,
                ),
                const SizedBox(height: 8),
                AppFileUploadTile(
                  label: 'Manifest / Dokumen Cargo',
                  hint: 'Upload manifest cargo',
                  fileName: vm.cargoFileName,
                  allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
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

/// Toggle selector between Container and Cargo payload types.
class _PayloadTypeSelector extends StatelessWidget {
  final String selected;
  final void Function(String) onSelected;

  const _PayloadTypeSelector({
    required this.selected,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _PayloadOption(
            label: 'Container',
            icon: Icons.view_module_outlined,
            description: 'Satuan container',
            isSelected: selected == AppConstants.payloadContainer,
            onTap: () => onSelected(AppConstants.payloadContainer),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _PayloadOption(
            label: 'Cargo',
            icon: Icons.inventory_outlined,
            description: 'Muatan curah /\nUpload manifest',
            isSelected: selected == AppConstants.payloadCargo,
            onTap: () => onSelected(AppConstants.payloadCargo),
          ),
        ),
      ],
    );
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
                shape: BoxShape.circle,
                color: isSelected ? AppColors.primary : Colors.transparent,
                border: Border.all(
                  color: isSelected ? AppColors.primary : AppColors.disabled,
                  width: 2,
                ),
              ),
              child: isSelected
                  ? const Icon(Icons.check, size: 14, color: Colors.white)
                  : null,
            ),
          ],
        ),
      ),
    );
  }
}
