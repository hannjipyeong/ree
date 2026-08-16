import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';
import 'package:bkj_app/main.dart'; // import rootScaffoldMessengerKey
import 'package:bkj_app/core/theme/app_theme.dart';

class ApiService {
  static void _showErrorToast(String message) {
    rootScaffoldMessengerKey.currentState?.showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: AppColors.error,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  static String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api';
    }
    return defaultTargetPlatform == TargetPlatform.android
        ? 'http://10.0.2.2:8000/api'
        : 'http://127.0.0.1:8000/api';
  }

  static Future<Map<String, String>> getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    
    final headers = {'Accept': 'application/json'};
    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  /// 1. Login Endpoint
  /// Returns a Map containing user data if successful, null otherwise.
  static Future<Map<String, dynamic>?> login(String email, String password) async {
    try {
      final url = Uri.parse('$baseUrl/login');
      final response = await http.post(
        url,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          final prefs = await SharedPreferences.getInstance();
          if (body['token'] != null) {
            await prefs.setString('auth_token', body['token']);
          }
          return body['data'];
        }
      } else {
        _showErrorToast('Login gagal: Server merespons ${response.statusCode}');
      }
      return null;
    } catch (e) {
      debugPrint('API login error: $e');
      _showErrorToast('Login gagal: Periksa koneksi internet atau server Anda.');
      return null;
    }
  }

  static Future<Map<String, dynamic>?> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/register');
      final response = await http.post(
        url,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'name': name,
          'email': email,
          'phone': phone,
          'password': password,
        }),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          final prefs = await SharedPreferences.getInstance();
          if (body['token'] != null) {
            await prefs.setString('auth_token', body['token']);
          }
          return body['data'];
        }
      } else {
        _showErrorToast('Registrasi gagal: Server merespons ${response.statusCode}');
      }
      return null;
    } catch (e) {
      debugPrint('API register error: $e');
      _showErrorToast('Registrasi gagal: Periksa koneksi internet atau server Anda.');
      return null;
    }
  }

  static Future<void> logout() async {
    try {
      final url = Uri.parse('$baseUrl/logout');
      final headers = await getHeaders();
      await http.post(url, headers: headers);
    } catch (e) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
    }
  }

  static Future<Map<String, dynamic>?> getUser() async {
    try {
      final url = Uri.parse('$baseUrl/user');
      final headers = await getHeaders();
      final response = await http.get(url, headers: headers);

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        debugPrint('getUser failed: ${response.statusCode}');
      }
      return null;
    } catch (e) {
      debugPrint('API getUser error: $e');
      _showErrorToast('Gagal terhubung ke server (CORS / Network Error).');
      return null;
    }
  }

  /// 2. Fetch Orders
  static Future<List<AppOrder>> getOrders({required String role, String? supirType}) async {
    try {
      var urlStr = '$baseUrl/orders?role=$role';
      if (supirType != null) {
        urlStr += '&supir_type=$supirType';
      }
      final url = Uri.parse(urlStr);
      final headers = await getHeaders();
      final response = await http.get(url, headers: headers);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          final data = body['data'] as List;
          List<AppOrder> appOrders = [];

          if (role == 'supir') {
            // Data is a list of SubTasks
            for (var task in data) {
              final order = task['order'] ?? {};
              appOrders.add(AppOrder(
                id: task['task_number']?.toString() ?? task['id'].toString(),
                customerName: order['nama_pt'] ?? 'Unknown Customer',
                serviceType: task['service_type'] ?? 'Unknown',
                source: order['source'] ?? 'ALL IN',
                date: DateTime.tryParse(task['created_at'] ?? '') ?? DateTime.now(),
                status: task['status'] ?? 'Masuk',
              ));
            }
          } else {
            // Data is a list of Orders (For Customer history - if needed later)
            for (var order in data) {
              appOrders.add(AppOrder(
                id: order['order_number']?.toString() ?? order['id'].toString(),
                customerName: order['nama_pt'] ?? 'Unknown Customer',
                serviceType: 'Multiple',
                source: order['source'] ?? 'ALL IN',
                date: DateTime.tryParse(order['tanggal_order'] ?? '') ?? DateTime.now(),
                status: order['status'] ?? 'Submitted',
              ));
            }
          }
          return appOrders;
        }
      } else {
        if (response.statusCode != 401) {
          _showErrorToast('Gagal mengambil data order (${response.statusCode})');
        }
      }
      return [];
    } catch (e) {
      debugPrint('API getOrders error: $e');
      _showErrorToast('Gagal fetch order: Koneksi terputus.');
      return [];
    }
  }

  /// 3. Submit Order (Multipart)
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
    List<Map<String, dynamic>>? containers,
    String? cargoFilePath,
    String? haulageFilePath,
    Uint8List? cargoFileBytes,
    String? cargoFileName,
    Uint8List? haulageFileBytes,
    String? haulageFileName,
  }) async {
    try {
      final url = Uri.parse('$baseUrl/orders');
      var request = http.MultipartRequest('POST', url);
      final headers = await getHeaders();
      request.headers.addAll(headers);

      // Text fields
      request.fields['source'] = source;
      request.fields['tanggal_order'] = DateTime.now().toIso8601String().substring(0, 10);
      request.fields['nama_pt'] = namaPt;
      request.fields['nama_pbm'] = namaPbm;
      request.fields['no_telp'] = noTelp;
      request.fields['wilayah'] = wilayah;
      request.fields['lokasi_fasilitas'] = lokasiFasilitas;
      request.fields['jenis_kegiatan'] = jenisKegiatan;
      request.fields['payload_type'] = payloadType;
      
      // JSON strings for arrays
      request.fields['services'] = jsonEncode(services.toList());
      if (containers != null && containers.isNotEmpty) {
        request.fields['containers'] = jsonEncode(containers);
      }

      // Files — always use bytes since file_picker withData: true provides bytes on all platforms
      // and fromPath crashes the Dart web compiler.
      if (cargoFileBytes != null && cargoFileName != null) {
        request.files.add(http.MultipartFile.fromBytes('cargo_file', cargoFileBytes, filename: cargoFileName));
      } else if (cargoFilePath != null && cargoFilePath.isNotEmpty) {
        // Fallback for native if bytes are missing for some reason
        debugPrint('Warning: Missing cargo bytes on native. Cannot upload via path on Web.');
      }
      
      if (haulageFileBytes != null && haulageFileName != null) {
        request.files.add(http.MultipartFile.fromBytes('haulage_file', haulageFileBytes, filename: haulageFileName));
      } else if (haulageFilePath != null && haulageFilePath.isNotEmpty) {
        debugPrint('Warning: Missing haulage bytes on native. Cannot upload via path on Web.');
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200 || response.statusCode == 201) {
        return true;
      } else {
        debugPrint('Submit Order Failed: ${response.body}');
        _showErrorToast('Submit Order Failed: ${response.statusCode}');
        return false;
      }
    } catch (e) {
      debugPrint('API submitOrder error: $e');
      _showErrorToast('Gagal submit order: Periksa koneksi internet Anda.');
      return false;
    }
  }

  /// 4. Update Supir Action (Multipart)
  static Future<bool> updateSubTaskAction({
    required String taskId, 
    required String actionType,
    required String note,
    String? photoPath,
    Uint8List? photoBytes,
    String? photoFileName,
  }) async {
    try {
      // API needs database ID. If taskId is task_number (REQ-...), we must ensure Laravel can handle it.
      // Laravel route: /api/sub-tasks/{id}/action. 
      final url = Uri.parse('$baseUrl/sub-tasks/$taskId/action');
      var request = http.MultipartRequest('POST', url);
      final headers = await getHeaders();
      request.headers.addAll(headers);
      
      // Spoof PATCH request for Laravel
      request.fields['_method'] = 'PATCH';
      request.fields['action_type'] = actionType;
      request.fields['note'] = note;

      if (photoBytes != null && photoFileName != null) {
        request.files.add(http.MultipartFile.fromBytes('photo', photoBytes, filename: photoFileName));
      } else if (photoPath != null && photoPath.isNotEmpty) {
        debugPrint('Warning: Missing photo bytes. Cannot upload via path on Web.');
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        return true;
      } else {
        debugPrint('Update Action Failed: ${response.body}');
        _showErrorToast('Gagal update status: ${response.statusCode}');
        return false;
      }
    } catch (e) {
      debugPrint('API updateSubTaskAction error: $e');
      _showErrorToast('Gagal update status: Periksa jaringan Anda.');
      return false;
    }
  }
}
