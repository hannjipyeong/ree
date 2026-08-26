import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';
import 'package:bkj_app/main.dart'; // import rootScaffoldMessengerKey
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/features/all_in/models/cargo_entry.dart';

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
      final t = DateTime.now().millisecondsSinceEpoch;
      final url = Uri.parse('$baseUrl/user?_t=$t');
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
      final t = DateTime.now().millisecondsSinceEpoch;
      var urlStr = '$baseUrl/orders?role=$role&_t=$t';
      if (supirType != null) {
        urlStr += '&supir_type=$supirType';
      }
      final url = Uri.parse(urlStr);
      final headers = await getHeaders();
      final response = await http.get(url, headers: headers);

      debugPrint('DEBUG: getOrders URL: $url');
      debugPrint('DEBUG: getOrders status: ${response.statusCode}');
      debugPrint('DEBUG: getOrders body: ${response.body}');

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          final data = body['data'] as List;
          List<AppOrder> appOrders = [];

          if (role == 'supir') {
            // Data is a list of SubTasks
            for (var task in data) {
              final order = task['order'] ?? {};
              
              // Calculate hierarchy logic
              final rawSubTasks = order['sub_tasks'] ?? [];
              final hierarchy = ['Haulage', 'Lolo', 'Penumpukan', 'TKBM'];
              final sortedSubTasks = (rawSubTasks as List)
                  .where((st) => hierarchy.contains(st['service_type']))
                  .toList();
              sortedSubTasks.sort((a, b) => hierarchy.indexOf(a['service_type']).compareTo(hierarchy.indexOf(b['service_type'])));
              
              int currentIndex = sortedSubTasks.indexWhere((st) => st['id'] == task['id']);

              // Parse containers and their progress
              List<AppContainer> parsedContainers = [];
              final rawContainers = order['containers'] ?? [];
              final rawProgress = task['container_progress'] ?? task['containerProgress'] ?? [];
              
              for (var c in rawContainers) {
                // Find matching progress
                final progressMap = rawProgress.firstWhere(
                  (p) => p['order_container_id'] == c['id'],
                  orElse: () => null,
                );
                
                AppContainerProgress? progObj;
                if (progressMap != null) {
                  String? lockedIn;
                  String? lockedOut;

                  if (currentIndex != -1) {
                    // Check IN constraint (look at previous subtask in hierarchy)
                    if (currentIndex > 0) {
                      final prevSubTask = sortedSubTasks[currentIndex - 1];
                      final prevProgressList = prevSubTask['container_progress'] ?? prevSubTask['containerProgress'] ?? [];
                      final prevProg = prevProgressList.firstWhere(
                        (p) => p['order_container_id'] == c['id'],
                        orElse: () => null,
                      );
                      if (prevProg == null || (prevProg['status'] != 'In' && prevProg['status'] != 'Out')) {
                        lockedIn = "Menunggu IN dari ${prevSubTask['service_type']}";
                      }
                    }

                    // Check OUT constraint (look at next subtask in hierarchy)
                    if (currentIndex < sortedSubTasks.length - 1) {
                      final nextSubTask = sortedSubTasks[currentIndex + 1];
                      final nextProgressList = nextSubTask['container_progress'] ?? nextSubTask['containerProgress'] ?? [];
                      final nextProg = nextProgressList.firstWhere(
                        (p) => p['order_container_id'] == c['id'],
                        orElse: () => null,
                      );
                      if (nextProg == null || nextProg['status'] != 'Out') {
                        lockedOut = "Menunggu OUT dari ${nextSubTask['service_type']}";
                      }
                    }
                  }

                  progObj = AppContainerProgress(
                    id: progressMap['id'] ?? 0,
                    subTaskId: progressMap['sub_task_id'] ?? 0,
                    containerId: progressMap['order_container_id'] ?? 0,
                    status: progressMap['status'] ?? 'Pending',
                    inNote: progressMap['in_note'],
                    inPhotoPath: progressMap['in_photo_path'],
                    inTime: DateTime.tryParse(progressMap['in_time'] ?? ''),
                    outNote: progressMap['out_note'],
                    outPhotoPath: progressMap['out_photo_path'],
                    outTime: DateTime.tryParse(progressMap['out_time'] ?? ''),
                    lockedReasonIn: lockedIn,
                    lockedReasonOut: lockedOut,
                  );
                }
                
                parsedContainers.add(AppContainer(
                  id: c['id'] ?? 0,
                  type: c['container_type'] ?? '',
                  size: c['container_size'] ?? '',
                  number: c['container_number'] ?? '',
                  sp3kkFileUrl: c['sp3kk_file_url'],
                  tkbmOption: c['tkbm_option'] ?? order['tkbm_option'],
                  progress: progObj,
                ));
              }

              appOrders.add(AppOrder(
                id: task['task_number']?.toString() ?? task['id'].toString(),
                customerName: order['nama_pt'] ?? 'Unknown Customer',
                serviceType: task['service_type'] ?? 'Unknown',
                source: order['source'] ?? 'ALL IN',
                date: DateTime.tryParse(task['created_at'] ?? '') ?? DateTime.now(),
                status: task['status'] ?? 'Masuk',
                payloadType: order['payload_type'] ?? 'Container',
                tkbmOption: order['tkbm_option'] ?? task['tkbm_option'],
                jenisBarang: order['jenis_barang'],
                jumlahBarang: order['jumlah_barang'],
                jumlahTonase: order['jumlah_tonase']?.toString(),
                nomorBl: order['nomor_bl'],
                vessel: order['vessel'],
                voyage: order['voyage'],
                noSuratJalan: order['no_surat_jalan'],
                noBp: order['no_bp'],
                nomorContainerCargo: order['nomor_container_cargo'],
                containers: parsedContainers,
                inNote: task['in_note'],
                outNote: task['out_note'],
              ));
            }
          } else {
            // Data is a list of Orders (For Customer history - if needed later)
            for (var order in data) {
              try {
                appOrders.add(AppOrder(
                  id: order['order_number']?.toString() ?? order['id'].toString(),
                  customerName: order['nama_pt'] ?? 'Unknown Customer',
                  serviceType: 'Multiple',
                  source: order['source'] ?? 'ALL IN',
                  date: DateTime.tryParse(order['tanggal_order'] ?? '') ?? DateTime.now(),
                  status: order['status'] ?? 'Submitted',
                  payloadType: order['payload_type'] ?? 'Container',
                  jenisBarang: order['jenis_barang'],
                  jumlahBarang: order['jumlah_barang'],
                  jumlahTonase: order['jumlah_tonase']?.toString(),
                  nomorBl: order['nomor_bl'],
                  vessel: order['vessel'],
                  voyage: order['voyage'],
                  noSuratJalan: order['no_surat_jalan'],
                  noBp: order['no_bp'],
                  nomorContainerCargo: order['nomor_container_cargo'],
                ));
              } catch (e) {
                debugPrint('DEBUG: Error parsing order: $e, data: $order');
              }
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

  static Future<bool> submitOrder({
    required String source,
    required String namaPt,
    String? namaPbm,
    required String noTelp,
    required String wilayah,
    required String lokasiFasilitas,
    required String jenisKegiatan,
    required String payloadType,
    required Set<String> services,
    List<Map<String, dynamic>>? containers,
    List<CargoEntry>? cargos,
    String? haulageFilePath,
    Uint8List? haulageFileBytes,
    String? haulageFileName,
  }) async {
    try {
      final request = http.MultipartRequest('POST', Uri.parse('$baseUrl/orders'));
      
      final headers = await getHeaders();
      request.headers.addAll(headers);
      request.fields['source'] = source;
      request.fields['tanggal_order'] = DateTime.now().toIso8601String().substring(0, 10);
      request.fields['nama_pt'] = namaPt;
      request.fields['nama_pbm'] = namaPbm ?? '';
      request.fields['no_telp'] = noTelp;
      request.fields['wilayah'] = wilayah;
      request.fields['lokasi_fasilitas'] = lokasiFasilitas;
      request.fields['jenis_kegiatan'] = jenisKegiatan;
      request.fields['payload_type'] = payloadType;
      
      if (cargos != null && cargos.isNotEmpty) {
        // Text fields
        final jenisBarang = cargos.map((c) => c.jenisBarang?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final jumlahBarang = cargos.map((c) => c.jumlahBarang?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final jumlahTonase = cargos.map((c) => c.jumlahTonase?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final nomorBl = cargos.map((c) => c.nomorBl?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final vessel = cargos.map((c) => c.vessel?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final voyage = cargos.map((c) => c.voyage?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final noSuratJalan = cargos.map((c) => c.noSuratJalan?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final noBp = cargos.map((c) => c.noBp?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');
        final nomorContainerCargo = cargos.map((c) => c.nomorContainerCargo?.trim()).where((s) => s != null && s.isNotEmpty).join(', ');

        if (jenisBarang.isNotEmpty) request.fields['jenis_barang'] = jenisBarang;
        if (jumlahBarang.isNotEmpty) request.fields['jumlah_barang'] = jumlahBarang;
        if (jumlahTonase.isNotEmpty) request.fields['jumlah_tonase'] = jumlahTonase;
        if (nomorBl.isNotEmpty) request.fields['nomor_bl'] = nomorBl;
        if (vessel.isNotEmpty) request.fields['vessel'] = vessel;
        if (voyage.isNotEmpty) request.fields['voyage'] = voyage;
        if (noSuratJalan.isNotEmpty) request.fields['no_surat_jalan'] = noSuratJalan;
        if (noBp.isNotEmpty) request.fields['no_bp'] = noBp;
        if (nomorContainerCargo.isNotEmpty) request.fields['nomor_container_cargo'] = nomorContainerCargo;

        // Files - array of cargo files
        for (var c in cargos) {
          if (c.cargoFileBytes != null && c.cargoFileName != null) {
            request.files.add(http.MultipartFile.fromBytes('cargo_files[]', c.cargoFileBytes!, filename: c.cargoFileName));
          }
        }
      }

      // JSON strings for arrays
      request.fields['services'] = jsonEncode(services.toList());
      if (containers != null && containers.isNotEmpty) {
        request.fields['containers'] = jsonEncode(containers);
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

  /// 4.1 Fetch Notifications (IN/OUT proofs + new tasks per role)
  static Future<Map<String, dynamic>?> getNotifications() async {
    try {
      final t = DateTime.now().millisecondsSinceEpoch;
      final url = Uri.parse('$baseUrl/notifications?_t=$t');
      final headers = await getHeaders();
      final response = await http.get(url, headers: headers);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          return body;
        }
      }
      return null;
    } catch (e) {
      debugPrint('API getNotifications error: $e');
      return null;
    }
  }

  /// 4.2 Quick Notification Summary (for badge polling)
  static Future<Map<String, dynamic>?> getNotificationSummary() async {
    try {
      final t = DateTime.now().millisecondsSinceEpoch;
      final url = Uri.parse('$baseUrl/notifications/summary?_t=$t');
      final headers = await getHeaders();
      final response = await http.get(url, headers: headers);

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) return body;
      }
      return null;
    } catch (e) {
      debugPrint('API getNotificationSummary error: $e');
      return null;
    }
  }

  /// 4.3 Mark all notifications as read
  static Future<bool> markNotificationsRead() async {
    try {
      final url = Uri.parse('$baseUrl/notifications/mark-read');
      final headers = await getHeaders();
      final response = await http.post(url, headers: headers);
      return response.statusCode == 200;
    } catch (e) {
      debugPrint('API markNotificationsRead error: $e');
      return false;
    }
  }

  /// 5. Update Supir Action (Multipart)
  static Future<bool> updateSubTaskAction({
    required String taskId,
    required String actionType,
    required String note,
    int? containerId,
    List<dynamic>? photos, // Accepting List<UploadedFile> from SupirActionScreen
  }) async {
    try {
      final url = Uri.parse('$baseUrl/sub-tasks/$taskId/action');
      final headers = await getHeaders();
      
      final request = http.MultipartRequest('POST', url);
      request.headers.addAll(headers);
      
      request.fields['_method'] = 'PATCH'; // Laravel spoofing
      request.fields['action_type'] = actionType;
      request.fields['note'] = note;
      if (containerId != null) {
        request.fields['container_id'] = containerId.toString();
      }

      if (photos != null && photos.isNotEmpty) {
        for (var photo in photos) {
          if (photo.bytes != null && photo.name != null) {
            request.files.add(http.MultipartFile.fromBytes('photos[]', photo.bytes!, filename: photo.name));
          }
        }
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
