import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/services/api_service.dart';

/// ViewModel for Authentication and Role Management.
class AuthViewModel extends ChangeNotifier {
  bool _isAuthenticated = false;
  bool _isLoading = false;
  String? _errorMessage;

  String _userRole = 'customer'; // 'customer' or 'supir'
  String? _supirType; // e.g., 'Haulage', 'LOLO', 'Penumpukan', 'TBKM'

  // User Profile Data
  String _fullName = '';
  String _email = '';
  String _phone = '';

  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  String get userRole => _userRole;
  String? get supirType => _supirType;

  String get fullName => _fullName;
  String get email => _email;
  String get phone => _phone;

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final userData = await ApiService.login(email, password);

      if (userData != null) {
        _email = userData['email'] ?? email;
        _fullName = userData['name'] ?? 'User';
        _phone = userData['phone'] ?? '';
        _userRole = userData['role'] ?? 'customer';
        _supirType = userData['supir_type'];

        _isAuthenticated = true;
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _isLoading = false;
        _errorMessage = 'Gagal login: Email atau password salah, atau server tidak merespons.';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _isLoading = false;
      _errorMessage = 'Gagal login: $e';
      notifyListeners();
      return false;
    }
  }

  Future<bool> register({
    required String fullName,
    required String email,
    required String phone,
    required String password,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await Future.delayed(const Duration(milliseconds: 800));
      _isLoading = false;
      // Auto login after register
      _email = email;
      _fullName = fullName;
      _phone = phone;
      _userRole = 'customer'; // Default for new registration
      _supirType = null;
      _isAuthenticated = true;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _errorMessage = 'Gagal mendaftar: $e';
      notifyListeners();
      return false;
    }
  }

  void logout() {
    _isAuthenticated = false;
    _userRole = 'customer';
    _supirType = null;
    _fullName = '';
    _email = '';
    _phone = '';
    notifyListeners();
  }
}
