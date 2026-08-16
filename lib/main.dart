import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/routing/app_routes.dart';
import 'package:bkj_app/features/all_in/viewmodels/all_in_viewmodel.dart';
import 'package:bkj_app/features/koperasi/viewmodels/koperasi_viewmodel.dart';
import 'package:bkj_app/features/pbm_lain/viewmodels/pbm_lain_viewmodel.dart';
import 'package:bkj_app/features/profile/viewmodels/profile_viewmodel.dart';
import 'package:bkj_app/features/home/viewmodels/home_viewmodel.dart';
import 'package:bkj_app/features/auth/viewmodels/auth_viewmodel.dart';
import 'package:bkj_app/features/supir/viewmodels/supir_viewmodel.dart';

final GlobalKey<ScaffoldMessengerState> rootScaffoldMessengerKey = GlobalKey<ScaffoldMessengerState>();

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('id_ID', null);

  // Lock the app to portrait orientation for a consistent mobile experience.
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  // Set the status bar style to match the app's dark primary color.
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.light,
    ),
  );

  runApp(const BkjApp());
}

class BkjApp extends StatelessWidget {
  const BkjApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        // Each ViewModel is registered at the app root so it persists
        // across navigation and is accessible via context.read/watch.
        ChangeNotifierProvider(create: (_) => AuthViewModel()),
        ChangeNotifierProvider(create: (_) => SupirViewModel()),
        ChangeNotifierProvider(create: (_) => HomeViewModel()),
        ChangeNotifierProvider(create: (_) => AllInViewModel()),
        ChangeNotifierProvider(create: (_) => KoperasiViewModel()),
        ChangeNotifierProvider(create: (_) => PbmLainViewModel()),
        ChangeNotifierProvider(create: (_) => ProfileViewModel()),
      ],
      child: MaterialApp(
        title: 'BKJ App',
        scaffoldMessengerKey: rootScaffoldMessengerKey,
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        initialRoute: AppRoutes.login,
        routes: AppRoutes.routes,
      ),
    );
  }
}
