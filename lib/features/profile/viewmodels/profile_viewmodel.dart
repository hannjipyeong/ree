import 'package:flutter/foundation.dart';

/// ViewModel for the Profile/user management module.
class ProfileViewModel extends ChangeNotifier {
  // ─── User State ──────────────────────────────────────────────────────────────
  String _fullName = 'Andi Pratama';
  String _email = 'andi.pratama@bkj.co.id';
  String _phone = '081234567890';
  final String _role = 'Member BKJ';
  final String _userId = 'BKJ-2024-0042';

  bool _isUpdating = false;
  String? _errorMessage;
  String? _successMessage;

  // ─── Getters ─────────────────────────────────────────────────────────────────
  String get fullName => _fullName;
  String get email => _email;
  String get phone => _phone;
  String get role => _role;
  String get userId => _userId;

  bool get isUpdating => _isUpdating;
  String? get errorMessage => _errorMessage;
  String? get successMessage => _successMessage;

  // ─── Methods ─────────────────────────────────────────────────────────────────
  void clearMessages() {
    _errorMessage = null;
    _successMessage = null;
  }

  Future<bool> updateProfile({
    required String fullName,
    required String email,
    required String phone,
  }) async {
    _isUpdating = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await Future.delayed(const Duration(milliseconds: 800));
      _fullName = fullName;
      _email = email;
      _phone = phone;
      _isUpdating = false;
      _successMessage = 'Profil berhasil diperbarui';
      notifyListeners();
      return true;
    } catch (e) {
      _isUpdating = false;
      _errorMessage = 'Gagal memperbarui profil';
      notifyListeners();
      return false;
    }
  }

  Future<bool> changePassword({
    required String oldPassword,
    required String newPassword,
  }) async {
    _isUpdating = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await Future.delayed(const Duration(milliseconds: 800));
      _isUpdating = false;
      _successMessage = 'Password berhasil diubah';
      notifyListeners();
      return true;
    } catch (e) {
      _isUpdating = false;
      _errorMessage = 'Gagal mengubah password';
      notifyListeners();
      return false;
    }
  }

  Future<bool> changePin({
    required String oldPin,
    required String newPin,
  }) async {
    _isUpdating = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await Future.delayed(const Duration(milliseconds: 800));
      _isUpdating = false;
      _successMessage = 'PIN berhasil diubah';
      notifyListeners();
      return true;
    } catch (e) {
      _isUpdating = false;
      _errorMessage = 'Gagal mengubah PIN';
      notifyListeners();
      return false;
    }
  }

  void logout() {
    // TODO: Clear tokens and navigate to login
    resetState();
  }

  void resetState() {
    _errorMessage = null;
    _successMessage = null;
    notifyListeners();
  }
}
