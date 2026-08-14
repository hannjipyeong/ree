import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';

class SupirViewModel extends ChangeNotifier {
  final MockOrderRepository _orderRepository;
  bool _isLoading = false;
  String? _errorMessage;

  SupirViewModel({required MockOrderRepository orderRepository})
      : _orderRepository = orderRepository {
    // Listen to repository changes
    _orderRepository.addListener(notifyListeners);
  }

  @override
  void dispose() {
    _orderRepository.removeListener(notifyListeners);
    super.dispose();
  }

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  List<AppOrder> getOrdersByStatus(String status, String supirType) {
    final allOrders = _orderRepository.getOrdersForSupir(supirType);
    return allOrders.where((o) => o.status == status).toList();
  }

  Future<bool> processAction({
    required String orderId,
    required String actionType, // 'IN' or 'OUT'
    required String note,
    // File/Photo would normally be passed here
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await Future.delayed(const Duration(seconds: 1)); // Simulate network request

      _orderRepository.processAction(orderId, actionType, note);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _errorMessage = 'Gagal memproses aksi: $e';
      notifyListeners();
      return false;
    }
  }
}
