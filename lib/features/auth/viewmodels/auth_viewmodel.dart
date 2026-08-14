import 'package:flutter/foundation.dart';

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
      await Future.delayed(const Duration(milliseconds: 800));

      _email = email;
      _isAuthenticated = true;
      _isLoading = false;

      // Mock logic: Determine role based on email string
      if (email.toLowerCase().contains('supir_haulage')) {
        _userRole = 'supir';
        _supirType = 'Haulage';
        _fullName = 'Supir Haulage 1';
        _phone = '081234567801';
      } else if (email.toLowerCase().contains('supir_lolo')) {
        _userRole = 'supir';
        _supirType = 'LOLO';
        _fullName = 'Supir LOLO 1';
        _phone = '081234567802';
      } else if (email.toLowerCase().contains('supir_penumpukan')) {
        _userRole = 'supir';
        _supirType = 'Penumpukan';
        _fullName = 'Supir Penumpukan 1';
        _phone = '081234567803';
      } else if (email.toLowerCase().contains('supir_tbkm')) {
        _userRole = 'supir';
        _supirType = 'TBKM';
        _fullName = 'Supir TBKM 1';
        _phone = '081234567804';
      } else {
        // Default Customer
        _userRole = 'customer';
        _supirType = null;
        _fullName = 'Andi Pratama (Customer)';
        _phone = '081234567890';
      }

      notifyListeners();
      return true;
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
