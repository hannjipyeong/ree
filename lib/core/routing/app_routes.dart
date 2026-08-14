import 'package:flutter/material.dart';

import 'package:bkj_app/features/auth/views/login_screen.dart';
import 'package:bkj_app/features/auth/views/register_screen.dart';
import 'package:bkj_app/features/supir/views/supir_action_screen.dart';


import 'package:bkj_app/features/all_in/views/all_in_page1_screen.dart';
import 'package:bkj_app/features/all_in/views/all_in_page2_screen.dart';
import 'package:bkj_app/features/all_in/views/all_in_page3_screen.dart';
import 'package:bkj_app/features/koperasi/views/koperasi_page1_screen.dart';
import 'package:bkj_app/features/koperasi/views/koperasi_page2_screen.dart';
import 'package:bkj_app/features/koperasi/views/koperasi_page3_screen.dart';
import 'package:bkj_app/features/pbm_lain/views/pbm_lain_page1_screen.dart';
import 'package:bkj_app/features/pbm_lain/views/pbm_lain_page2_screen.dart';
import 'package:bkj_app/features/pbm_lain/views/pbm_lain_page3_screen.dart';
import 'package:bkj_app/features/profile/views/profile_screen.dart';
import 'package:bkj_app/features/profile/views/edit_profile_screen.dart';
import 'package:bkj_app/features/profile/views/change_password_screen.dart';
import 'package:bkj_app/features/profile/views/change_pin_screen.dart';
import 'package:bkj_app/features/profile/views/app_info_screen.dart';
import 'package:bkj_app/core/views/main_shell.dart';

/// Central route registry. All named routes are defined here.
/// Never define routes inline in [MaterialApp] outside of this file.
class AppRoutes {
  AppRoutes._();

  // ─── Auth ────────────────────────────────────────────────────────────────────
  static const String login = '/login';
  static const String register = '/register';

  // ─── Supir ───────────────────────────────────────────────────────────────────
  static const String supirAction = '/supir/action';

  // ─── Root ────────────────────────────────────────────────────────────────────
  static const String shell = '/shell';

  // ─── Feature: Home ───────────────────────────────────────────────────────────
  static const String home = '/home';

  // ─── Feature: All In ────────────────────────────────────────────────────────
  static const String allInPage1 = '/all-in/page-1';
  static const String allInPage2 = '/all-in/page-2';
  static const String allInPage3 = '/all-in/page-3';

  // ─── Feature: Koperasi ──────────────────────────────────────────────────────
  static const String koperasiPage1 = '/koperasi/page-1';
  static const String koperasiPage2 = '/koperasi/page-2';
  static const String koperasiPage3 = '/koperasi/page-3';

  // ─── Feature: PBM Lain ──────────────────────────────────────────────────────
  static const String pbmLainPage1 = '/pbm-lain/page-1';
  static const String pbmLainPage2 = '/pbm-lain/page-2';
  static const String pbmLainPage3 = '/pbm-lain/page-3';

  // ─── Feature: Profile ───────────────────────────────────────────────────────
  static const String profile = '/profile';
  static const String editProfile = '/profile/edit';
  static const String changePassword = '/profile/change-password';
  static const String changePin = '/profile/change-pin';
  static const String appInfo = '/profile/app-info';

  /// Registers all routes. Called once in [MaterialApp].
  static Map<String, WidgetBuilder> get routes => {
    login: (_) => const LoginScreen(),
    register: (_) => const RegisterScreen(),
    supirAction: (_) => const SupirActionScreen(),
    shell: (_) => const MainShell(),
    allInPage1: (_) => const AllInPage1Screen(),
    allInPage2: (_) => const AllInPage2Screen(),
    allInPage3: (_) => const AllInPage3Screen(),
    koperasiPage1: (_) => const KoperasiPage1Screen(),
    koperasiPage2: (_) => const KoperasiPage2Screen(),
    koperasiPage3: (_) => const KoperasiPage3Screen(),
    pbmLainPage1: (_) => const PbmLainPage1Screen(),
    pbmLainPage2: (_) => const PbmLainPage2Screen(),
    pbmLainPage3: (_) => const PbmLainPage3Screen(),
    profile: (_) => const ProfileScreen(),
    editProfile: (_) => const EditProfileScreen(),
    changePassword: (_) => const ChangePasswordScreen(),
    changePin: (_) => const ChangePinScreen(),
    appInfo: (_) => const AppInfoScreen(),
  };
}
