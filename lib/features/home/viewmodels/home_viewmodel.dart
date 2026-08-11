import 'package:flutter/foundation.dart';

/// ViewModel for the Home/Dashboard screen.
/// Responsible for fetching and holding user profile data,
/// balance, points, promo banners, and recent activity.
class HomeViewModel extends ChangeNotifier {
  // ─── State ──────────────────────────────────────────────────────────────────
  bool _isLoading = false;
  String? _errorMessage;

  // Simulated user data (replace with API integration later)
  final String userName = 'Andi Pratama';
  final String userRole = 'Member BKJ';
  final String userId = 'BKJ-2024-0042';

  // Simulated Order Statistics
  final Map<String, int> orderStats = {
    'Selesai': 45,
    'Proses': 12,
    'Pending': 8,
  };

  final List<Map<String, dynamic>> recentActivities = [
    {
      'title': 'Order ALL IN #A-2024-001',
      'subtitle': 'Selatan • Bongkar • 3x 20\' GP',
      'date': '10 Agu 2026',
      'status': 'Selesai',
      'statusColor': 0xFF27AE60,
    },
    {
      'title': 'Order Koperasi #K-2024-018',
      'subtitle': 'Utara • Muat Utara • Cargo',
      'date': '08 Agu 2026',
      'status': 'Proses',
      'statusColor': 0xFFF39C12,
    },
    {
      'title': 'Order PBM Lain #P-2024-007',
      'subtitle': 'Eximen • 2x 40\' HC',
      'date': '05 Agu 2026',
      'status': 'Pending',
      'statusColor': 0xFF2980B9,
    },
  ];

  // ─── Accessors ──────────────────────────────────────────────────────────────
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  // ─── Methods ────────────────────────────────────────────────────────────────
  Future<void> loadDashboard() async {
    _isLoading = true;
    notifyListeners();

    // Simulate network delay
    await Future.delayed(const Duration(milliseconds: 600));

    _isLoading = false;
    notifyListeners();
  }
}
