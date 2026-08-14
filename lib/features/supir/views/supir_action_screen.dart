import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/app_button.dart';
import 'package:bkj_app/core/components/app_text_field.dart';
import 'package:bkj_app/core/components/app_file_upload_tile.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/features/supir/viewmodels/supir_viewmodel.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';

class SupirActionScreenArgs {
  final AppOrder order;
  final String actionType; // 'IN' or 'OUT'

  SupirActionScreenArgs({required this.order, required this.actionType});
}

class SupirActionScreen extends StatefulWidget {
  const SupirActionScreen({super.key});

  @override
  State<SupirActionScreen> createState() => _SupirActionScreenState();
}

class _SupirActionScreenState extends State<SupirActionScreen> {
  final _noteCtrl = TextEditingController();
  bool _isLoading = false;
  String? _selectedFileName;

  @override
  void dispose() {
    _noteCtrl.dispose();
    super.dispose();
  }

  Future<void> _submitAction(AppOrder order, String actionType) async {
    setState(() => _isLoading = true);
    final vm = context.read<SupirViewModel>();
    
    final success = await vm.processAction(
      orderId: order.id,
      actionType: actionType,
      note: _noteCtrl.text,
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Proses $actionType berhasil disimpan.'),
          backgroundColor: AppColors.success,
        ),
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

  @override
  Widget build(BuildContext context) {
    // Retrieve arguments
    final args = ModalRoute.of(context)!.settings.arguments as SupirActionScreenArgs;
    final order = args.order;
    final actionType = args.actionType;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Proses $actionType'),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Info Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 10,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Detail Order', style: AppTextStyles.heading3),
                    const Divider(height: 24),
                    _buildInfoRow('ID Order', order.id),
                    _buildInfoRow('Customer', order.customerName),
                    _buildInfoRow('Layanan', order.serviceType),
                    _buildInfoRow('Sumber', order.source),
                  ],
                ),
              ),
              
              const SizedBox(height: 32),
              
              const Text('Dokumentasi Lapangan', style: AppTextStyles.heading3),
              const SizedBox(height: 16),
              
              AppFileUploadTile(
                label: 'Upload Foto Bukti',
                fileName: _selectedFileName,
                onFileSelected: (name, path) {
                  setState(() => _selectedFileName = name);
                },
                onCleared: () {
                  setState(() => _selectedFileName = null);
                },
              ),
              
              const SizedBox(height: 24),
              AppTextField(
                label: 'Catatan / Note',
                hint: 'Tambahkan catatan aktivitas...',
                controller: _noteCtrl,
                maxLines: 4,
              ),
              
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              offset: const Offset(0, -4),
              blurRadius: 16,
            ),
          ],
        ),
        child: AppButton(
          label: 'Simpan Proses $actionType',
          onPressed: () => _submitAction(order, actionType),
          isLoading: _isLoading,
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(label, style: AppTextStyles.caption),
          ),
          Expanded(
            child: Text(
              value, 
              style: AppTextStyles.body2.copyWith(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
