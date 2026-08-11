import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

import 'package:bkj_app/features/profile/viewmodels/profile_viewmodel.dart';

/// Change PIN screen — 6-digit old PIN + 6-digit new PIN entry.
class ChangePinScreen extends StatefulWidget {
  const ChangePinScreen({super.key});

  @override
  State<ChangePinScreen> createState() => _ChangePinScreenState();
}

class _ChangePinScreenState extends State<ChangePinScreen> {
  final _formKey = GlobalKey<FormState>();
  String _oldPin = '';
  String _newPin = '';

  Future<void> _handleSave() async {
    if (_oldPin.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Masukkan PIN lama (6 digit)'),
        backgroundColor: AppColors.error,
      ));
      return;
    }
    if (_newPin.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Masukkan PIN baru (6 digit)'),
        backgroundColor: AppColors.error,
      ));
      return;
    }
    final vm = context.read<ProfileViewModel>();
    final success = await vm.changePin(oldPin: _oldPin, newPin: _newPin);
    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(vm.successMessage ?? 'PIN diubah'), backgroundColor: AppColors.success),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(vm.errorMessage ?? 'Gagal'), backgroundColor: AppColors.error),
      );
    }
    vm.clearMessages();
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<ProfileViewModel>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Ganti PIN')),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const FormInfoBanner(
                message: 'PIN adalah 6 digit angka yang digunakan untuk '
                    'konfirmasi transaksi.',
                icon: Icons.pin_outlined,
              ),
              const SizedBox(height: 16),
              SectionCard(
                title: 'PIN Lama',
                icon: Icons.lock_clock_outlined,
                children: [
                  AppPinField(
                    onCompleted: (pin) => setState(() => _oldPin = pin),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              SectionCard(
                title: 'PIN Baru',
                icon: Icons.lock_outline,
                children: [
                  AppPinField(
                    onCompleted: (pin) => setState(() => _newPin = pin),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              AppButton(
                label: 'Simpan PIN Baru',
                onPressed: _handleSave,
                isLoading: vm.isUpdating,
                leadingIcon: Icons.save_outlined,
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}
