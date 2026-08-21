import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/features/all_in/models/container_entry.dart';
import 'package:bkj_app/core/services/api_service.dart';

/// ViewModel for the PBM LAIN streamlined multi-step order form.
/// Page 2 handles Containers ONLY (no Cargo). Page 3 has fewer services.
class PbmLainViewModel extends ChangeNotifier {
  // ─── Page 1 State ────────────────────────────────────────────────────────────
  DateTime? _tanggalOrder;
  String? _wilayah;
  String? _namaPt;
  String? _namaPbm; // Editable, no default value
  String? _noTelp;
  String? _lokasiFasilitas;
  String? _jenisKegiatan;

  // ─── Page 2 State ────────────────────────────────────────────────────────────
  final Set<String> _payloadTypes = {AppConstants.payloadContainer};
  final List<ContainerEntry> _containers = [ContainerEntry()];

  // Cargo fields
  String? _jenisBarang;
  String? _jumlahBarang;
  String? _jumlahTonase;
  String? _nomorBl;
  String? _vessel;
  String? _voyage;
  String? _noSuratJalan;
  String? _noBp;
  String? _nomorContainerCargo;
  String? _cargoFileName;
  String? _cargoFilePath;
  Uint8List? _cargoFileBytes;

  // ─── Page 3 State (fewer services) ──────────────────────────────────────────
  final Set<String> _selectedServices = {};
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
    return AppConstants.lokasiFasilitasPbmLain; // Both Selatan & Eximen use the same locations
  }

  // ─── Page 2 Getters ──────────────────────────────────────────────────────────
  Set<String> get payloadTypes => Set.unmodifiable(_payloadTypes);
  String get payloadType => _payloadTypes.isEmpty ? AppConstants.payloadContainer : _payloadTypes.join(',');
  bool isPayloadSelected(String type) => _payloadTypes.contains(type);
  bool get hasContainer => _payloadTypes.contains(AppConstants.payloadContainer);
  bool get hasCargo => _payloadTypes.contains(AppConstants.payloadCargo);
  List<ContainerEntry> get containers => List.unmodifiable(_containers);
  bool get canAddContainer => _containers.length < AppConstants.maxContainers;

  String? get jenisBarang => _jenisBarang;
  String? get jumlahBarang => _jumlahBarang;
  String? get jumlahTonase => _jumlahTonase;
  String? get nomorBl => _nomorBl;
  String? get vessel => _vessel;
  String? get voyage => _voyage;
  String? get noSuratJalan => _noSuratJalan;
  String? get noBp => _noBp;
  String? get nomorContainerCargo => _nomorContainerCargo;
  String? get cargoFileName => _cargoFileName;

  // ─── Page 3 Getters ──────────────────────────────────────────────────────────
  Set<String> get selectedServices => Set.unmodifiable(_selectedServices);
  bool isServiceSelected(String service) => _selectedServices.contains(service);
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
  void togglePayloadType(String value) {
    if (_payloadTypes.contains(value)) {
      _payloadTypes.remove(value);
    } else {
      _payloadTypes.add(value);
    }
    notifyListeners();
  }

  void setPayloadType(String value) {
    _payloadTypes.clear();
    _payloadTypes.add(value);
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

  void setJenisBarang(String value) { _jenisBarang = value; notifyListeners(); }
  void setJumlahBarang(String value) { _jumlahBarang = value; notifyListeners(); }
  void setJumlahTonase(String value) { _jumlahTonase = value; notifyListeners(); }
  void setNomorBl(String value) { _nomorBl = value; notifyListeners(); }
  void setVessel(String value) { _vessel = value; notifyListeners(); }
  void setVoyage(String value) { _voyage = value; notifyListeners(); }
  void setNoSuratJalan(String value) { _noSuratJalan = value; notifyListeners(); }
  void setNoBp(String value) { _noBp = value; notifyListeners(); }
  void setNomorContainerCargo(String value) { _nomorContainerCargo = value; notifyListeners(); }

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

  void setTbkmOption(String? option) {
    _tkbmOption = option;
    notifyListeners();
  }

  Future<bool> submitOrder() async {
    _isSubmitting = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final servicesToSubmit = _selectedServices.isEmpty ? {'TKBM'} : _selectedServices;
      
      final containerList = hasContainer 
          ? _containers.map((c) => c.toJson()).toList() 
          : null;

      final success = await ApiService.submitOrder(
        source: 'LOLO',
        namaPt: _namaPt ?? 'Unknown PT',
        namaPbm: _namaPbm ?? 'Unknown PBM',
        noTelp: _noTelp ?? '081234567890',
        wilayah: _wilayah ?? 'Utara',
        lokasiFasilitas: _lokasiFasilitas ?? 'TPFT',
        jenisKegiatan: _jenisKegiatan ?? 'cek fisik',
        payloadType: payloadType,
        services: servicesToSubmit,
        containers: containerList,
        jenisBarang: hasCargo ? _jenisBarang : null,
        jumlahBarang: hasCargo ? _jumlahBarang : null,
        jumlahTonase: hasCargo ? _jumlahTonase : null,
        nomorBl: hasCargo ? _nomorBl : null,
        vessel: hasCargo ? _vessel : null,
        voyage: hasCargo ? _voyage : null,
        noSuratJalan: hasCargo ? _noSuratJalan : null,
        noBp: hasCargo ? _noBp : null,
        nomorContainerCargo: hasCargo ? _nomorContainerCargo : null,
        cargoFilePath: hasCargo ? _cargoFilePath : null,
        cargoFileBytes: hasCargo ? _cargoFileBytes : null,
        cargoFileName: hasCargo ? _cargoFileName : null,
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

  void resetForm({String? defaultNamaPt, bool hasDefaultAsuransi = false}) {
    _tanggalOrder = null;
    _wilayah = null;
    _namaPt = defaultNamaPt;
    _namaPbm = null;
    _noTelp = null;
    _lokasiFasilitas = null;
    _jenisKegiatan = null;
    _payloadTypes.clear();
    _payloadTypes.add(AppConstants.payloadContainer);
    _containers.clear();
    _containers.add(ContainerEntry());
    _jenisBarang = null;
    _jumlahBarang = null;
    _jumlahTonase = null;
    _nomorBl = null;
    _vessel = null;
    _voyage = null;
    _noSuratJalan = null;
    _noBp = null;
    _nomorContainerCargo = null;
    _cargoFileName = null;
    _cargoFilePath = null;
    _cargoFileBytes = null;
    _selectedServices.clear();
    if (hasDefaultAsuransi) {
      _selectedServices.add('Asuransi');
    }
    _tkbmOption = null;
    _errorMessage = null;
    notifyListeners();
  }
}
