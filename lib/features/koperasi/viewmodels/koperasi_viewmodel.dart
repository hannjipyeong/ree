import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/features/all_in/models/container_entry.dart';
import 'package:bkj_app/core/services/api_service.dart';

/// ViewModel for the KOPERASI multi-step order form.
/// Extends the ALL IN logic with an extra wilayah (Utara) and manual PBM name.
class KoperasiViewModel extends ChangeNotifier {
  // ─── Page 1 State ────────────────────────────────────────────────────────────
  DateTime? _tanggalOrder;
  String? _wilayah;
  String? _namaPt;
  String? _namaPbm; // Manual input text
  String? _noTelp;
  String? _lokasiFasilitas;
  String? _jenisKegiatan;

  // ─── Page 2 State ────────────────────────────────────────────────────────────
  String _payloadType = AppConstants.payloadContainer;
  final List<ContainerEntry> _containers = [ContainerEntry()];
  String? _cargoFileName;
  String? _cargoFilePath;
  Uint8List? _cargoFileBytes;

  // ─── Page 3 State ────────────────────────────────────────────────────────────
  final Set<String> _selectedServices = {};
  String? _haulageFileName;
  String? _haulageFilePath;
  Uint8List? _haulageFileBytes;
  String? _tkbmOption;

  bool _isSubmitting = false;
  String? _errorMessage;

  // ─── Page 1 Getters ──────────────────────────────────────────────────────────
  DateTime? get tanggalOrder => _tanggalOrder;
  String? get wilayah => _wilayah;
  String? get namaPt => _namaPt;
  String? get namaPbm => _namaPbm;
  String? get noTelp => _noTelp;
  String? get lokasiFasilitas => _lokasiFasilitas;
  String? get jenisKegiatan => _jenisKegiatan;

  List<String> get availableLokasi {
    if (_wilayah == null) return [];
    switch (_wilayah) {
      case AppConstants.wilayahSelatan:
        return AppConstants.lokasiFasilitasSelatan;
      case AppConstants.wilayahEximen:
        return AppConstants.lokasiFasilitasEximen;
      case AppConstants.wilayahUtara:
        return AppConstants.lokasiFasilitasUtara;
      default:
        return [];
    }
  }

  // ─── Page 2 Getters ──────────────────────────────────────────────────────────
  String get payloadType => _payloadType;
  List<ContainerEntry> get containers => List.unmodifiable(_containers);
  bool get canAddContainer => _containers.length < AppConstants.maxContainers;
  String? get cargoFileName => _cargoFileName;
  String? get cargoFilePath => _cargoFilePath;

  // ─── Page 3 Getters ──────────────────────────────────────────────────────────
  Set<String> get selectedServices => Set.unmodifiable(_selectedServices);
  bool isServiceSelected(String service) => _selectedServices.contains(service);
  String? get haulageFileName => _haulageFileName;
  String? get haulageFilePath => _haulageFilePath;
  String? get tkbmOption => _tkbmOption;

  bool get isSubmitting => _isSubmitting;
  String? get errorMessage => _errorMessage;

  // ─── Page 1 Setters ──────────────────────────────────────────────────────────
  void setTanggalOrder(DateTime? value) {
    _tanggalOrder = value;
    notifyListeners();
  }

  void setWilayah(String? value) {
    if (_wilayah == value) return;
    _wilayah = value;
    _lokasiFasilitas = null;
    _jenisKegiatan = null;
    notifyListeners();
  }

  void setNamaPt(String value) {
    _namaPt = value;
    notifyListeners();
  }

  void setNamaPbm(String value) {
    _namaPbm = value;
    notifyListeners();
  }

  void setNoTelp(String value) {
    _noTelp = value;
    notifyListeners();
  }

  void setLokasiFasilitas(String? value) {
    if (_lokasiFasilitas == value) return;
    _lokasiFasilitas = value;
    _jenisKegiatan = AppConstants.jenisKegiatanMapping[value];
    notifyListeners();
  }

  // ─── Page 2 Setters ──────────────────────────────────────────────────────────
  void setPayloadType(String type) {
    if (_payloadType == type) return;
    _payloadType = type;
    notifyListeners();
  }

  void addContainer() {
    if (!canAddContainer) return;
    _containers.add(ContainerEntry());
    notifyListeners();
  }

  void removeContainer(int index) {
    if (_containers.length <= 1) return;
    _containers.removeAt(index);
    notifyListeners();
  }

  void updateContainer(int index, ContainerEntry updated) {
    if (index < 0 || index >= _containers.length) return;
    _containers[index] = updated;
    notifyListeners();
  }

  void setCargoFile({required String name, required String path, Uint8List? bytes}) {
    _cargoFileName = name;
    _cargoFilePath = path;
    _cargoFileBytes = bytes;
    notifyListeners();
  }

  void clearCargoFile() {
    _cargoFileName = null;
    _cargoFilePath = null;
    _cargoFileBytes = null;
    notifyListeners();
  }

  // ─── Page 3 Setters ──────────────────────────────────────────────────────────
  void toggleService(String service) {
    if (_selectedServices.contains(service)) {
      _selectedServices.remove(service);
    } else {
      _selectedServices.add(service);
    }
    notifyListeners();
  }

  void setHaulageFile({required String name, required String path, Uint8List? bytes}) {
    _haulageFileName = name;
    _haulageFilePath = path;
    _haulageFileBytes = bytes;
    notifyListeners();
  }

  void clearHaulageFile() {
    _haulageFileName = null;
    _haulageFilePath = null;
    _haulageFileBytes = null;
    notifyListeners();
  }

  void setTbkmOption(String? option) {
    _tkbmOption = option;
    notifyListeners();
  }

  Future<bool> submitOrder() async {
    _isSubmitting = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final servicesToSubmit = _selectedServices.isEmpty ? {'Haulage'} : _selectedServices;
      
      final containerList = _payloadType == AppConstants.payloadContainer 
          ? _containers.map((c) => c.toJson()).toList() 
          : null;

      final success = await ApiService.submitOrder(
        source: 'Koperasi',
        namaPt: _namaPt ?? 'Unknown PT',
        namaPbm: _namaPbm ?? 'PT Bintang Kepri Jaya',
        noTelp: _noTelp ?? '081234567890',
        wilayah: _wilayah ?? 'Utara',
        lokasiFasilitas: _lokasiFasilitas ?? 'TPFT',
        jenisKegiatan: _jenisKegiatan ?? 'cek fisik',
        payloadType: _payloadType,
        services: servicesToSubmit,
        containers: containerList,
        cargoFilePath: _cargoFilePath,
        haulageFilePath: _haulageFilePath,
        cargoFileBytes: _cargoFileBytes,
        cargoFileName: _cargoFileName,
        haulageFileBytes: _haulageFileBytes,
        haulageFileName: _haulageFileName,
      );

      if (success) {
        _isSubmitting = false;
        notifyListeners();
        return true;
      } else {
        _isSubmitting = false;
        _errorMessage = 'Gagal mengirim order ke server.';
        notifyListeners();
        return false;
      }
    } catch (e) {
      _isSubmitting = false;
      _errorMessage = 'Gagal mengirim order: $e';
      notifyListeners();
      return false;
    }
  }

  void resetForm() {
    _tanggalOrder = null;
    _wilayah = null;
    _namaPt = null;
    _namaPbm = null;
    _noTelp = null;
    _lokasiFasilitas = null;
    _jenisKegiatan = null;
    _payloadType = AppConstants.payloadContainer;
    _containers.clear();
    _containers.add(ContainerEntry());
    _cargoFileName = null;
    _cargoFilePath = null;
    _cargoFileBytes = null;
    _selectedServices.clear();
    _haulageFileName = null;
    _haulageFilePath = null;
    _haulageFileBytes = null;
    _tkbmOption = null;
    _errorMessage = null;
    notifyListeners();
  }
}
