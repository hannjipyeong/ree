import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart';
import 'package:bkj_app/features/supir/viewmodels/supir_viewmodel.dart';
import 'package:bkj_app/features/supir/views/supir_action_screen.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';

class SupirHomeScreen extends StatefulWidget {
  const SupirHomeScreen({super.key});

  @override
  State<SupirHomeScreen> createState() => _SupirHomeScreenState();
}

class _SupirHomeScreenState extends State<SupirHomeScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    
    // We no longer load mock data here because MockOrderRepository handles it.
    // Data will be driven by Customer submissions.
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _navigateToActionScreen(BuildContext context, AppOrder order, String actionType) {
    Navigator.pushNamed(
      context,
      AppRoutes.supirAction,
      arguments: SupirActionScreenArgs(order: order, actionType: actionType),
    );
  }

  void _showRecapDialog(BuildContext context, AppOrder order) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Detail Request Order'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('ID Order: ${order.id}', style: AppTextStyles.body2),
            const SizedBox(height: 8),
            Text('Customer: ${order.customerName}', style: AppTextStyles.heading3),
            const SizedBox(height: 8),
            Text('Layanan: ${order.serviceType}', style: AppTextStyles.body1),
            const SizedBox(height: 8),
            Text('Tanggal: ${AppFormatters.toDDMMYYYY(order.date)}', style: AppTextStyles.body2),
            const SizedBox(height: 16),
            const Text('Ini adalah ringkasan dari request customer.', style: AppTextStyles.caption),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Tutup'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(ctx);
              _navigateToActionScreen(context, order, 'IN');
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
            child: const Text('Proses IN', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderList(String status) {
    final vm = context.watch<SupirViewModel>();
    final authVm = context.watch<AuthViewModel>();
    final orders = vm.getOrdersByStatus(status, authVm.supirType ?? '');

    if (orders.isEmpty) {
      return Center(
        child: Text('Tidak ada order dengan status $status', style: AppTextStyles.body2),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: orders.length,
      itemBuilder: (context, index) {
        final order = orders[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(order.id, style: AppTextStyles.caption),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        order.status,
                        style: AppTextStyles.caption.copyWith(
                          color: AppColors.primary,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(order.customerName, style: AppTextStyles.heading3),
                const SizedBox(height: 4),
                Text('Layanan: ${order.serviceType}', style: AppTextStyles.body2),
                const SizedBox(height: 16),
                if (status == 'Masuk')
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () => _showRecapDialog(context, order),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('Lihat Detail & Proses IN'),
                    ),
                  ),
                if (status == 'In')
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () => _navigateToActionScreen(context, order, 'OUT'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.warning,
                        foregroundColor: Colors.white,
                      ),
                      child: const Text('Proses OUT'),
                    ),
                  ),
                if (status == 'Out')
                  const Text('Menunggu validasi Admin untuk menjadi Done.', style: AppTextStyles.caption),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final authVm = context.watch<AuthViewModel>();
    
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text('Dashboard ${authVm.supirType ?? ''}'),
        bottom: TabBar(
          controller: _tabController,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
          tabs: const [
            Tab(text: 'Masuk'),
            Tab(text: 'In'),
            Tab(text: 'Out'),
            Tab(text: 'Done'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildOrderList('Masuk'),
          _buildOrderList('In'),
          _buildOrderList('Out'),
          _buildOrderList('Done'),
        ],
      ),
    );
  }
}
