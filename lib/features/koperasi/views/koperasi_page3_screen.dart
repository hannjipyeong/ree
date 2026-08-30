import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart' as bkj_app;
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/koperasi/viewmodels/koperasi_viewmodel.dart';

/// Koperasi — Page 3: Additional services (Railing, LOLO, dll)
class KoperasiPage3Screen extends StatefulWidget {
  const KoperasiPage3Screen({super.key});

  @override
  State<KoperasiPage3Screen> createState() => _KoperasiPage3ScreenState();
}

class _KoperasiPage3ScreenState extends State<KoperasiPage3Screen> {
  final _formKey = GlobalKey<FormState>();

  void _handleSubmit() async {
    final vm = context.read<KoperasiViewModel>();

    if (vm.selectedServices.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih minimal 1 layanan tambahan.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    // Koperasi only has TKBM and Asuransi, so we removed Railing check.

    if (vm.isServiceSelected(AppConstants.serviceTKBM) &&
        vm.tkbmOption == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih opsi untuk layanan TKBM.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    if (_formKey.currentState!.validate()) {
      final success = await vm.submitOrder();
      if (!mounted) return;
      if (success) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => const _SuccessDialog(),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(vm.errorMessage ?? 'Terjadi kesalahan.'),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<KoperasiViewModel>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Order Koperasi'),
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
          nextLabel: 'Submit Order',
          isLoading: vm.isSubmitting,
        ),
        children: [
          FormStepIndicator(
            currentStep: 3,
            totalSteps: AppConstants.totalStepsKoperasi,
            stepLabel: 'Layanan Tambahan',
          ),
          const SizedBox(height: 4),
          const FormInfoBanner(
            message: 'Pilih layanan tambahan yang diperlukan. '
                'Beberapa layanan memerlukan dokumen tambahan.',
          ),
          
          SectionCard(
            title: 'Daftar Layanan',
            icon: Icons.add_task_outlined,
            children: [
              // Koperasi only has TKBM and Asuransi
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceTKBM,
                label: 'TKBM',
                icon: Icons.people_alt_outlined,
                isSelected: vm.isServiceSelected(AppConstants.serviceTKBM),
                onToggle: () => vm.toggleService(AppConstants.serviceTKBM),
                expandedChild: AppRadioGroup<String>(
                  label: 'Opsi TKBM',
                  groupValue: vm.tkbmOption,
                  options: AppConstants.tkbmOptions,
                  optionLabel: (v) => v,
                  onChanged: vm.setTbkmOption,
                  validator: (v) => AppValidators.requiredDropdown(
                    v,
                    fieldName: 'Opsi TKBM',
                  ),
                ),
              ),
              const Divider(height: 1),
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceAsuransi,
                label: vm.isServiceSelected(AppConstants.serviceAsuransi)
                    ? 'Asuransi Liability'
                    : 'Asuransi',
                icon: Icons.shield_outlined,
                isSelected:
                    vm.isServiceSelected(AppConstants.serviceAsuransi),
                onToggle: () => _toggleAsuransiWithConfirmation(vm),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _toggleAsuransiWithConfirmation(KoperasiViewModel vm) {
    final isSelected = vm.isServiceSelected(AppConstants.serviceAsuransi);
    if (!isSelected) {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.shield_outlined, color: AppColors.primary),
              SizedBox(width: 8),
              Text('Konfirmasi Asuransi', style: AppTextStyles.heading3),
            ],
          ),
          content: const Text(
            'Apakah Anda yakin ingin menambahkan perlindungan Asuransi untuk pengiriman order ini?',
            style: AppTextStyles.body2,
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Batal', style: TextStyle(color: Colors.grey)),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
              onPressed: () {
                Navigator.pop(ctx);
                vm.toggleService(AppConstants.serviceAsuransi);
              },
              child: const Text('Ya, Gunakan Asuransi', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      );
    } else {
      vm.toggleService(AppConstants.serviceAsuransi);
    }
  }
}

/// Success dialog shown after order submission.
class _SuccessDialog extends StatelessWidget {
  const _SuccessDialog();

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      contentPadding: const EdgeInsets.all(24),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.success.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.check_circle,
                color: AppColors.success, size: 48),
          ),
          const SizedBox(height: 16),
          const Text(
            'Order Berhasil!',
            style: AppTextStyles.heading2,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            'Order Koperasi Anda telah berhasil dikirim dan sedang diproses.',
            style: AppTextStyles.body2.copyWith(color: AppColors.textSecondary),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                final authVm = context.read<bkj_app.AuthViewModel>();
                context.read<KoperasiViewModel>().resetForm(
                  defaultNamaPt: authVm.defaultNamaPt,
                  hasDefaultAsuransi: authVm.hasDefaultAsuransi,
                );
                Navigator.of(context).pushNamedAndRemoveUntil(
                  AppRoutes.shell,
                  (route) => false,
                );
              },
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: const Text('Kembali ke Beranda'),
            ),
          ),
        ],
      ),
    );
  }
}
