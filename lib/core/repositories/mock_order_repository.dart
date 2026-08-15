import 'package:flutter/foundation.dart';

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
    // LOLO mock data
    AppOrder(id: 'REQ-1234-LOL', customerName: 'PT Lintas Samudra', serviceType: 'LOLO', source: 'ALL IN', date: DateTime.now(), status: 'Masuk'),
    AppOrder(id: 'REQ-1235-LOL', customerName: 'PT Lintas Samudra', serviceType: 'LOLO', source: 'ALL IN', date: DateTime.now().subtract(const Duration(hours: 2)), status: 'In'),
    AppOrder(id: 'REQ-1236-LOL', customerName: 'PT Lintas Samudra', serviceType: 'LOLO', source: 'Koperasi', date: DateTime.now().subtract(const Duration(days: 1)), status: 'Out'),
    
    // Haulage mock data
    AppOrder(id: 'REQ-2234-HAU', customerName: 'PT Lintas Samudra', serviceType: 'Haulage', source: 'ALL IN', date: DateTime.now(), status: 'Masuk'),
    AppOrder(id: 'REQ-2235-HAU', customerName: 'PT Bumi Maju', serviceType: 'Haulage', source: 'Koperasi', date: DateTime.now().subtract(const Duration(hours: 1)), status: 'In'),
    
    // Penumpukan mock data
    AppOrder(id: 'REQ-3234-PEN', customerName: 'PT Karya Makmur', serviceType: 'Penumpukan', source: 'PBM Lain', date: DateTime.now(), status: 'Masuk'),
    
    // TBKM mock data
    AppOrder(id: 'REQ-4234-TBK', customerName: 'PT Lintas Samudra', serviceType: 'TBKM', source: 'ALL IN', date: DateTime.now(), status: 'Masuk'),
  ];

  List<AppOrder> get orders => List.unmodifiable(_orders);

  /// Called by Customer ViewModels when an order is submitted.
  /// If a customer requests multiple services (e.g., Haulage and LOLO),
  /// this method creates separate [AppOrder]s for each service so they
  /// can be picked up by the respective Supir.
  void addOrderFromCustomer({
    required String customerName,
    required String source,
    required Set<String> selectedServices,
  }) {
    final now = DateTime.now();
    for (final service in selectedServices) {
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
  }

  /// Called by Supir ViewModels to get orders matching their service type.
  List<AppOrder> getOrdersForSupir(String supirType) {
    return _orders
        .where((o) => o.serviceType.toLowerCase() == supirType.toLowerCase())
        .toList();
  }

  /// Processes an IN or OUT action for a specific order.
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
    }
  }
}
