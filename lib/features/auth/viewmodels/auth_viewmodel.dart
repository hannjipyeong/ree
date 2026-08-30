import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/services/api_service.dart';

/// ViewModel for Authentication and Role Management.
class AuthViewModel extends ChangeNotifier {
  bool _isAuthenticated = false;
  bool _isLoading = false;
  String? _errorMessage;

  String _userRole = 'customer'; // 'customer' or 'supir'
  String? _supirType; // e.g., 'Railing', 'LOLO', 'Storage', 'TKBM'
  String? _supirWilayah; // e.g., 'Selatan', 'Utara', 'Eximen'

  // User Profile Data
  String _fullName = '';
  String _email = '';
  String _phone = '';
  String? _defaultNamaPt;
  bool _hasDefaultAsuransi = false;

  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  String get userRole => _userRole;
  String? get supirType => _supirType;
  String? get supirWilayah => _supirWilayah;

  String get fullName => _fullName;
  String get email => _email;
  String get phone => _phone;
  String? get defaultNamaPt => _defaultNamaPt;
  bool get hasDefaultAsuransi => _hasDefaultAsuransi;

  Future<void> checkLoginStatus() async {
    _isLoading = true;
    notifyListeners();

    final userData = await ApiService.getUser();
    if (userData != null) {
      _email = userData['email'] ?? '';
      _fullName = userData['name'] ?? 'User';
      _phone = userData['phone'] ?? '';
      _userRole = userData['role'] ?? 'customer';
      _supirType = userData['supir_type'];
      _supirWilayah = userData['supir_wilayah'];
      _defaultNamaPt = userData['default_nama_pt'];
      _hasDefaultAsuransi = userData['has_default_asuransi'] == true || userData['has_default_asuransi'] == 1;
      _isAuthenticated = true;
    } else {
      _isAuthenticated = false;
    }

    _isLoading = false;
    notifyListeners();
  }

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
        _supirWilayah = userData['supir_wilayah'];
        _defaultNamaPt = userData['default_nama_pt'];
        _hasDefaultAsuransi = userData['has_default_asuransi'] == true || userData['has_default_asuransi'] == 1;

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
      final userData = await ApiService.register(
        name: fullName,
        email: email,
        phone: phone,
        password: password,
      );

      if (userData != null) {
        // Auto login after register
        _email = userData['email'] ?? email;
        _fullName = userData['name'] ?? fullName;
        _phone = userData['phone'] ?? phone;
        _userRole = userData['role'] ?? 'customer'; // Default for new registration
        _supirType = userData['supir_type'];
        _supirWilayah = userData['supir_wilayah'];
        _isAuthenticated = true;
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _isLoading = false;
        _errorMessage = 'Gagal mendaftar: Pastikan email belum digunakan dan koneksi internet stabil.';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _isLoading = false;
      _errorMessage = 'Gagal mendaftar: $e';
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await ApiService.logout();
    _isAuthenticated = false;
    _userRole = 'customer';
    _supirType = null;
    _supirWilayah = null;
    _fullName = '';
    _email = '';
    _phone = '';
    notifyListeners();
  }
}
