import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/services/api_service.dart';

/// Represents a single task/order assigned to a specific service type.
class AppOrder {
  final String id;
  final String customerName;
  final String serviceType; // e.g., 'Haulage', 'LOLO', 'Penumpukan', 'TBKM'
  final String source; // e.g., 'ALL IN', 'Koperasi', 'PBM Lain'
  final DateTime date;
  
  String status; // 'Masuk', 'In', 'Out', 'Done'
  String? inNote;
  String? outNote;

  AppOrder({
    required this.id,
    required this.customerName,
    required this.serviceType,
    required this.source,
    required this.date,
    this.status = 'Masuk',
  });
}

/// A global mock database to simulate orders being created by Customers
/// and processed by Supir.
class MockOrderRepository extends ChangeNotifier {
  final List<AppOrder> _orders = [
    // --- LOLO Mock Data ---
    AppOrder(id: 'REQ-1001-LOL', customerName: 'PT Lintas Samudra', serviceType: 'LOLO', source: 'ALL IN', date: DateTime.now(), status: 'Masuk'),
    AppOrder(id: 'REQ-1002-LOL', customerName: 'PT Bumi Makmur', serviceType: 'LOLO', source: 'Koperasi', date: DateTime.now().subtract(const Duration(hours: 2)), status: 'In'),
    AppOrder(id: 'REQ-1003-LOL', customerName: 'PT Samudra Biru', serviceType: 'LOLO', source: 'PBM Lain', date: DateTime.now().subtract(const Duration(days: 1)), status: 'Out'),
    
    // --- Haulage Mock Data ---
    AppOrder(id: 'REQ-2001-HAU', customerName: 'PT Trans Logistik', serviceType: 'Haulage', source: 'ALL IN', date: DateTime.now(), status: 'Masuk'),
    AppOrder(id: 'REQ-2002-HAU', customerName: 'PT Bumi Makmur', serviceType: 'Haulage', source: 'Koperasi', date: DateTime.now().subtract(const Duration(hours: 1)), status: 'In'),
    AppOrder(id: 'REQ-2003-HAU', customerName: 'PT Cargo Makmur', serviceType: 'Haulage', source: 'PBM Lain', date: DateTime.now().subtract(const Duration(days: 2)), status: 'Out'),
    
    // --- Penumpukan Mock Data ---
    AppOrder(id: 'REQ-3001-PEN', customerName: 'PT Gudang Bersama', serviceType: 'Penumpukan', source: 'ALL IN', date: DateTime.now(), status: 'Masuk'),
    AppOrder(id: 'REQ-3002-PEN', customerName: 'PT Lintas Samudra', serviceType: 'Penumpukan', source: 'Koperasi', date: DateTime.now().subtract(const Duration(hours: 3)), status: 'In'),
    AppOrder(id: 'REQ-3003-PEN', customerName: 'PT Logistik Utama', serviceType: 'Penumpukan', source: 'PBM Lain', date: DateTime.now().subtract(const Duration(days: 1)), status: 'Out'),
    
    // --- TBKM Mock Data ---
    AppOrder(id: 'REQ-4001-TBK', customerName: 'PT Samudra Jaya', serviceType: 'TBKM', source: 'ALL IN', date: DateTime.now(), status: 'Masuk'),
    AppOrder(id: 'REQ-4002-TBK', customerName: 'PT Kargo Lestari', serviceType: 'TBKM', source: 'Koperasi', date: DateTime.now().subtract(const Duration(hours: 4)), status: 'In'),
    AppOrder(id: 'REQ-4003-TBK', customerName: 'PT Trans Nusa', serviceType: 'TBKM', source: 'PBM Lain', date: DateTime.now().subtract(const Duration(days: 3)), status: 'Out'),
  ];

  List<AppOrder> get orders => List.unmodifiable(_orders);

  /// Called by Customer ViewModels when an order is submitted.
  /// If a customer requests multiple services (e.g., Haulage and LOLO),
  /// this method creates separate [AppOrder]s for each service so they
  /// can be picked up by the respective Supir. Also syncs with Laravel API.
  void addOrderFromCustomer({
    required String customerName,
    required String source,
    required Set<String> selectedServices,
  }) {
    final now = DateTime.now();
    final servicesToSubmit = selectedServices.isEmpty ? {'Haulage'} : selectedServices;
    for (final service in servicesToSubmit) {
      // Create a unique ID for each sub-task
      final id = 'REQ-${now.millisecondsSinceEpoch.toString().substring(7)}-${service.substring(0, 3).toUpperCase()}';
      _orders.add(
        AppOrder(
          id: id,
          customerName: customerName,
          serviceType: service,
          source: source,
          date: now,
          status: 'Masuk',
        ),
      );
    }
    notifyListeners();

    // Fire and forget API call to Laravel Backend
    ApiService.submitOrder(
      source: source,
      namaPt: customerName,
      namaPbm: 'PT. ABC',
      noTelp: '081234567890',
      wilayah: 'Selatan',
      lokasiFasilitas: 'TPFT',
      jenisKegiatan: 'cek fisik',
      payloadType: 'Container',
      services: servicesToSubmit,
    );
  }

  /// Called by Supir ViewModels to get orders matching their service type.
  List<AppOrder> getOrdersForSupir(String supirType) {
    return _orders
        .where((o) => o.serviceType.toLowerCase() == supirType.toLowerCase())
        .toList();
  }

  /// Processes an IN or OUT action for a specific order and syncs with Laravel API.
  void processAction(String orderId, String actionType, String note) {
    final index = _orders.indexWhere((o) => o.id == orderId);
    if (index != -1) {
      if (actionType == 'IN') {
        _orders[index].status = 'In';
        _orders[index].inNote = note;
      } else if (actionType == 'OUT') {
        _orders[index].status = 'Out'; // Wait for Admin to set to Done
        _orders[index].outNote = note;
      }
      notifyListeners();

      // Fire and forget API call to Laravel Backend
      ApiService.updateSubTaskAction(
        taskId: orderId,
        actionType: actionType,
        note: note,
      );
    }
  }
}
