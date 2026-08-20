import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart';
import 'package:bkj_app/features/supir/viewmodels/supir_viewmodel.dart';
import 'package:bkj_app/features/supir/views/supir_action_screen.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';
import 'package:bkj_app/core/routing/app_routes.dart';

import 'dart:async';

class SupirHomeScreen extends StatefulWidget {
  const SupirHomeScreen({super.key});

  @override
  State<SupirHomeScreen> createState() => _SupirHomeScreenState();
}

class _SupirHomeScreenState extends State<SupirHomeScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _fetchData();
      // Polling every 10 seconds for pseudo-realtime
      _pollingTimer = Timer.periodic(const Duration(seconds: 10), (_) {
        _fetchData();
      });
    });
  }

  Future<void> _fetchData() async {
    final supirType = context.read<AuthViewModel>().supirType ?? '';
    await context.read<SupirViewModel>().fetchOrders(supirType);
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  void _navigateToActionScreen(AppOrder order) async {
    await Navigator.pushNamed(
      context,
      AppRoutes.supirAction,
      arguments: SupirActionScreenArgs(order: order, actionType: 'DETAIL'),
    );
    if (!mounted) return;
    final supirType = context.read<AuthViewModel>().supirType ?? '';
    context.read<SupirViewModel>().fetchOrders(supirType);
  }

  Widget _buildOrderList() {
    final vm = context.watch<SupirViewModel>();
    final allOrders = vm.getAllOrders();
    
    final orders = allOrders.where((order) {
      if (_searchQuery.isEmpty) return true;
      final q = _searchQuery.toLowerCase();
      
      final matchId = order.id.toLowerCase().contains(q);
      final matchCustomer = order.customerName.toLowerCase().contains(q);
      final matchContainer = order.containers.any((c) => c.number.toLowerCase().contains(q));
      
      return matchId || matchCustomer || matchContainer;
    }).toList();

    if (vm.isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (orders.isEmpty) {
      return Center(
        child: Text(
          _searchQuery.isEmpty ? 'Tidak ada data order' : 'Tidak ditemukan hasil pencarian',
          style: AppTextStyles.body2
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _fetchData,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: orders.length,
        itemBuilder: (context, index) {
          final order = orders[index];
          final formattedDate = "${order.date.day.toString().padLeft(2, '0')}/${order.date.month.toString().padLeft(2, '0')}/${order.date.year} ${order.date.hour.toString().padLeft(2, '0')}:${order.date.minute.toString().padLeft(2, '0')}";
          
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            clipBehavior: Clip.antiAlias,
            child: InkWell(
              onTap: () => _navigateToActionScreen(order),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(order.id, style: AppTextStyles.caption.copyWith(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text(order.customerName, style: AppTextStyles.heading3),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.access_time, size: 14, color: AppColors.textSecondary),
                        const SizedBox(width: 4),
                        Text(formattedDate, style: AppTextStyles.caption),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.getServiceBgColor(order.serviceType),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: AppColors.getServiceColor(order.serviceType).withValues(alpha: 0.4),
                            ),
                          ),
                          child: Text(
                            order.serviceType,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                              color: AppColors.getServiceColor(order.serviceType),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.background,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: AppColors.divider),
                          ),
                          child: Text(
                            order.payloadType ?? 'Container',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: AppColors.textSecondary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    if (order.containers.isNotEmpty) ...[
                      Text('Containers (${order.containers.length}):', style: AppTextStyles.caption.copyWith(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 4),
                      Wrap(
                        spacing: 8,
                        runSpacing: 4,
                        children: order.containers.map((c) {
                          return Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.grey[200],
                              borderRadius: BorderRadius.circular(4),
                              border: Border.all(color: Colors.grey[300]!),
                            ),
                            child: Text(
                              c.number.isNotEmpty ? c.number : 'No Container',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          );
                        }).toList(),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authVm = context.watch<AuthViewModel>();
    
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          (authVm.supirType?.toLowerCase() == 'haulage' || authVm.supirType?.toLowerCase() == 'houlage')
              ? 'Dashboard Supir Haulage'
              : 'Dashboard Pelaksana Lapangan ${authVm.supirType ?? ''}',
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(60),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
            child: TextField(
              controller: _searchController,
              onChanged: (val) {
                setState(() {
                  _searchQuery = val;
                });
              },
              decoration: InputDecoration(
                hintText: 'Cari PT, No Request, Container...',
                prefixIcon: const Icon(Icons.search),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 0),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
        ),
      ),
      body: _buildOrderList(),
    );
  }
}
