import 'package:flutter/material.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';

class CustomerOrderDetailScreen extends StatelessWidget {
  final AppOrder order;

  const CustomerOrderDetailScreen({
    super.key,
    required this.order,
  });

  @override
  Widget build(BuildContext context) {
    final subTasks = order.subTasksList;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Order #${order.id}'),
        leading: BackButton(
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header Info Card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.divider),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFF93C5FD)),
                        ),
                        child: Text(
                          order.source,
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1D4ED8),
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: _getStatusBgColor(order.status),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          order.status,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: _getStatusColor(order.status),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    order.customerName,
                    style: AppTextStyles.heading2,
                  ),
                  const Divider(height: 24),
                  _buildDetailRow('ID Order', order.id),
                  _buildDetailRow('Tanggal Order', AppFormatters.toDisplayDate(order.date)),
                  if (order.wilayah != null && order.wilayah!.isNotEmpty)
                    _buildDetailRow('Wilayah', order.wilayah!),
                  if (order.lokasiFasilitas != null && order.lokasiFasilitas!.isNotEmpty)
                    _buildDetailRow('Lokasi Fasilitas', order.lokasiFasilitas!),
                  if (order.tkbmOption != null && order.tkbmOption!.isNotEmpty)
                    _buildDetailRow('Opsi TKBM', order.tkbmOption!),
                  if (order.payloadType != null)
                    _buildDetailRow('Tipe Muatan', order.payloadType!),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Progress Layanan & Sub-Tasks
            const Text('Status Layanan & Tracking', style: AppTextStyles.heading3),
            const SizedBox(height: 10),

            if (subTasks.isEmpty)
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.divider),
                ),
                child: const Center(
                  child: Text(
                    'Order sedang diverifikasi oleh admin operasional.',
                    style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                    textAlign: TextAlign.center,
                  ),
                ),
              )
            else
              ...subTasks.map((st) => _buildSubTaskCard(context, st)),

            if (order.containers.isNotEmpty) ...[
              const SizedBox(height: 20),
              Text('Daftar Kontainer (${order.containers.length})', style: AppTextStyles.heading3),
              const SizedBox(height: 10),
              ...order.containers.map((c) => _buildContainerCard(c)),
            ],
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubTaskCard(BuildContext context, Map<String, dynamic> st) {
    final serviceType = st['service_type'] ?? 'Layanan';
    final status = st['status'] ?? 'Masuk';
    final inTime = st['in_time'];
    final outTime = st['out_time'];
    final doneTime = st['done_time'];
    final inNote = st['in_note'];
    final outNote = st['out_note'];
    final inPhoto = st['in_photo_path'];
    final outPhoto = st['out_photo_path'];

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.getServiceBgColor(serviceType),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(
                        color: AppColors.getServiceColor(serviceType).withValues(alpha: 0.4),
                      ),
                    ),
                    child: Text(
                      serviceType,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: AppColors.getServiceColor(serviceType),
                      ),
                    ),
                  ),
                  if (st['task_number'] != null) ...[
                    const SizedBox(width: 8),
                    Text(
                      st['task_number'],
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary, fontWeight: FontWeight.bold),
                    ),
                  ],
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: _getStatusBgColor(status),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  status,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: _getStatusColor(status),
                  ),
                ),
              ),
            ],
          ),
          const Divider(height: 20),

          // Milestone Timeline
          _buildMilestoneRow(
            icon: Icons.login_outlined,
            title: 'IN (Masuk Lapangan)',
            time: inTime,
            note: inNote,
            photoUrl: inPhoto,
          ),
          const SizedBox(height: 10),
          _buildMilestoneRow(
            icon: Icons.logout_outlined,
            title: 'OUT (Keluar Lapangan)',
            time: outTime,
            note: outNote,
            photoUrl: outPhoto,
          ),
          if (doneTime != null) ...[
            const SizedBox(height: 10),
            _buildMilestoneRow(
              icon: Icons.check_circle_outline,
              title: 'DONE (Selesai Operasional)',
              time: doneTime,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMilestoneRow({
    required IconData icon,
    required String title,
    dynamic time,
    String? note,
    String? photoUrl,
  }) {
    final bool isCompleted = time != null;

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            color: isCompleted ? const Color(0xFFDCFCE7) : const Color(0xFFF1F5F9),
            shape: BoxShape.circle,
          ),
          child: Icon(
            icon,
            size: 14,
            color: isCompleted ? const Color(0xFF16A34A) : const Color(0xFF94A3B8),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: isCompleted ? AppColors.textPrimary : AppColors.textSecondary,
                    ),
                  ),
                  Text(
                    isCompleted ? time.toString() : 'Belum proses',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: isCompleted ? FontWeight.w600 : FontWeight.normal,
                      color: isCompleted ? const Color(0xFF16A34A) : AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
              if (note != null && note.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(top: 2),
                  child: Text(
                    'Catatan: $note',
                    style: const TextStyle(fontSize: 11, color: AppColors.textSecondary, fontStyle: FontStyle.italic),
                  ),
                ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildContainerCard(AppContainer c) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              const Icon(Icons.inventory_2_outlined, size: 20, color: AppColors.primary),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    c.number.isNotEmpty ? c.number : 'Tanpa No Kontainer',
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    '${c.size} - ${c.type}',
                    style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                  ),
                ],
              ),
            ],
          ),
          if (c.tkbmOption != null && c.tkbmOption!.isNotEmpty)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: const Color(0xFFF59E0B)),
              ),
              child: Text(
                c.tkbmOption!,
                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF92400E)),
              ),
            ),
        ],
      ),
    );
  }

  Color _getStatusBgColor(String status) {
    final s = status.toLowerCase();
    if (s == 'done' || s == 'selesai') return const Color(0xFFDCFCE7);
    if (s == 'in' || s == 'out' || s == 'on progress' || s == 'in progress') return const Color(0xFFFEF3C7);
    return const Color(0xFFEFF6FF);
  }

  Color _getStatusColor(String status) {
    final s = status.toLowerCase();
    if (s == 'done' || s == 'selesai') return const Color(0xFF16A34A);
    if (s == 'in' || s == 'out' || s == 'on progress' || s == 'in progress') return const Color(0xFFD97706);
    return const Color(0xFF1D4ED8);
  }
}
