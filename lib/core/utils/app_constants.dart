/// Application-wide constants. A single source of truth for route names,
/// asset paths, and business domain data to avoid magic strings.
class AppConstants {
  AppConstants._();

  // ─── Asset Paths ────────────────────────────────────────────────────────────
  static const String imagePlaceholderLogo = 'assets/images/logo.png';

  // ─── Business Domain: Wilayah Operasional ───────────────────────────────────
  static const String wilayahSelatan = 'Selatan';
  static const String wilayahEximen = 'Eximen';
  static const String wilayahUtara = 'Utara';

  static const List<String> wilayahAllIn = [wilayahSelatan, wilayahEximen];
  static const List<String> wilayahKoperasi = [
    wilayahSelatan,
    wilayahEximen,
    wilayahUtara,
  ];
  static const List<String> wilayahPbmLain = [wilayahSelatan, wilayahEximen];

  // ─── Jenis Kegiatan per Wilayah ─────────────────────────────────────────────
  static const Map<String, List<String>> jenisKegiatanSelatan = {
    wilayahSelatan: [
      'Bongkar',
      'Muat',
      'Stripping',
      'Stuffing',
      'Repair',
    ],
  };

  static const Map<String, List<String>> jenisKegiatanEximen = {
    wilayahEximen: [
      'Bongkar Eximen',
      'Muat Eximen',
    ],
  };

  static const Map<String, List<String>> jenisKegiatanUtara = {
    wilayahUtara: [
      'Bongkar Utara',
      'Muat Utara',
    ],
  };

  // ─── ALL IN Form Constants (Page 1) ─────────────────────────────────────────
  static const List<String> lokasiFasilitasSelatan = [
    'TPFT',
    'CFS',
    'loss cargo',
    'gudang',
    'tps',
  ];

  static const List<String> lokasiFasilitasEximen = [
    'cfs',
    'loss cargo',
    'gudang',
  ];

  static const List<String> lokasiFasilitasUtara = [
    'TPFT',
  ];

  static const List<String> lokasiFasilitasPbmLain = [
    'TPFT',
    'TPS',
    'CFS',
  ];

  static const Map<String, String> jenisKegiatanMapping = {
    'TPFT': 'cek fisik',
    'CFS': 'stripping / staffing',
    'cfs': 'stripping / staffing',
    'loss cargo': 'penumpukan',
    'gudang': 'penumpukan',
    'tps': 'penumpukan',
  };

  // ─── Payload Types ──────────────────────────────────────────────────────────
  static const String payloadContainer = 'Container';
  static const String payloadCargo = 'Cargo';

  // ─── Container Types ────────────────────────────────────────────────────────
  static const List<String> containerTypes = [
    '20\' GP',
    '40\' GP',
    '40\' HC',
    '20\' RF',
    '40\' RF',
    '20\' OT',
    '40\' OT',
    '20\' FR',
    '40\' FR',
    '20\' TK',
  ];

  // ─── Ukuran Container ───────────────────────────────────────────────────────
  static const List<String> containerSizes = ['10 ft', '20 ft', '40 ft', '45 ft', '60 ft'];

  // ─── Max Container Entries ──────────────────────────────────────────────────
  static const int maxContainers = 60;

  // ─── Layanan / Services ─────────────────────────────────────────────────────
  static const String serviceHaulage = 'Haulage';
  static const String serviceLolo = 'LOLO';
  static const String servicePenumpukan = 'Penumpukan';
  static const String serviceTKBM = 'TKBM';
  static const String serviceAsuransi = 'Asuransi';

  static const List<String> servicesAllIn = [
    serviceHaulage,
    serviceLolo,
    servicePenumpukan,
    serviceTKBM,
    serviceAsuransi,
  ];

  static const List<String> servicesKoperasi = [
    serviceHaulage,
    serviceLolo,
    servicePenumpukan,
    serviceTKBM,
    serviceAsuransi,
  ];

  static const List<String> servicesPbmLain = [
    serviceLolo,
    serviceTKBM,
    serviceAsuransi,
  ];

  // ─── TKBM Options ───────────────────────────────────────────────────────────
  static const List<String> tkbmOptions = ['Man Power', 'Man Power + Forklift'];

  // ─── Pagination / UI ────────────────────────────────────────────────────────
  static const int totalStepsAllIn = 3;
  static const int totalStepsKoperasi = 3;
  static const int totalStepsPbmLain = 3;
}
