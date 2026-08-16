import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/profile/viewmodels/profile_viewmodel.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart';

/// Edit Profile screen — allows updating full name, email, and phone number.
class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _phoneCtrl;

  @override
  void initState() {
    super.initState();
    final authVm = context.read<AuthViewModel>();
    _nameCtrl = TextEditingController(text: authVm.fullName);
    _emailCtrl = TextEditingController(text: authVm.email);
    _phoneCtrl = TextEditingController(text: authVm.phone);
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    super.dispose();
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) return;
    final vm = context.read<ProfileViewModel>();
    final success = await vm.updateProfile(
      fullName: _nameCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      phone: _phoneCtrl.text.trim(),
    );
    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(vm.successMessage ?? 'Profil diperbarui'),
          backgroundColor: AppColors.success,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(vm.errorMessage ?? 'Gagal memperbarui profil'),
          backgroundColor: AppColors.error,
        ),
      );
    }
    vm.clearMessages();
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<ProfileViewModel>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: const Text('Edit Profil')),
      body: FormPageWrapper(
        formKey: _formKey,
        bottomBar: AppButton(
          label: 'Simpan Perubahan',
          onPressed: _handleSave,
          isLoading: vm.isUpdating,
          leadingIcon: Icons.save_outlined,
        ),
        children: [
          SectionCard(
            title: 'Informasi Pribadi',
            icon: Icons.person_outline,
            children: [
              AppTextField(
                label: 'Nama Lengkap',
                hint: 'Masukkan nama lengkap Anda',
                controller: _nameCtrl,
                validator: (v) => AppValidators.minLength(v, 3, fieldName: 'Nama'),
              ),
              AppTextField(
                label: 'Email',
                hint: 'nama@email.com',
                controller: _emailCtrl,
                keyboardType: TextInputType.emailAddress,
                validator: AppValidators.email,
              ),
              AppTextField(
                label: 'Nomor HP',
                hint: '08xxxxxxxxxx',
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                validator: AppValidators.phoneNumber,
              ),
            ],
          ),
        ],
      ),
    );
  }
}
