/// Represents a single cargo entry in the dynamic list.
/// Immutable value object — use [copyWith] to create updated instances.
import 'dart:typed_data';

class CargoEntry {
  final String? jenisBarang;
  final String? jumlahBarang;
  final String? jumlahTonase;
  final String? nomorBl;
  final String? vessel;
  final String? voyage;
  final String? noSuratJalan;
  final String? noBp;
  final String? nomorContainerCargo;
  final String? cargoFileName;
  final String? cargoFilePath;
  final Uint8List? cargoFileBytes;

  CargoEntry({
    this.jenisBarang,
    this.jumlahBarang,
    this.jumlahTonase,
    this.nomorBl,
    this.vessel,
    this.voyage,
    this.noSuratJalan,
    this.noBp,
    this.nomorContainerCargo,
    this.cargoFileName,
    this.cargoFilePath,
    this.cargoFileBytes,
  });

  CargoEntry copyWith({
    String? jenisBarang,
    String? jumlahBarang,
    String? jumlahTonase,
    String? nomorBl,
    String? vessel,
    String? voyage,
    String? noSuratJalan,
    String? noBp,
    String? nomorContainerCargo,
    String? cargoFileName,
    String? cargoFilePath,
    Uint8List? cargoFileBytes,
    bool clearFile = false,
  }) {
    return CargoEntry(
      jenisBarang: jenisBarang ?? this.jenisBarang,
      jumlahBarang: jumlahBarang ?? this.jumlahBarang,
      jumlahTonase: jumlahTonase ?? this.jumlahTonase,
      nomorBl: nomorBl ?? this.nomorBl,
      vessel: vessel ?? this.vessel,
      voyage: voyage ?? this.voyage,
      noSuratJalan: noSuratJalan ?? this.noSuratJalan,
      noBp: noBp ?? this.noBp,
      nomorContainerCargo: nomorContainerCargo ?? this.nomorContainerCargo,
      cargoFileName: clearFile ? null : (cargoFileName ?? this.cargoFileName),
      cargoFilePath: clearFile ? null : (cargoFilePath ?? this.cargoFilePath),
      cargoFileBytes: clearFile ? null : (cargoFileBytes ?? this.cargoFileBytes),
    );
  }

  /// Validates that all required fields are filled.
  bool get isValid =>
      (jenisBarang?.trim().isNotEmpty ?? false) &&
      (jumlahBarang?.trim().isNotEmpty ?? false) &&
      (jumlahTonase?.trim().isNotEmpty ?? false) &&
      (nomorBl?.trim().isNotEmpty ?? false) &&
      (vessel?.trim().isNotEmpty ?? false) &&
      (voyage?.trim().isNotEmpty ?? false) &&
      (noSuratJalan?.trim().isNotEmpty ?? false) &&
      (noBp?.trim().isNotEmpty ?? false);

  /// Returns a map for API serialization.
  Map<String, dynamic> toJson() => {
        'jenis_barang': jenisBarang,
        'jumlah_barang': jumlahBarang,
        'jumlah_tonase': jumlahTonase,
        'nomor_bl': nomorBl,
        'vessel': vessel,
        'voyage': voyage,
        'no_surat_jalan': noSuratJalan,
        'no_bp': noBp,
        'nomor_container_cargo': nomorContainerCargo,
      };

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is CargoEntry &&
          runtimeType == other.runtimeType &&
          jenisBarang == other.jenisBarang &&
          jumlahBarang == other.jumlahBarang &&
          jumlahTonase == other.jumlahTonase &&
          nomorBl == other.nomorBl &&
          vessel == other.vessel &&
          voyage == other.voyage &&
          noSuratJalan == other.noSuratJalan &&
          noBp == other.noBp &&
          nomorContainerCargo == other.nomorContainerCargo &&
          cargoFileName == other.cargoFileName;

  @override
  int get hashCode =>
      (jenisBarang?.hashCode ?? 0) ^
      (jumlahBarang?.hashCode ?? 0) ^
      (jumlahTonase?.hashCode ?? 0) ^
      (nomorBl?.hashCode ?? 0) ^
      (vessel?.hashCode ?? 0) ^
      (voyage?.hashCode ?? 0) ^
      (noSuratJalan?.hashCode ?? 0) ^
      (noBp?.hashCode ?? 0) ^
      (nomorContainerCargo?.hashCode ?? 0) ^
      (cargoFileName?.hashCode ?? 0);
}
