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
    String? photoPath,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await ApiService.updateSubTaskAction(
        taskId: orderId,
        actionType: actionType,
        note: note,
        photoPath: photoPath,
      );

      if (success) {
        // Update local state without re-fetching
        final index = _orders.indexWhere((o) => o.id == orderId);
        if (index != -1) {
          _orders[index].status = actionType == 'IN' ? 'In' : 'Out';
          if (actionType == 'IN') _orders[index].inNote = note;
          if (actionType == 'OUT') _orders[index].outNote = note;
        }
        _isLoading = false;
        notifyListeners();
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
