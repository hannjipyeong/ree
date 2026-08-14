import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/app_button.dart';
import 'package:bkj_app/core/components/app_text_field.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _passCtrl = TextEditingController();

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;
    
    final vm = context.read<AuthViewModel>();
    final success = await vm.register(
      fullName: _nameCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      phone: _phoneCtrl.text.trim(),
      password: _passCtrl.text,
    );
    
    if (!mounted) return;
    if (success) {
      Navigator.pushReplacementNamed(context, AppRoutes.shell);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(vm.errorMessage ?? 'Gagal mendaftar'),
          backgroundColor: AppColors.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<AuthViewModel>();
    
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Daftar Akun Baru'),
        elevation: 0,
        backgroundColor: Colors.transparent,
        foregroundColor: AppColors.textPrimary,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'Buat Akun Customer',
                  style: AppTextStyles.heading1,
                ),
                const SizedBox(height: 8),
                const Text(
                  'Isi form di bawah ini untuk mendaftar',
                  style: AppTextStyles.body1,
                ),
                const SizedBox(height: 32),
                
                AppTextField(
                  label: 'Nama Lengkap',
                  hint: 'Masukkan nama lengkap',
                  controller: _nameCtrl,
                  validator: (v) => AppValidators.minLength(v, 3, fieldName: 'Nama Lengkap'),
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
                AppTextField(
                  label: 'Password',
                  hint: 'Buat password',
                  controller: _passCtrl,
                  obscureText: true,
                  validator: (v) => AppValidators.minLength(v, 6, fieldName: 'Password'),
                ),
                const SizedBox(height: 32),
                
                AppButton(
                  label: 'Daftar Sekarang',
                  onPressed: _handleRegister,
                  isLoading: vm.isLoading,
                ),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text('Sudah punya akun?', style: AppTextStyles.body2),
                    TextButton(
                      onPressed: () {
                        Navigator.pop(context);
                      },
                      child: const Text('Masuk di sini'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
