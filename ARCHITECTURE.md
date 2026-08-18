# BKJ App — Complete Architecture Reference

> **For AI Agents**: Read this document in full before making any changes to the BKJ App codebase.
> It contains every architectural decision, pattern, and domain rule you need to avoid regressions.

---

## 1. Project Overview

| Property | Value |
|----------|-------|
| **App Name** | BKJ App |
| **Package** | `com.bkj.bkj_app` |
| **Platform** | Android (Flutter) |
| **Flutter Version** | 3.44.x |
| **Dart Version** | 3.12.x |
| **State Management** | Provider (`^6.1.x`) |
| **Architecture** | MVVM (strict separation of View / ViewModel / Model) |
| **Routing** | Centralized named routes (`AppRoutes`) |
| **Workspace Root** | `/Users/aliffandy/Documents/HolidayProject/BKJ-APP` |

### Key Dependencies
| Package | Version | Purpose |
|---------|---------|---------|
| `provider` | ^6.1.2 | State management (ChangeNotifier) |
| `intl` | ^0.19.0 | Date formatting (`AppFormatters`) |
| `file_picker` | ^8.1.2 | Document upload (Haulage, Cargo) |
| `uuid` | ^4.5.1 | Unique ID generation |
| `path` | ^1.9.0 | File path utilities |

---

## 2. Directory Structure

```
lib/
├── core/
│   ├── components/             ← Reusable UI components (barrel: components.dart)
│   │   ├── app_text_field.dart         AppTextField, AppPinField
│   │   ├── app_dropdown.dart           AppDropdown<T>, AppConditionalDropdown<T>, AppRadioGroup<T>
│   │   ├── app_date_picker.dart        AppDatePicker
│   │   ├── app_button.dart             AppButton (5 variants), AppIconButton
│   │   ├── app_file_upload_tile.dart   AppFileUploadTile
│   │   ├── container_list_builder.dart ContainerListBuilder (dynamic list, max 60)
│   │   ├── service_checkbox_tile.dart  ServiceCheckboxTile (expandable)
│   │   ├── form_step_indicator.dart    FormStepIndicator
│   │   ├── section_card.dart           SectionCard, FormPageWrapper, FormNavigationBar, FormInfoBanner
│   │   └── components.dart             ← BARREL EXPORT — always import this, not individual files
│   │
│   ├── routing/
│   │   └── app_routes.dart             ← ALL named routes defined here (never inline)
│   │
│   ├── theme/
│   │   └── app_theme.dart              AppColors, AppTextStyles, AppTheme.light
│   │
│   ├── utils/
│   │   ├── app_constants.dart          Business domain constants (wilayah, services, limits)
│   │   └── app_formatters.dart         AppFormatters (date/currency/initials), AppValidators
│   │
│   └── views/
│       └── main_shell.dart             Bottom navigation shell (IndexedStack: Home + Profile)
│
├── features/
│   ├── home/
│   │   ├── models/                     (no separate model — data lives in ViewModel)
│   │   ├── viewmodels/home_viewmodel.dart
│   │   └── views/home_screen.dart      ← FULLY implemented
│   │
│   ├── all_in/
│   │   ├── models/container_entry.dart  ContainerEntry (immutable, copyWith)
│   │   ├── viewmodels/all_in_viewmodel.dart
│   │   └── views/
│   │       ├── all_in_page1_screen.dart  ← STUB (implement in Step 4)
│   │       ├── all_in_page2_screen.dart  ← STUB
│   │       └── all_in_page3_screen.dart  ← STUB
│   │
│   ├── koperasi/
│   │   ├── viewmodels/koperasi_viewmodel.dart
│   │   └── views/
│   │       ├── koperasi_page1_screen.dart  ← STUB
│   │       ├── koperasi_page2_screen.dart  ← STUB
│   │       └── koperasi_page3_screen.dart  ← STUB
│   │
│   ├── pbm_lain/
│   │   ├── viewmodels/pbm_lain_viewmodel.dart
│   │   └── views/
│   │       ├── pbm_lain_page1_screen.dart  ← STUB
│   │       ├── pbm_lain_page2_screen.dart  ← STUB
│   │       └── pbm_lain_page3_screen.dart  ← STUB
│   │
│   └── profile/
│       ├── viewmodels/profile_viewmodel.dart
│       └── views/
│           ├── profile_screen.dart       ← STUB
│           ├── edit_profile_screen.dart  ← STUB
│           ├── change_password_screen.dart ← STUB
│           ├── change_pin_screen.dart    ← STUB
│           └── app_info_screen.dart      ← STUB
│
└── main.dart                            ← MultiProvider root + MaterialApp entry
```

---

## 3. Architecture Rules (STRICT — Never Violate)

### MVVM
- **Model**: Data-only. Immutable. Uses `copyWith`. Has `toJson()`. Example: `ContainerEntry`.
- **ViewModel**: `ChangeNotifier`. All business logic here. Exposes read-only getters + explicit setters. NEVER accesses Flutter widgets. NEVER uses `BuildContext`.
- **View**: Reads ViewModel via `context.watch<VM>()` or `context.read<VM>()`. NEVER contains business logic. Uses only pre-built components from `lib/core/components/`.

### DRY / Component-First
- **NEVER** use raw `TextField`, `ElevatedButton`, `DropdownButtonFormField` directly in feature screens.
- **ALWAYS** use components from `lib/core/components/components.dart`.
- All validators live in `AppValidators` — never write inline validation strings.
- All date/number formatting flows through `AppFormatters`.

### Routing
- All routes are string constants in `AppRoutes`.
- Navigate with `Navigator.pushNamed(context, AppRoutes.someRoute)`.
- **NEVER** use anonymous routes (`MaterialPageRoute(builder: ...)`) for main screens.

---

## 4. Design System

### Import
```dart
import 'package:bkj_app/core/theme/app_theme.dart';
```

### Color Palette (`AppColors`)
| Token | Hex | Usage |
|-------|-----|-------|
| `primary` | `#1A3C6E` | Main brand, app bar, buttons |
| `primaryLight` | `#2A5298` | Hover, active states |
| `primaryDark` | `#0D2247` | Gradient dark end |
| `accent` | `#0D7C66` | Secondary action, success indicators |
| `success` | `#27AE60` | Completed status |
| `warning` | `#F39C12` | In-progress, cap warnings |
| `error` | `#E74C3C` | Error states, delete actions |
| `info` | `#2980B9` | Pending status, info banners |
| `surface` | `#FFFFFF` | Card backgrounds |
| `background` | `#F4F6F9` | Scaffold background |
| `divider` | `#E0E6EF` | Borders, dividers |
| `textPrimary` | `#1C2833` | Main body text |
| `textSecondary` | `#5D6D7E` | Labels, subtitles |
| `textHint` | `#AEB6BF` | Placeholder text |

### Typography (`AppTextStyles`)
| Style | Size | Weight | Usage |
|-------|------|--------|-------|
| `heading1` | 24 | 700 | Page titles |
| `heading2` | 20 | 700 | Section headers |
| `heading3` | 16 | 600 | Card headers, field group labels |
| `body1` | 15 | 400 | Body content, form values |
| `body2` | 13 | 400 | Subtitles, descriptions |
| `label` | 13 | 600 | Form field labels |
| `caption` | 11 | 400 | Helper text, timestamps |
| `button` | 15 | 600 | Button labels |

---

## 5. Business Domain Constants (`AppConstants`)

All constants live in `lib/core/utils/app_constants.dart`. Never hardcode these values.

### Wilayah Operasional
| Feature | Available Wilayah |
|---------|-------------------|
| ALL IN | `Selatan`, `Eximen` |
| Koperasi | `Selatan`, `Eximen`, `Utara` |
| PBM Lain | `Selatan`, `Eximen` |

### Jenis Kegiatan (derived from Wilayah)
| Wilayah | Jenis Kegiatan |
|---------|----------------|
| Selatan | Bongkar, Muat, Stripping, Stuffing, Repair |
| Eximen | Bongkar Eximen, Muat Eximen |
| Utara | Bongkar Utara, Muat Utara |

> **Critical**: When user changes Wilayah, `jenisKegiatan` MUST be reset to `null`. This logic is in `setWilayah()` in each ViewModel.

### Payload Types (Page 2)
| Feature | Payload Options |
|---------|-----------------|
| ALL IN | Container OR Cargo (file upload) |
| Koperasi | Container OR Cargo (file upload) |
| PBM Lain | Container ONLY (no Cargo) |

### Container Constraints
- Max containers per order: **60** (`AppConstants.maxContainers`)
- Container types: `20' GP`, `40' GP`, `40' HC`, `20' RF`, `40' RF`, `20' OT`, `40' OT`, `20' FR`, `40' FR`, `20' TK`
- Container sizes: `20 Feet`, `40 Feet`

### Layanan / Services (Page 3)
| Service | ALL IN | Koperasi | PBM Lain | Sub-option |
|---------|--------|----------|----------|------------|
| Haulage | ✅ | ✅ | ❌ | File upload (SP2) |
| LOLO | ✅ | ✅ | ✅ | None |
| Penumpukan | ✅ | ✅ | ✅ | None |
| TKBM | ✅ | ✅ | ✅ | Radio: `Dalam Pelabuhan` / `Luar Pelabuhan` |
| Asuransi | ✅ | ✅ | ❌ | Numeric input (nilai asuransi) |

---

## 6. ViewModels Reference

### AllInViewModel (`lib/features/all_in/viewmodels/all_in_viewmodel.dart`)
**Manages**: 3-page ALL IN order form

| Property | Type | Description |
|----------|------|-------------|
| `wilayah` | `String?` | Selected wilayah (Selatan/Eximen) |
| `jenisKegiatan` | `String?` | Derived from wilayah |
| `availableJenisKegiatan` | `List<String>` | Computed from wilayah — use in dropdown |
| `tanggalKapal` | `DateTime?` | Ship date |
| `namaKapal` | `String?` | Ship name |
| `nomorVoyage` | `String?` | Voyage number |
| `payloadType` | `String` | `Container` or `Cargo` |
| `containers` | `List<ContainerEntry>` | Unmodifiable list |
| `canAddContainer` | `bool` | False when containers.length == 60 |
| `cargoFileName` | `String?` | Cargo file name |
| `selectedServices` | `Set<String>` | Selected service keys |
| `haulageFileName` | `String?` | Haulage document |
| `tkbmOption` | `String?` | TKBM radio selection |
| `asuransiValue` | `double` | Insurance value |
| `isSubmitting` | `bool` | Loading state during submit |

Key methods: `setWilayah()`, `setJenisKegiatan()`, `addContainer()`, `removeContainer()`, `updateContainer()`, `toggleService()`, `submitOrder()`, `resetForm()`

### KoperasiViewModel (`lib/features/koperasi/viewmodels/koperasi_viewmodel.dart`)
**Same as AllInViewModel plus:**
- `wilayah` includes `Utara`
- `namaPbm` (`String?`) — Manual PBM name input (Koperasi-specific)
- `setNamaPbm(String)` setter

### PbmLainViewModel (`lib/features/pbm_lain/viewmodels/pbm_lain_viewmodel.dart`)
**Streamlined version:**
- Page 2: Container ONLY (no `payloadType` toggle, no Cargo)
- Page 3: LOLO, Penumpukan, TKBM only (no Haulage, no Asuransi)
- Has `namaPbm` field for PBM name

### ProfileViewModel (`lib/features/profile/viewmodels/profile_viewmodel.dart`)
| Method | Description |
|--------|-------------|
| `updateProfile(fullName, email, phone)` | Updates user info |
| `changePassword(oldPassword, newPassword)` | Changes password |
| `changePin(oldPin, newPin)` | Changes 6-digit PIN |
| `logout()` | Clears state, triggers nav to login |

---

## 7. Component Usage Guide

### Always import via barrel:
```dart
import 'package:bkj_app/core/components/components.dart';
```

### AppTextField
```dart
AppTextField(
  label: 'Nama Kapal',
  hint: 'Masukkan nama kapal',
  controller: _namaKapalController,
  validator: (v) => AppValidators.required(v, fieldName: 'Nama Kapal'),
  onChanged: (v) => context.read<AllInViewModel>().setNamaKapal(v),
)
```

### AppDropdown (generic type-safe)
```dart
AppDropdown<String>(
  label: 'Wilayah Operasional',
  value: vm.wilayah,
  items: AppConstants.wilayahAllIn,
  itemLabel: (v) => v,
  onChanged: vm.setWilayah,
  validator: (v) => AppValidators.requiredDropdown(v, fieldName: 'Wilayah'),
)
```

### AppConditionalDropdown (dependent on another dropdown)
```dart
AppConditionalDropdown<String>(
  label: 'Jenis Kegiatan',
  value: vm.jenisKegiatan,
  items: vm.availableJenisKegiatan,  // empty list = auto-disabled
  itemLabel: (v) => v,
  onChanged: vm.setJenisKegiatan,
)
```

### AppDatePicker
```dart
AppDatePicker(
  label: 'Tanggal Kapal',
  selectedDate: vm.tanggalKapal,
  onDateSelected: vm.setTanggalKapal,
  validator: (d) => d == null ? 'Tanggal wajib diisi' : null,
)
```

### AppButton variants
```dart
AppButton(label: 'Submit', onPressed: _submit, isLoading: vm.isSubmitting)
AppButton(label: 'Kembali', onPressed: _back, variant: AppButtonVariant.outline)
AppButton(label: 'Hapus', onPressed: _delete, variant: AppButtonVariant.danger)
```

### AppFileUploadTile
```dart
AppFileUploadTile(
  label: 'Dokumen Haulage',
  hint: 'Upload SP2 (PDF/JPG)',
  fileName: vm.haulageFileName,
  allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
  onFileSelected: (name, path) => vm.setHaulageFile(name: name, path: path),
  onCleared: vm.clearHaulageFile,
)
```

### ContainerListBuilder
```dart
ContainerListBuilder(
  containers: vm.containers,
  canAdd: vm.canAddContainer,
  onAdd: vm.addContainer,
  onRemove: vm.removeContainer,
  onUpdate: vm.updateContainer,
)
```

### ServiceCheckboxTile (with conditional sub-content)
```dart
ServiceCheckboxTile(
  serviceKey: AppConstants.serviceHaulage,
  label: 'Haulage',
  description: 'Angkutan container dari/ke pelabuhan',
  icon: Icons.local_shipping_outlined,
  isSelected: vm.isServiceSelected(AppConstants.serviceHaulage),
  onToggle: () => vm.toggleService(AppConstants.serviceHaulage),
  expandedChild: AppFileUploadTile(
    label: 'Dokumen Haulage',
    fileName: vm.haulageFileName,
    onFileSelected: (n, p) => vm.setHaulageFile(name: n, path: p),
    onCleared: vm.clearHaulageFile,
  ),
)
```

### FormStepIndicator
```dart
FormStepIndicator(
  currentStep: 1,
  totalSteps: AppConstants.totalStepsAllIn,  // = 3
  stepLabel: 'Informasi Kapal',
)
```

### FormPageWrapper (full page layout)
```dart
Scaffold(
  appBar: AppBar(title: const Text('ALL IN')),
  body: FormPageWrapper(
    formKey: _formKey,
    children: [
      FormStepIndicator(currentStep: 1, totalSteps: 3),
      SectionCard(
        title: 'Wilayah & Kegiatan',
        icon: Icons.location_on_outlined,
        children: [ ... ],
      ),
    ],
    bottomBar: FormNavigationBar(
      onNext: _handleNext,
      isLoading: vm.isSubmitting,
    ),
  ),
)
```

### SectionCard
```dart
SectionCard(
  title: 'Informasi Kapal',
  icon: Icons.directions_boat_outlined,
  children: [
    AppTextField(label: 'Nama Kapal', ...),
    AppTextField(label: 'Nomor Voyage', ...),
    AppDatePicker(label: 'Tanggal Kapal', ...),
  ],
)
```

---

## 8. Centralized Routing

**File**: `lib/core/routing/app_routes.dart`

| Constant | Route | Screen |
|----------|-------|--------|
| `AppRoutes.shell` | `/` | `MainShell` (bottom nav) |
| `AppRoutes.allInPage1` | `/all-in/page-1` | `AllInPage1Screen` |
| `AppRoutes.allInPage2` | `/all-in/page-2` | `AllInPage2Screen` |
| `AppRoutes.allInPage3` | `/all-in/page-3` | `AllInPage3Screen` |
| `AppRoutes.koperasiPage1` | `/koperasi/page-1` | `KoperasiPage1Screen` |
| `AppRoutes.koperasiPage2` | `/koperasi/page-2` | `KoperasiPage2Screen` |
| `AppRoutes.koperasiPage3` | `/koperasi/page-3` | `KoperasiPage3Screen` |
| `AppRoutes.pbmLainPage1` | `/pbm-lain/page-1` | `PbmLainPage1Screen` |
| `AppRoutes.pbmLainPage2` | `/pbm-lain/page-2` | `PbmLainPage2Screen` |
| `AppRoutes.pbmLainPage3` | `/pbm-lain/page-3` | `PbmLainPage3Screen` |
| `AppRoutes.profile` | `/profile` | `ProfileScreen` |
| `AppRoutes.editProfile` | `/profile/edit` | `EditProfileScreen` |
| `AppRoutes.changePassword` | `/profile/change-password` | `ChangePasswordScreen` |
| `AppRoutes.changePin` | `/profile/change-pin` | `ChangePinScreen` |
| `AppRoutes.appInfo` | `/profile/app-info` | `AppInfoScreen` |

---

## 9. Build Execution Status

| Step | Status | Description |
|------|--------|-------------|
| Step 1 | ✅ DONE | Project scaffold, pubspec, theme, constants, formatters, routing, main.dart, main_shell.dart, all ViewModels, HomeScreen, stub screens |
| Step 2 | ✅ DONE | All 9 shared UI components in `lib/core/components/` |
| Step 3 | ✅ DONE | ViewModels (done early as part of Step 1) |
| Step 4 | 🔲 TODO | Feature Views: 3 ALL IN screens, 3 Koperasi screens, 3 PBM Lain screens, 5 Profile screens |

---

## 10. Coding Rules Checklist

Before writing any screen, verify:

- [ ] ViewModel is read via `context.watch<VM>()` (for reactive UI) or `context.read<VM>()` (for one-shot calls)
- [ ] All form fields use components from `components.dart` — no raw widgets
- [ ] Navigation uses `Navigator.pushNamed(context, AppRoutes.xxx)` 
- [ ] Form is wrapped in `FormPageWrapper` with a `GlobalKey<FormState>`
- [ ] Multi-step forms show `FormStepIndicator` at the top
- [ ] Related fields grouped in `SectionCard`
- [ ] Bottom nav uses `FormNavigationBar`
- [ ] All validation strings come from `AppValidators`
- [ ] All formatting comes from `AppFormatters`
- [ ] All domain values (wilayah, services, limits) come from `AppConstants`
- [ ] After successful submission, call `vm.resetForm()`

---

## 11. ContainerEntry Model

```dart
// lib/features/all_in/models/container_entry.dart
// Shared by ALL IN, Koperasi, and PBM Lain ViewModels

ContainerEntry({
  String? containerType,    // from AppConstants.containerTypes
  String? containerSize,    // from AppConstants.containerSizes
  String? containerNumber,  // free text (e.g. "ABCD 123456 7")
  int quantity = 1,
})
// Methods: copyWith(), isValid, toJson()
```

> **Note**: Koperasi and PBM Lain reuse `ContainerEntry` from the `all_in/models/` path. Do NOT duplicate the model.

---

## 12. Known Flutter Version Specifics (3.44.x)

- `DropdownButtonFormField`: Use `initialValue` + `ValueKey(value)` instead of deprecated `value` param
- Use `KeyboardListener` + `KeyEvent` / `KeyDownEvent` instead of deprecated `RawKeyboardListener`
- Use `?trailing` (null-aware spread) instead of `if (x != null) x!` in widget lists
- `withValues(alpha: 0.x)` instead of deprecated `withOpacity(0.x)` for color opacity
