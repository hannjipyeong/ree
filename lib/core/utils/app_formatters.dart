import 'package:intl/intl.dart';

/// Centralized formatting utilities. DRY compliance: all formatting
/// logic lives here; never scattered across widgets or ViewModels.
class AppFormatters {
  AppFormatters._();

  static final DateFormat _dateDisplay = DateFormat('dd MMMM yyyy', 'id_ID');
  static final DateFormat _dateApi = DateFormat('yyyy-MM-dd');
  static final DateFormat _dateDDMMYYYY = DateFormat('dd/MM/yyyy');
  
  static final NumberFormat _currency = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );
  static final NumberFormat _decimal = NumberFormat('#,##0', 'id_ID');

  /// Formats a [DateTime] for human-readable display (e.g., "11 Agustus 2026").
  static String toDisplayDate(DateTime date) => _dateDisplay.format(date);

  /// Formats a [DateTime] to DD/MM/YYYY.
  static String toDDMMYYYY(DateTime date) => _dateDDMMYYYY.format(date);

  /// Formats a [DateTime] for API payloads (e.g., "2026-08-11").
  static String toApiDate(DateTime date) => _dateApi.format(date);

  /// Parses an ISO date string from the API into a [DateTime].
  static DateTime? fromApiDate(String? raw) {
    if (raw == null || raw.isEmpty) return null;
    try {
      return _dateApi.parse(raw);
    } catch (_) {
      return null;
    }
  }

  /// Formats a numeric value as Indonesian Rupiah currency.
  static String toCurrency(num amount) => _currency.format(amount);

  /// Formats a number with thousand separators for display.
  static String toDecimal(num value) => _decimal.format(value);

  /// Returns initials from a full name (max 2 characters).
  static String toInitials(String fullName) {
    final parts = fullName.trim().split(RegExp(r'\s+'));
    if (parts.isEmpty) return '';
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }
}

/// Centralized validation utilities. All form field validators live here
/// so they can be reused without duplication across forms.
class AppValidators {
  AppValidators._();

  static String? required(String? value, {String fieldName = 'Field ini'}) {
    if (value == null || value.trim().isEmpty) {
      return '$fieldName tidak boleh kosong';
    }
    return null;
  }

  static String? requiredDropdown<T>(T? value, {String fieldName = 'Pilihan'}) {
    if (value == null) return '$fieldName harus dipilih';
    return null;
  }

  static String? minLength(String? value, int min, {String fieldName = 'Field ini'}) {
    if (value == null || value.trim().length < min) {
      return '$fieldName minimal $min karakter';
    }
    return null;
  }

  static String? phoneNumber(String? value) {
    if (value == null || value.trim().isEmpty) return 'Nomor HP tidak boleh kosong';
    final cleaned = value.replaceAll(RegExp(r'\D'), '');
    if (cleaned.length < 10 || cleaned.length > 13) {
      return 'Nomor HP tidak valid';
    }
    return null;
  }

  static String? password(String? value) {
    if (value == null || value.isEmpty) return 'Password tidak boleh kosong';
    if (value.length < 8) return 'Password minimal 8 karakter';
    return null;
  }

  static String? confirmPassword(String? value, String original) {
    if (value != original) return 'Password tidak cocok';
    return null;
  }

  static String? pin(String? value) {
    if (value == null || value.trim().isEmpty) return 'PIN tidak boleh kosong';
    if (!RegExp(r'^\d{6}$').hasMatch(value.trim())) return 'PIN harus 6 digit angka';
    return null;
  }

  static String? email(String? value) {
    if (value == null || value.trim().isEmpty) return 'Email tidak boleh kosong';
    if (!RegExp(r'^[\w-.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value.trim())) {
      return 'Format email tidak valid';
    }
    return null;
  }
}
