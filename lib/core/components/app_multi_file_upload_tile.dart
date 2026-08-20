import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:file_picker/file_picker.dart';
import 'package:image_picker/image_picker.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

class UploadedFile {
  final String name;
  final Uint8List? bytes;
  final String? path;

  UploadedFile({required this.name, this.bytes, this.path});
}

class AppMultiFileUploadTile extends StatefulWidget {
  final String label;
  final String? hint;
  final List<UploadedFile> files;
  final List<String>? allowedExtensions;
  final void Function(List<UploadedFile> newFiles) onFilesSelected;
  final void Function(UploadedFile fileToRemove) onFileRemoved;
  final String? Function()? validator;

  const AppMultiFileUploadTile({
    super.key,
    required this.label,
    required this.files,
    required this.onFilesSelected,
    required this.onFileRemoved,
    this.hint,
    this.allowedExtensions,
    this.validator,
  });

  @override
  State<AppMultiFileUploadTile> createState() => _AppMultiFileUploadTileState();
}

class _AppMultiFileUploadTileState extends State<AppMultiFileUploadTile> {
  bool _isLoading = false;
  String? _validationError;
  final ImagePicker _imagePicker = ImagePicker();

  Future<void> _showPickerOptions(BuildContext context) async {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Wrap(
            children: [
              ListTile(
                leading: const Icon(Icons.camera_alt),
                title: const Text('Kamera (Satu per Satu)'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickImage(ImageSource.camera);
                },
              ),
              ListTile(
                leading: const Icon(Icons.photo_library),
                title: const Text('Galeri (Pilih Banyak)'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickMultipleImages();
                },
              ),
              ListTile(
                leading: const Icon(Icons.folder),
                title: const Text('File Explorer (Pilih Banyak)'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickMultipleFiles();
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _pickImage(ImageSource source) async {
    setState(() => _isLoading = true);
    
    try {
      final XFile? image = await _imagePicker.pickImage(source: source);
      if (image != null) {
        final bytes = await image.readAsBytes();
        final path = kIsWeb ? null : image.path;
        final newFile = UploadedFile(name: image.name, bytes: bytes, path: path);
        widget.onFilesSelected([newFile]);
        _validationError = null;
      }
    } catch (e) {
      _validationError = 'Gagal mengambil gambar: $e';
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _pickMultipleImages() async {
    setState(() => _isLoading = true);
    
    try {
      final List<XFile> images = await _imagePicker.pickMultiImage();
      if (images.isNotEmpty) {
        List<UploadedFile> newFiles = [];
        for (var image in images) {
          final bytes = await image.readAsBytes();
          final path = kIsWeb ? null : image.path;
          newFiles.add(UploadedFile(name: image.name, bytes: bytes, path: path));
        }
        widget.onFilesSelected(newFiles);
        _validationError = null;
      }
    } catch (e) {
      _validationError = 'Gagal memilih gambar: $e';
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _pickMultipleFiles() async {
    setState(() => _isLoading = true);

    try {
      final type = (kIsWeb || widget.allowedExtensions == null)
          ? FileType.any
          : FileType.custom;

      final result = await FilePicker.platform.pickFiles(
        type: type,
        allowedExtensions: kIsWeb ? null : widget.allowedExtensions,
        withData: true, 
        allowMultiple: true,
      );

      if (result != null && result.files.isNotEmpty) {
        _validationError = null;
        List<UploadedFile> newFiles = [];
        for (var file in result.files) {
          final path = kIsWeb ? null : file.path;
          newFiles.add(UploadedFile(name: file.name, bytes: file.bytes, path: path));
        }
        widget.onFilesSelected(newFiles);
      }
    } catch (e) {
       _validationError = 'Gagal memilih file: $e';
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  String _formatExtensions() {
    if (widget.allowedExtensions == null) return '';
    return widget.allowedExtensions!
        .map((e) => e.toUpperCase())
        .join(', ');
  }

  @override
  Widget build(BuildContext context) {
    final hasFiles = widget.files.isNotEmpty;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(widget.label, style: AppTextStyles.label),
            if (hasFiles)
              Text('${widget.files.length} Foto/File', style: AppTextStyles.caption.copyWith(color: AppColors.accent, fontWeight: FontWeight.bold)),
          ],
        ),
        const SizedBox(height: 6),
        
        if (hasFiles) ...[
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: widget.files.length,
            padding: const EdgeInsets.only(bottom: 8),
            itemBuilder: (ctx, i) {
              final file = widget.files[i];
              return Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: AppColors.accent.withValues(alpha: 0.06),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.accent, width: 1.5),
                  ),
                  child: _FileDisplay(
                    fileName: file.name,
                    onClear: () => widget.onFileRemoved(file),
                  ),
                ),
              );
            },
          )
        ],

        GestureDetector(
          onTap: () => _showPickerOptions(context),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: _validationError != null
                    ? AppColors.error
                    : AppColors.divider,
                width: _validationError != null ? 1.5 : 1,
              ),
            ),
            child: _isLoading
                ? const Center(
                    child: SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                  )
                : _UploadPrompt(
                    hint: widget.hint,
                    extensions: _formatExtensions(),
                  ),
          ),
        ),
        if (_validationError != null)
          Padding(
            padding: const EdgeInsets.only(top: 6, left: 14),
            child: Text(
              _validationError!,
              style: AppTextStyles.caption.copyWith(color: AppColors.error),
            ),
          ),
      ],
    );
  }
}

class _UploadPrompt extends StatelessWidget {
  final String? hint;
  final String extensions;

  const _UploadPrompt({this.hint, required this.extensions});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: AppColors.primary.withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(8),
          ),
          child: const Icon(
            Icons.add_photo_alternate_outlined,
            color: AppColors.primary,
            size: 22,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                hint ?? 'Ketuk untuk menambah file / foto',
                style: AppTextStyles.body1,
              ),
              if (extensions.isNotEmpty)
                Text(
                  'Format: $extensions',
                  style: AppTextStyles.caption,
                ),
            ],
          ),
        ),
        const Icon(
          Icons.arrow_forward_ios,
          size: 14,
          color: AppColors.textHint,
        ),
      ],
    );
  }
}

class _FileDisplay extends StatelessWidget {
  final String fileName;
  final VoidCallback onClear;

  const _FileDisplay({required this.fileName, required this.onClear});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: AppColors.accent.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: const Icon(
            Icons.check_circle_outline,
            color: AppColors.accent,
            size: 22,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                fileName,
                style: AppTextStyles.body1.copyWith(
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                ),
                overflow: TextOverflow.ellipsis,
              ),
              Text('File dipilih', style: AppTextStyles.caption),
            ],
          ),
        ),
        GestureDetector(
          onTap: onClear,
          child: Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: AppColors.error.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(6),
            ),
            child: const Icon(
              Icons.close,
              color: AppColors.error,
              size: 16,
            ),
          ),
        ),
      ],
    );
  }
}
