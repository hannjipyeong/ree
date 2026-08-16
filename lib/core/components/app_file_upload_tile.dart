import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'dart:typed_data';

/// A tappable tile that triggers a file picker and displays the chosen file.
///
/// Supports allowed extensions filtering. Calls [onFileSelected] with the
/// file name and path, or [onCleared] when the user removes the selection.
///
/// Usage:
/// ```dart
/// AppFileUploadTile(
///   label: 'Dokumen Haulage',
///   hint: 'Upload surat jalan (PDF/JPG)',
///   fileName: vm.haulageFileName,
///   allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
///   onFileSelected: (name, path) => vm.setHaulageFile(name: name, path: path),
///   onCleared: vm.clearHaulageFile,
/// )
/// ```
class AppFileUploadTile extends StatefulWidget {
  final String label;
  final String? hint;
  final String? fileName;
  final List<String>? allowedExtensions;
  final void Function(String name, Uint8List? bytes, String? path) onFileSelected;
  final VoidCallback onCleared;
  final String? Function()? validator;

  const AppFileUploadTile({
    super.key,
    required this.label,
    required this.onFileSelected,
    required this.onCleared,
    this.hint,
    this.fileName,
    this.allowedExtensions,
    this.validator,
  });

  @override
  State<AppFileUploadTile> createState() => _AppFileUploadTileState();
}

class _AppFileUploadTileState extends State<AppFileUploadTile> {
  bool _isLoading = false;
  String? _validationError;

  Future<void> _pickFile() async {
    setState(() => _isLoading = true);

    final result = await FilePicker.platform.pickFiles(
      type: widget.allowedExtensions != null ? FileType.custom : FileType.any,
      allowedExtensions: widget.allowedExtensions,
      withData: true, // IMPORTANT: Needed for web bytes
    );

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (result != null) {
      final file = result.files.single;
      _validationError = null;
      widget.onFileSelected(file.name, file.bytes, file.path);
    }
  }

  void _clear() {
    setState(() => _validationError = null);
    widget.onCleared();
  }

  String _formatExtensions() {
    if (widget.allowedExtensions == null) return '';
    return widget.allowedExtensions!
        .map((e) => e.toUpperCase())
        .join(', ');
  }

  @override
  Widget build(BuildContext context) {
    final hasFile = widget.fileName != null && widget.fileName!.isNotEmpty;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(widget.label, style: AppTextStyles.label),
        const SizedBox(height: 6),
        GestureDetector(
          onTap: hasFile ? null : _pickFile,
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              color: hasFile
                  ? AppColors.accent.withValues(alpha: 0.06)
                  : AppColors.surface,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: _validationError != null
                    ? AppColors.error
                    : hasFile
                        ? AppColors.accent
                        : AppColors.divider,
                width: hasFile || _validationError != null ? 1.5 : 1,
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
                : hasFile
                    ? _FileDisplay(
                        fileName: widget.fileName!,
                        onClear: _clear,
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
            Icons.upload_file_outlined,
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
                hint ?? 'Ketuk untuk memilih file',
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
