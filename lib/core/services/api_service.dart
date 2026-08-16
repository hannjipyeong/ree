import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:bkj_app/core/repositories/mock_order_repository.dart';

class ApiService {
  // Base URL for Laravel API backend
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api';
    }
    return defaultTargetPlatform == TargetPlatform.android
        ? 'http://10.0.2.2:8000/api'
        : 'http://127.0.0.1:8000/api';
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
          return body['data'];
        }
      }
      return null;
    } catch (e) {
      debugPrint('API login error: $e');
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
      final response = await http.get(
        url,
        headers: {'Accept': 'application/json'},
      );

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
      }
      return [];
    } catch (e) {
      debugPrint('API getOrders error: $e');
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
  }) async {
    try {
      final url = Uri.parse('$baseUrl/orders');
      var request = http.MultipartRequest('POST', url);
      request.headers['Accept'] = 'application/json';

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

      // Files
      if (cargoFilePath != null) {
        request.files.add(await http.MultipartFile.fromPath('cargo_file', cargoFilePath));
      }
      if (haulageFilePath != null) {
        request.files.add(await http.MultipartFile.fromPath('haulage_file', haulageFilePath));
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200 || response.statusCode == 201) {
        return true;
      } else {
        debugPrint('Submit Order Failed: ${response.body}');
        return false;
      }
    } catch (e) {
      debugPrint('API submitOrder error: $e');
      return false;
    }
  }

  /// 4. Update Supir Action (Multipart)
  static Future<bool> updateSubTaskAction({
    required String taskId, 
    required String actionType,
    required String note,
    String? photoPath,
  }) async {
    try {
      // API needs database ID. If taskId is task_number (REQ-...), we must ensure Laravel can handle it.
      // Laravel route: /api/sub-tasks/{id}/action. 
      final url = Uri.parse('$baseUrl/sub-tasks/$taskId/action');
      var request = http.MultipartRequest('POST', url);
      request.headers['Accept'] = 'application/json';
      
      // Spoof PATCH request for Laravel
      request.fields['_method'] = 'PATCH';
      request.fields['action_type'] = actionType;
      request.fields['note'] = note;

      if (photoPath != null) {
        request.files.add(await http.MultipartFile.fromPath('photo', photoPath));
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        return true;
      } else {
        debugPrint('Update Action Failed: ${response.body}');
        return false;
      }
    } catch (e) {
      debugPrint('API updateSubTaskAction error: $e');
      return false;
    }
  }
}
