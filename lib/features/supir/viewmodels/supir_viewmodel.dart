import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart'; // AppOrder
import 'package:bkj_app/core/services/api_service.dart';

class SupirViewModel extends ChangeNotifier {
  bool _isLoading = false;
  String? _errorMessage;
  
  List<AppOrder> _orders = [];

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  List<AppOrder> getOrdersByStatus(String status) {
    return _orders.where((o) => o.status == status).toList();
  }

  List<AppOrder> getAllOrders() {
    return _orders;
  }
  Future<void> fetchOrders(String supirType) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _orders = await ApiService.getOrders(role: 'supir', supirType: supirType);
    } catch (e) {
      _errorMessage = 'Gagal memuat orders: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> processAction({
    required String orderId,
    required String actionType,
    required String note,
    int? containerId,
    List<dynamic>? photos,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await ApiService.updateSubTaskAction(
        taskId: orderId,
        actionType: actionType,
        note: note,
        containerId: containerId,
        photos: photos,
      );

      if (success) {
        return true;
      } else {
        _isLoading = false;
        _errorMessage = 'Gagal memproses aksi dari server.';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _isLoading = false;
      _errorMessage = 'Gagal memproses aksi: $e';
      notifyListeners();
      return false;
    }
  }
}
