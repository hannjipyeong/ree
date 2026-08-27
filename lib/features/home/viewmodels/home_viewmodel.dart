import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/services/api_service.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';

/// ViewModel for the Home/Dashboard screen.
/// Responsible for fetching and holding user profile data,
/// balance, points, promo banners, and recent activity.
class HomeViewModel extends ChangeNotifier {
  // ─── State ──────────────────────────────────────────────────────────────────
  bool _isLoading = false;
  String? _errorMessage;

  // Order Statistics
  Map<String, int> orderStats = {
    'Selesai': 0,
    'Proses': 0,
    'Pending': 0,
  };

  List<Map<String, dynamic>> recentActivities = [];

  // ─── Accessors ──────────────────────────────────────────────────────────────
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  // ─── Methods ────────────────────────────────────────────────────────────────
  Future<void> loadDashboard({bool silent = false}) async {
    if (!silent) {
      _isLoading = true;
      notifyListeners();
    }

    try {
      final orders = await ApiService.getOrders(role: 'customer');
      debugPrint('DEBUG: HomeViewModel loaded ${orders.length} orders');
      
      int selesai = 0;
      int proses = 0;
      int pending = 0;
      List<Map<String, dynamic>> activities = [];

      for (var order in orders) {
        String statusLabel = 'Pending';
        int statusColor = 0xFF2980B9; // Blue

        final lowerStatus = order.status.toLowerCase();
        if (lowerStatus == 'submitted' || lowerStatus == 'masuk') {
          pending++;
          statusLabel = 'Pending';
          statusColor = 0xFF2980B9;
        } else if (lowerStatus == 'on progress' || lowerStatus == 'in' || lowerStatus == 'out') {
          proses++;
          statusLabel = 'Proses';
          statusColor = 0xFFF39C12; // Orange
        } else if (lowerStatus == 'done' || lowerStatus == 'selesai') {
          selesai++;
          statusLabel = 'Selesai';
          statusColor = 0xFF27AE60; // Green
        } else {
          pending++; // Fallback
        }

        activities.add({
          'title': 'Order ${order.source} #${order.id}',
          'subtitle': order.customerName,
          'date': AppFormatters.toDisplayDate(order.date),
          'status': statusLabel,
          'statusColor': statusColor,
          'originalDate': order.date,
          'order': order,
        });
      }
      debugPrint('DEBUG: Activities count = ${activities.length}');

      // Sort activities descending by date
      activities.sort((a, b) => (b['originalDate'] as DateTime).compareTo(a['originalDate'] as DateTime));
      
      // Limit to 5 recent
      if (activities.length > 5) {
        activities = activities.sublist(0, 5);
      }

      orderStats = {
        'Selesai': selesai,
        'Proses': proses,
        'Pending': pending,
      };
      recentActivities = activities;
      
    } catch (e) {
      _errorMessage = 'Gagal memuat data dashboard.';
      debugPrint('DEBUG: HomeViewModel loadDashboard error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
