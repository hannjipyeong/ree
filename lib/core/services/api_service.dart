import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // Base URL for Laravel API backend
  // For Android Emulator: 10.0.2.2
  // For iOS / macOS / Web / Desktop: 127.0.0.1
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api';
    }
    return defaultTargetPlatform == TargetPlatform.android
        ? 'http://10.0.2.2:8000/api'
        : 'http://127.0.0.1:8000/api';
  }

  /// Sends a POST request to submit a customer order to Laravel backend.
  static Future<bool> submitOrder({
    required String source,
    required String namaPt,
    required String namaPbm,
    required String noTelp,
    required String wilayah,
    required String lokasiFasilitas,
    required String jenisKegiatan,
    required String payloadType,
    required Set<String> services,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/orders');
      final response = await http.post(
        url,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'source': source,
          'tanggal_order': DateTime.now().toIso8601String().substring(0, 10),
          'nama_pt': namaPt,
          'nama_pbm': namaPbm,
          'no_telp': noTelp,
          'wilayah': wilayah,
          'lokasi_fasilitas': lokasiFasilitas,
          'jenis_kegiatan': jenisKegiatan,
          'payload_type': payloadType,
          'services': services.toList(),
        }),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        return true;
      }
      return false;
    } catch (e) {
      debugPrint('API submitOrder error: $e');
      return false;
    }
  }

  /// Sends a PATCH request for Supir action IN/OUT.
  static Future<bool> updateSubTaskAction({
    required String taskId,
    required String actionType, // 'IN' or 'OUT'
    required String note,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/sub-tasks/$taskId/action');
      final response = await http.patch(
        url,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'action_type': actionType,
          'note': note,
        }),
      );

      if (response.statusCode == 200) {
        return true;
      }
      return false;
    } catch (e) {
      debugPrint('API updateSubTaskAction error: $e');
      return false;
    }
  }
}
