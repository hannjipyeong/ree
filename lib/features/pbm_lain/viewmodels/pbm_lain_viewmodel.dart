import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/features/all_in/models/container_entry.dart';
import 'package:bkj_app/core/repositories/mock_order_repository.dart';

/// ViewModel for the PBM LAIN streamlined multi-step order form.
/// Page 2 handles Containers ONLY (no Cargo). Page 3 has fewer services.
class PbmLainViewModel extends ChangeNotifier {
  final MockOrderRepository _orderRepository;

  PbmLainViewModel({required MockOrderRepository orderRepository})
      : _orderRepository = orderRepository;

  // ─── Page 1 State ────────────────────────────────────────────────────────────
  DateTime? _tanggalOrder;
  String? _wilayah;
  String? _namaPt;
  String? _namaPbm; // Editable, no default value
  String? _noTelp;
  String? _lokasiFasilitas;
  String? _jenisKegiatan;

  // ─── Page 2 State (Container ONLY) ──────────────────────────────────────────
  final List<ContainerEntry> _containers = [ContainerEntry()];

  // ─── Page 3 State (fewer services) ──────────────────────────────────────────
  final Set<String> _selectedServices = {};
  String? _tbkmOption;

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
  List<ContainerEntry> get containers => List.unmodifiable(_containers);
  bool get canAddContainer => _containers.length < AppConstants.maxContainers;

  // ─── Page 3 Getters ──────────────────────────────────────────────────────────
  Set<String> get selectedServices => Set.unmodifiable(_selectedServices);
  bool isServiceSelected(String service) => _selectedServices.contains(service);
  String? get tbkmOption => _tbkmOption;

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
    _tbkmOption = option;
    notifyListeners();
  }

  Future<bool> submitOrder() async {
    _isSubmitting = true;
    _errorMessage = null;
    notifyListeners();
    try {
      await Future.delayed(const Duration(seconds: 1));
      
      _orderRepository.addOrderFromCustomer(
        customerName: _namaPt ?? 'Unknown PT',
        source: 'PBM Lain',
        selectedServices: _selectedServices,
      );

      _isSubmitting = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isSubmitting = false;
      _errorMessage = 'Gagal mengirim order. Silakan coba lagi.';
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
    _containers.clear();
    _containers.add(ContainerEntry());
    _selectedServices.clear();
    _tbkmOption = null;
    _errorMessage = null;
    notifyListeners();
  }
}
