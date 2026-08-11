import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/profile/viewmodels/profile_viewmodel.dart';

/// Change Password screen — old password + new password + confirm.
class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _oldCtrl = TextEditingController();
  final _newCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();

  @override
  void dispose() {
    _oldCtrl.dispose();
    _newCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) return;
    final vm = context.read<ProfileViewModel>();
    final success = await vm.changePassword(
      oldPassword: _oldCtrl.text,
      newPassword: _newCtrl.text,
    );
    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(vm.successMessage ?? 'Password diubah'), backgroundColor: AppColors.success),
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
      appBar: AppBar(title: const Text('Ganti Password')),
      body: FormPageWrapper(
        formKey: _formKey,
        bottomBar: AppButton(
          label: 'Simpan Password Baru',
          onPressed: _handleSave,
          isLoading: vm.isUpdating,
          leadingIcon: Icons.lock_outline,
        ),
        children: [
          const FormInfoBanner(
            message: 'Password baru minimal 8 karakter. Gunakan kombinasi huruf dan angka.',
            icon: Icons.security_outlined,
          ),
          SectionCard(
            title: 'Ubah Password',
            icon: Icons.lock_outline,
            children: [
              AppTextField(
                label: 'Password Lama',
                hint: 'Masukkan password saat ini',
                controller: _oldCtrl,
                obscureText: true,
                validator: (v) => AppValidators.required(v, fieldName: 'Password Lama'),
              ),
              AppTextField(
                label: 'Password Baru',
                hint: 'Minimal 8 karakter',
                controller: _newCtrl,
                obscureText: true,
                validator: AppValidators.password,
              ),
              AppTextField(
                label: 'Konfirmasi Password Baru',
                hint: 'Ulangi password baru',
                controller: _confirmCtrl,
                obscureText: true,
                textInputAction: TextInputAction.done,
                validator: (v) => AppValidators.confirmPassword(v, _newCtrl.text),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
