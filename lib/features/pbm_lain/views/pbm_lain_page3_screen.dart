import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/features/pbm_lain/viewmodels/pbm_lain_viewmodel.dart';

/// LOLO — Page 3: Additional services (LOLO, TKBM, Asuransi).
class PbmLainPage3Screen extends StatefulWidget {
  const PbmLainPage3Screen({super.key});

  @override
  State<PbmLainPage3Screen> createState() => _PbmLainPage3ScreenState();
}

class _PbmLainPage3ScreenState extends State<PbmLainPage3Screen> {
  final _formKey = GlobalKey<FormState>();

  void _handleSubmit() async {
    final vm = context.read<PbmLainViewModel>();

    if (vm.selectedServices.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih minimal 1 layanan tambahan.'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

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
          onNext: _handleSubmit,
          nextLabel: 'Submit Order',
          isLoading: vm.isSubmitting,
        ),
        children: [
          FormStepIndicator(
            currentStep: 3,
            totalSteps: AppConstants.totalStepsPbmLain,
            stepLabel: 'Layanan Tambahan',
          ),
          const SizedBox(height: 4),
          const FormInfoBanner(
            message: 'Pilih layanan tambahan yang diperlukan.',
          ),
          
          SectionCard(
            title: 'Daftar Layanan',
            icon: Icons.add_task_outlined,
            children: [
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceLolo,
                label: 'LOLO',
                icon: Icons.precision_manufacturing_outlined,
                isSelected: vm.isServiceSelected(AppConstants.serviceLolo),
                onToggle: () => vm.toggleService(AppConstants.serviceLolo),
              ),
              ServiceCheckboxTile(
                serviceKey: AppConstants.serviceAsuransi,
                label: vm.isServiceSelected(AppConstants.serviceAsuransi)
                    ? 'Asuransi Liability'
                    : 'Asuransi',
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
            'Order LOLO Anda telah berhasil dikirim dan sedang diproses.',
            style: AppTextStyles.body2.copyWith(color: AppColors.textSecondary),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                context.read<PbmLainViewModel>().resetForm();
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
