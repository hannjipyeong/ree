import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/components/app_button.dart';
import 'package:bkj_app/core/components/app_text_field.dart';
import 'package:bkj_app/core/components/app_multi_file_upload_tile.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/features/supir/viewmodels/supir_viewmodel.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart';
import 'dart:typed_data';
import 'dart:async';
import 'package:url_launcher/url_launcher.dart';

class SupirActionScreenArgs {
  final AppOrder order;
  final String actionType; // 'IN', 'OUT', or 'DETAIL'

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
  List<UploadedFile> _selectedFiles = [];
  
  String _selectedContainerFilter = 'Semua';
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _pollingTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      final supirType = context.read<AuthViewModel>().supirType ?? '';
      context.read<SupirViewModel>().fetchOrders(supirType);
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _noteCtrl.dispose();
    super.dispose();
  }

  Future<void> _submitAction(AppOrder order, String actionType, {int? containerId}) async {
    setState(() => _isLoading = true);
    final vm = context.read<SupirViewModel>();
    
    final success = await vm.processAction(
      orderId: order.id,
      actionType: actionType,
      note: _noteCtrl.text,
      containerId: containerId,
      photos: _selectedFiles,
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (success) {
      if (containerId != null) {
        Navigator.pop(context); // Close bottom sheet
      } else {
        Navigator.pop(context); // Close screen for global cargo action
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Proses $actionType berhasil disimpan.'), backgroundColor: AppColors.success),
      );
      // Refresh data
      final supirType = context.read<AuthViewModel>().supirType ?? '';
      await context.read<SupirViewModel>().fetchOrders(supirType);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(vm.errorMessage ?? 'Terjadi kesalahan.'), backgroundColor: AppColors.error),
      );
    }
  }

  void _showContainerActionSheet(AppOrder order, AppContainer container) {
    _noteCtrl.clear();
    _selectedFiles.clear();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setSheetState) {
            return Consumer<SupirViewModel>(
              builder: (context, vm, child) {
                // Try to find the freshest container data from ViewModel
                final allOrders = vm.getAllOrders();
                final currentOrder = allOrders.firstWhere((o) => o.id == order.id, orElse: () => order);
                final freshContainer = currentOrder.containers.firstWhere((c) => c.id == container.id, orElse: () => container);
                final progress = freshContainer.progress;
                final String currentStatus = progress?.status ?? 'Pending';
                
                // Re-evaluate default action to propose based on fresh data
                String selectedActionType = 'IN';
                if (currentStatus == 'In' || currentStatus == 'Out') {
                  selectedActionType = 'OUT';
                }

                return Padding(
                  padding: EdgeInsets.only(
                    bottom: MediaQuery.of(ctx).viewInsets.bottom,
                    left: 24,
                    right: 24,
                    top: 24,
                  ),
                  child: SingleChildScrollView(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text('Proses - Container ${freshContainer.number}', style: AppTextStyles.heading3),
                    const SizedBox(height: 16),
                    if (selectedActionType == 'IN' && progress?.lockedReasonIn != null)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Text(
                          'Terkunci: ${progress!.lockedReasonIn}',
                          style: const TextStyle(color: AppColors.error, fontWeight: FontWeight.w600, fontSize: 13),
                        ),
                      ),
                    if (selectedActionType == 'OUT' && progress?.lockedReasonOut != null)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Text(
                          'Terkunci: ${progress!.lockedReasonOut}',
                          style: const TextStyle(color: AppColors.error, fontWeight: FontWeight.w600, fontSize: 13),
                        ),
                      ),
                    Row(
                      children: [
                        Expanded(
                          child: GestureDetector(
                            onTap: (currentStatus == 'In' || currentStatus == 'Out' || currentStatus == 'Done' || progress?.lockedReasonIn != null)
                                ? null
                                : () => setSheetState(() => selectedActionType = 'IN'),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              decoration: BoxDecoration(
                                color: selectedActionType == 'IN' 
                                    ? AppColors.primary 
                                    : ((currentStatus == 'In' || currentStatus == 'Out' || currentStatus == 'Done' || progress?.lockedReasonIn != null) ? Colors.grey[300] : Colors.grey[200]),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              alignment: Alignment.center,
                              child: Text(
                                'Proses IN',
                                style: TextStyle(
                                  color: selectedActionType == 'IN' 
                                      ? Colors.white 
                                      : ((currentStatus == 'In' || currentStatus == 'Out' || currentStatus == 'Done' || progress?.lockedReasonIn != null) ? Colors.grey[400] : Colors.black54),
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: GestureDetector(
                            onTap: (currentStatus == 'Out' || currentStatus == 'Done' || progress?.lockedReasonOut != null)
                                ? null
                                : () => setSheetState(() => selectedActionType = 'OUT'),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              decoration: BoxDecoration(
                                color: selectedActionType == 'OUT' 
                                    ? AppColors.primary 
                                    : ((currentStatus == 'Out' || currentStatus == 'Done' || progress?.lockedReasonOut != null) ? Colors.grey[300] : Colors.grey[200]),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              alignment: Alignment.center,
                              child: Text(
                                'Proses OUT',
                                style: TextStyle(
                                  color: selectedActionType == 'OUT' 
                                      ? Colors.white 
                                      : ((currentStatus == 'Out' || currentStatus == 'Done' || progress?.lockedReasonOut != null) ? Colors.grey[400] : Colors.black54),
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    AppMultiFileUploadTile(
                      label: 'Upload Foto Bukti $selectedActionType',
                      files: _selectedFiles,
                      onFilesSelected: (newFiles) {
                        setSheetState(() {
                          _selectedFiles.addAll(newFiles);
                        });
                      },
                      onFileRemoved: (file) {
                        setSheetState(() {
                          _selectedFiles.remove(file);
                        });
                      },
                    ),
                    const SizedBox(height: 24),
                    AppTextField(
                      label: 'Catatan / Note',
                      hint: 'Tambahkan catatan...',
                      controller: _noteCtrl,
                      maxLines: 3,
                    ),
                    const SizedBox(height: 24),
                    AppButton(
                      label: 'Simpan Proses $selectedActionType',
                      onPressed: ((selectedActionType == 'IN' && progress?.lockedReasonIn != null) || 
                                  (selectedActionType == 'OUT' && progress?.lockedReasonOut != null))
                          ? null
                          : () {
                              _submitAction(order, selectedActionType, containerId: container.id);
                            },
                      isLoading: _isLoading,
                    ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            );
              },
            );
          },
        );
      },
    );
  }

  Widget _buildContainerList(AppOrder order) {
    // We need to fetch the latest order state from ViewModel if possible
    final vm = context.watch<SupirViewModel>();
    final allOrders = vm.getAllOrders();
    final currentOrder = allOrders.firstWhere((o) => o.id == order.id, orElse: () => order);
    
    // Filter containers based on _selectedContainerFilter
    final displayedContainers = currentOrder.containers.where((c) {
      if (_selectedContainerFilter == 'Semua') return true;
      final status = c.progress?.status ?? 'Pending';
      return status == _selectedContainerFilter;
    }).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text('Daftar Container', style: AppTextStyles.heading3),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.grey[300]!),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _selectedContainerFilter,
                  icon: const Icon(Icons.arrow_drop_down, color: AppColors.primary),
                  style: AppTextStyles.body2.copyWith(color: AppColors.primary, fontWeight: FontWeight.bold),
                  onChanged: (String? newValue) {
                    if (newValue != null) {
                      setState(() {
                        _selectedContainerFilter = newValue;
                      });
                    }
                  },
                  items: <String>['Semua', 'Pending', 'In', 'Out', 'Done']
                      .map<DropdownMenuItem<String>>((String value) {
                    return DropdownMenuItem<String>(
                      value: value,
                      child: Text(value),
                    );
                  }).toList(),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        if (displayedContainers.isEmpty)
           const Padding(
             padding: EdgeInsets.symmetric(vertical: 32),
             child: Center(
               child: Text('Tidak ada container dengan status ini.'),
             ),
           )
        else
          ...displayedContainers.map((c) {
            final p = c.progress;
            return Card(
              margin: const EdgeInsets.only(bottom: 12),
              child: InkWell(
                onTap: () => _showContainerActionSheet(currentOrder, c),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(c.number.isNotEmpty ? c.number : 'No Container', style: AppTextStyles.heading3),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              p?.status ?? 'Pending',
                              style: AppTextStyles.caption.copyWith(color: AppColors.primary, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Ukuran: ${c.size} • Tipe: ${c.type}', style: AppTextStyles.body2),
                          if (currentOrder.serviceType == 'Haulage' && c.sp3kkFileUrl != null && c.sp3kkFileUrl!.isNotEmpty)
                            InkWell(
                              onTap: () async {
                                final url = Uri.parse(c.sp3kkFileUrl!);
                                if (await canLaunchUrl(url)) {
                                  await launchUrl(url);
                                } else {
                                  if (context.mounted) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(content: Text('Tidak dapat membuka file SP3KK.')),
                                    );
                                  }
                                }
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                decoration: BoxDecoration(
                                  color: Colors.green.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: Colors.green.withValues(alpha: 0.5)),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.file_present, size: 16, color: Colors.green),
                                    const SizedBox(width: 4),
                                    Text('Lihat SP3KK', style: AppTextStyles.caption.copyWith(color: Colors.green, fontWeight: FontWeight.bold)),
                                  ],
                                ),
                              ),
                            ),
                        ],
                      ),
                      
                      if (p != null && (p.inPhotoPath != null || p.outPhotoPath != null)) ...[
                        const Divider(height: 24),
                        if (p.inPhotoPath != null) ...[
                          Text('IN: ${p.inNote ?? "-"}', style: AppTextStyles.body2),
                          const SizedBox(height: 4),
                          if (p.inTime != null)
                            Text('Waktu IN: ${p.inTime!.day}/${p.inTime!.month}/${p.inTime!.year} ${p.inTime!.hour}:${p.inTime!.minute}', style: AppTextStyles.caption),
                        ],
                        if (p.outPhotoPath != null) ...[
                          const SizedBox(height: 8),
                          Text('OUT: ${p.outNote ?? "-"}', style: AppTextStyles.body2),
                          const SizedBox(height: 4),
                          if (p.outTime != null)
                            Text('Waktu OUT: ${p.outTime!.day}/${p.outTime!.month}/${p.outTime!.year} ${p.outTime!.hour}:${p.outTime!.minute}', style: AppTextStyles.caption),
                        ],
                      ]
                    ],
                  ),
                ),
              ),
            );
          }),
      ],
    );
  }

  Widget _buildGlobalAction(AppOrder order, String actionType) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Dokumentasi Lapangan (Global)', style: AppTextStyles.heading3),
        const SizedBox(height: 16),
        AppMultiFileUploadTile(
          label: 'Upload Foto Bukti',
          files: _selectedFiles,
          onFilesSelected: (newFiles) {
            setState(() {
              _selectedFiles.addAll(newFiles);
            });
          },
          onFileRemoved: (file) {
            setState(() {
              _selectedFiles.remove(file);
            });
          },
        ),
        const SizedBox(height: 24),
        AppTextField(
          label: 'Catatan / Note',
          hint: 'Tambahkan catatan aktivitas...',
          controller: _noteCtrl,
          maxLines: 4,
        ),
        const SizedBox(height: 24),
        AppButton(
          label: 'Simpan Proses $actionType',
          onPressed: () => _submitAction(order, actionType),
          isLoading: _isLoading,
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final args = ModalRoute.of(context)!.settings.arguments as SupirActionScreenArgs;
    final order = args.order;
    final isContainerPayload = (order.payloadType?.contains('Container') ?? false) && order.containers.isNotEmpty;
    final String actionType = isContainerPayload ? 'DETAIL' : (order.status == 'Masuk' ? 'IN' : 'OUT');

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(title: Text(isContainerPayload ? 'Detail Order' : 'Proses $actionType')),
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
                    BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 2)),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Detail Order', style: AppTextStyles.heading3),
                    const Divider(height: 24),
                    _buildInfoRow('ID Order', order.id),
                    _buildInfoRow('Customer', order.customerName),
                    _buildCustomRow(
                      'Layanan',
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.getServiceBgColor(order.serviceType),
                          borderRadius: BorderRadius.circular(4),
                          border: Border.all(
                            color: AppColors.getServiceColor(order.serviceType).withValues(alpha: 0.4),
                          ),
                        ),
                        child: Text(
                          order.serviceType,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: AppColors.getServiceColor(order.serviceType),
                          ),
                        ),
                      ),
                    ),
                    _buildInfoRow('Tipe', order.payloadType ?? 'Container'),
                    _buildInfoRow('Tanggal', '${order.date.day}/${order.date.month}/${order.date.year} ${order.date.hour}:${order.date.minute}'),
                  ],
                ),
              ),
              const SizedBox(height: 32),
              
              if (isContainerPayload)
                _buildContainerList(order)
              else if (actionType != 'DETAIL' && order.status != 'Done')
                _buildGlobalAction(order, actionType),
            ],
          ),
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
          SizedBox(width: 100, child: Text(label, style: AppTextStyles.caption)),
          Expanded(child: Text(value, style: AppTextStyles.body2.copyWith(fontWeight: FontWeight.w600))),
        ],
      ),
    );
  }

  Widget _buildCustomRow(String label, Widget child) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          SizedBox(width: 100, child: Text(label, style: AppTextStyles.caption)),
          Expanded(child: Align(alignment: Alignment.centerLeft, child: child)),
        ],
      ),
    );
  }
}
