import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:file_picker/file_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:bkj_app/core/components/components.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/core/services/api_service.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_constants.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/koperasi/viewmodels/koperasi_viewmodel.dart';

/// Koperasi — Page 1: Informasi Dasar Order (Wilayah, Nama PT, Nama PBM, Lokasi).
class KoperasiPage1Screen extends StatefulWidget {
  const KoperasiPage1Screen({super.key});

  @override
  State<KoperasiPage1Screen> createState() => _KoperasiPage1ScreenState();
}

class _KoperasiPage1ScreenState extends State<KoperasiPage1Screen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _namaPtCtrl;
  late final TextEditingController _namaPbmCtrl;
  late final TextEditingController _noTelpCtrl;
  late final TextEditingController _jenisKegiatanCtrl;

  @override
  void initState() {
    super.initState();
    final vm = context.read<KoperasiViewModel>();
    _namaPtCtrl = TextEditingController(text: vm.namaPt ?? '');
    _namaPbmCtrl = TextEditingController(text: vm.namaPbm ?? '');
    _noTelpCtrl = TextEditingController(text: vm.noTelp ?? '');
    _jenisKegiatanCtrl = TextEditingController(text: (vm.jenisKegiatan ?? '').toUpperCase());
  }

  @override
  void dispose() {
    _namaPtCtrl.dispose();
    _namaPbmCtrl.dispose();
    _noTelpCtrl.dispose();
    _jenisKegiatanCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<KoperasiViewModel>();

    // Update text controller if viewmodel value changes externally
    if (vm.jenisKegiatan != null &&
        _jenisKegiatanCtrl.text.toUpperCase() != vm.jenisKegiatan!.toUpperCase()) {
      _jenisKegiatanCtrl.text = vm.jenisKegiatan!.toUpperCase();
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Order Koperasi'),
        leading: BackButton(
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: FormPageWrapper(
        formKey: _formKey,
        bottomBar: FormNavigationBar(
          onNext: () {
            if (_formKey.currentState?.validate() ?? false) {
              Navigator.pushNamed(context, AppRoutes.koperasiPage2);
            }
          },
          nextLabel: 'Lanjut',
        ),
        children: [
          FormStepIndicator(
            currentStep: 1,
            totalSteps: AppConstants.totalStepsKoperasi,
            stepLabel: 'Informasi Dasar',
          ),
          const SizedBox(height: 4),
          const FormInfoBanner(
            message: 'Isi informasi dasar order Koperasi. '
                'Pastikan data perusahaan dan lokasi sudah sesuai.',
          ),

          // ── Data Pemesan ─────────────────────────────────────────────
          SectionCard(
            title: 'Data Pemesan',
            icon: Icons.business_outlined,
            children: [
              AppDatePicker(
                label: 'Tanggal Order',
                selectedDate: vm.tanggalOrder,
                onDateSelected: vm.setTanggalOrder,
                formatDate: AppFormatters.toDDMMYYYY,
                validator: (d) => d == null ? 'Tanggal Order wajib diisi' : null,
              ),
              AppTextField(
                label: 'Nama Perusahaan / PT',
                hint: 'Masukkan nama PT',
                controller: _namaPtCtrl,
                onChanged: vm.setNamaPt,
                validator: (v) => AppValidators.required(
                  v,
                  fieldName: 'Nama Perusahaan / PT',
                ),
              ),
              AppTextField(
                label: 'Nama PBM',
                hint: 'Masukkan nama PBM',
                controller: _namaPbmCtrl,
                readOnly: vm.wilayah == AppConstants.wilayahUtara,
                onChanged: vm.setNamaPbm,
                validator: (v) => AppValidators.required(
                  v,
                  fieldName: 'Nama PBM',
                ),
              ),
              AppTextField(
                label: 'Nomor Telepon / WhatsApp',
                hint: 'Contoh: 08123456789',
                controller: _noTelpCtrl,
                keyboardType: TextInputType.phone,
                onChanged: vm.setNoTelp,
                validator: AppValidators.phoneNumber,
              ),
            ],
          ),

          // ── Lokasi & Fasilitas ─────────────────────────────────────────
          SectionCard(
            title: 'Lokasi & Fasilitas',
            icon: Icons.location_on_outlined,
            children: [
              AppDropdown<String>(
                label: 'Wilayah Operasional',
                value: vm.wilayah,
                items: AppConstants.wilayahKoperasi,
                itemLabel: (v) => v,
                onChanged: vm.setWilayah,
                validator: (v) => AppValidators.requiredDropdown(
                  v,
                  fieldName: 'Wilayah Operasional',
                ),
              ),
              AppConditionalDropdown<String>(
                label: 'Lokasi Fasilitas',
                value: vm.lokasiFasilitas,
                items: vm.availableLokasi,
                itemLabel: (v) => v.toUpperCase(),
                onChanged: vm.setLokasiFasilitas,
                validator: (v) => AppValidators.requiredDropdown(
                  v,
                  fieldName: 'Lokasi Fasilitas',
                ),
                emptyHint: 'Pilih Wilayah Operasional terlebih dahulu',
              ),
              if (vm.jenisKegiatan != null)
                Padding(
                  padding: const EdgeInsets.only(top: 16),
                  child: AppTextField(
                    label: 'Jenis Kegiatan',
                    hint: 'Masukkan Jenis Kegiatan',
                    controller: _jenisKegiatanCtrl,
                    readOnly: !(vm.lokasiFasilitas?.toLowerCase() == 'cfs' || 
                                vm.lokasiFasilitas?.toLowerCase() == 'tps' ||
                                vm.lokasiFasilitas?.toLowerCase() == 'gudang'),
                    onChanged: vm.setJenisKegiatan,
                    validator: (v) => AppValidators.required(v, fieldName: 'Jenis Kegiatan'),
                  ),
                ),

              // ── Field Berdampingan: Upload SPK & Download Draft Template ──
              const SizedBox(height: 16),
              Row(
                children: [
                  // Upload SPK File
                  Expanded(
                    child: InkWell(
                      onTap: () async {
                        final result = await FilePicker.platform.pickFiles(
                          type: FileType.custom,
                          allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
                          withData: true,
                        );
                        if (result != null && result.files.isNotEmpty) {
                          final file = result.files.first;
                          vm.setHaulageFile(
                            name: file.name,
                            path: file.path ?? '',
                            bytes: file.bytes,
                          );
                        }
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                        decoration: BoxDecoration(
                          color: vm.haulageFileName != null
                              ? AppColors.primary.withValues(alpha: 0.1)
                              : AppColors.background,
                          border: Border.all(
                            color: vm.haulageFileName != null
                                ? AppColors.primary
                                : AppColors.divider,
                          ),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.upload_file_outlined,
                              size: 18,
                              color: vm.haulageFileName != null ? AppColors.primary : AppColors.textSecondary,
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                vm.haulageFileName ?? 'Upload SPK',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: vm.haulageFileName != null ? AppColors.primary : AppColors.textSecondary,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            if (vm.haulageFileName != null)
                              GestureDetector(
                                onTap: vm.clearHaulageFile,
                                child: const Icon(Icons.close, size: 14, color: AppColors.error),
                              ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  // Download Draft Template SPK
                  Expanded(
                    child: InkWell(
                      onTap: () async {
                        final draftUrl = Uri.parse('${ApiService.baseUrl}/draft_template_spk');
                        if (await canLaunchUrl(draftUrl)) {
                          await launchUrl(draftUrl, mode: LaunchMode.externalApplication);
                        } else {
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Draft template SPK tersedia.')),
                            );
                          }
                        }
                      },
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          border: Border.all(color: const Color(0xFF93C5FD)),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Row(
                          children: [
                            Icon(
                              Icons.download_outlined,
                              size: 18,
                              color: Color(0xFF1D4ED8),
                            ),
                            SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                'Draft Template SPK',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF1D4ED8),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }
}
