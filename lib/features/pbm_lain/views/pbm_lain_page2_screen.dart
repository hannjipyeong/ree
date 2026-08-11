import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/features/pbm_lain/viewmodels/pbm_lain_viewmodel.dart';

/// PBM Lain — Page 2: Container Only.
class PbmLainPage2Screen extends StatefulWidget {
  const PbmLainPage2Screen({super.key});

  @override
  State<PbmLainPage2Screen> createState() => _PbmLainPage2ScreenState();
}

class _PbmLainPage2ScreenState extends State<PbmLainPage2Screen> {
  final _formKey = GlobalKey<FormState>();

  void _handleNext() {
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
        title: const Text('Order PBM Lain'),
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
            stepLabel: 'Detail Container',
          ),
          const SizedBox(height: 4),
          const FormInfoBanner(
            message: 'Isi detail container (maksimal 60 container).',
            icon: Icons.info_outline,
          ),
          
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
        ],
      ),
    );
  }
}
