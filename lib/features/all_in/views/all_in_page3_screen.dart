import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/all_in/viewmodels/all_in_viewmodel.dart';

/// ALL IN — Page 3: Multi-select services with conditional sub-options.
/// Services: Haulage (+ file), LOLO, Penumpukan, TBKM (+ radio), Asuransi (+ value)
class AllInPage3Screen extends StatefulWidget {
  const AllInPage3Screen({super.key});

  @override
  State<AllInPage3Screen> createState() => _AllInPage3ScreenState();
}

class _AllInPage3ScreenState extends State<AllInPage3Screen> {
  final _formKey = GlobalKey<FormState>();

  Future<void> _handleSubmit() async {
    final vm = context.read<AllInViewModel>();

    if (vm.selectedServices.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih minimal satu layanan untuk melanjutkan.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    // Validate Haulage file if selected
    if (vm.isServiceSelected(AppConstants.serviceHaulage) &&
        (vm.haulageFileName == null || vm.haulageFileName!.isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Upload dokumen Haulage terlebih dahulu.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    // Validate TBKM option if selected
    if (vm.isServiceSelected(AppConstants.serviceTBKM) &&
        vm.tbkmOption == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih lokasi TBKM terlebih dahulu.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    if (!_formKey.currentState!.validate()) return;

    final success = await vm.submitOrder();
    if (!mounted) return;

    if (success) {
      vm.resetForm();
      _showSuccessDialog();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(vm.errorMessage ?? 'Terjadi kesalahan.'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  void _showSuccessDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => _SuccessDialog(
        onDone: () {
          Navigator.of(context).pushNamedAndRemoveUntil(
            AppRoutes.shell,
            (route) => false,
          );
        },
      ),
    );
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
          onNext: _handleSubmit,
          nextLabel: 'Kirim Order',
          isLoading: vm.isSubmitting,
        ),
        children: [
          FormStepIndicator(
            currentStep: 3,
            totalSteps: AppConstants.totalStepsAllIn,
            stepLabel: 'Pilihan Layanan',
          ),
          const FormInfoBanner(
            message: 'Pilih satu atau lebih layanan tambahan yang dibutuhkan. '
                'Beberapa layanan memerlukan dokumen pendukung.',
          ),

          SectionCard(
            title: 'Layanan Tambahan',
            icon: Icons.miscellaneous_services_outlined,
            spacing: 0,
            children: [
              // ── Haulage ────────────────────────────────────────────────────
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceHaulage,
                label: 'Haulage',
                icon: Icons.local_shipping_outlined,
                isSelected: vm.isServiceSelected(AppConstants.serviceHaulage),
                onToggle: () => vm.toggleService(AppConstants.serviceHaulage),
                expandedChild: AppFileUploadTile(
                  label: 'Dokumen Haulage',
                  hint: 'Upload surat jalan (PDF / JPG / PNG)',
                  fileName: vm.haulageFileName,
                  allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
                  onFileSelected: (n, p) =>
                      vm.setHaulageFile(name: n, path: p),
                  onCleared: vm.clearHaulageFile,
                ),
              ),

              // ── LOLO ───────────────────────────────────────────────────────
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceLolo,
                label: 'LOLO',
                icon: Icons.precision_manufacturing_outlined,
                isSelected: vm.isServiceSelected(AppConstants.serviceLolo),
                onToggle: () => vm.toggleService(AppConstants.serviceLolo),
              ),

              // ── Penumpukan ─────────────────────────────────────────────────
              ServiceCheckboxTile(
                serviceKey: AppConstants.servicePenumpukan,
                label: 'Penumpukan',
                icon: Icons.layers_outlined,
                isSelected:
                    vm.isServiceSelected(AppConstants.servicePenumpukan),
                onToggle: () =>
                    vm.toggleService(AppConstants.servicePenumpukan),
              ),

              // ── TBKM ───────────────────────────────────────────────────────
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceTBKM,
                label: 'TBKM',
                icon: Icons.security_outlined,
                isSelected: vm.isServiceSelected(AppConstants.serviceTBKM),
                onToggle: () => vm.toggleService(AppConstants.serviceTBKM),
                expandedChild: AppRadioGroup<String>(
                  label: 'Lokasi TBKM',
                  groupValue: vm.tbkmOption,
                  options: AppConstants.tbkmOptions,
                  optionLabel: (v) => v,
                  onChanged: vm.setTbkmOption,
                  validator: (v) => AppValidators.requiredDropdown(
                    v,
                    fieldName: 'Lokasi TBKM',
                  ),
                ),
              ),

              // ── Asuransi ───────────────────────────────────────────────────
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceAsuransi,
                label: 'Asuransi',
                icon: Icons.shield_outlined,
                isSelected:
                    vm.isServiceSelected(AppConstants.serviceAsuransi),
                onToggle: () =>
                    vm.toggleService(AppConstants.serviceAsuransi),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

// ─── Private Sub-Widgets ──────────────────────────────────────────────────────



/// Success dialog shown after order submission.
class _SuccessDialog extends StatelessWidget {
  final VoidCallback onDone;

  const _SuccessDialog({required this.onDone});

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: AppColors.success.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.check_circle_outline,
                color: AppColors.success,
                size: 44,
              ),
            ),
            const SizedBox(height: 20),
            const Text('Order Berhasil Dikirim!', style: AppTextStyles.heading2),
            const SizedBox(height: 8),
            Text(
              'Order ALL IN Anda telah berhasil dikirim dan sedang '
              'diproses oleh tim kami.',
              style: AppTextStyles.body2,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 28),
            AppButton(
              label: 'Kembali ke Beranda',
              onPressed: onDone,
              leadingIcon: Icons.home_outlined,
            ),
          ],
        ),
      ),
    );
  }
}
