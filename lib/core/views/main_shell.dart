import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/features/home/views/home_screen.dart';
import 'package:bkj_app/features/all_in/views/all_in_page1_screen.dart';
import 'package:bkj_app/features/koperasi/views/koperasi_page1_screen.dart';
import 'package:bkj_app/features/pbm_lain/views/pbm_lain_page1_screen.dart';
import 'package:bkj_app/features/profile/views/profile_screen.dart';

/// The persistent shell that hosts the bottom navigation bar and
/// manages tab switching. Only one instance is ever on the navigator stack.
class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  int _currentIndex = 0;

  // Tab bodies — lazily constructed and kept alive via [IndexedStack].
  static const List<Widget> _tabs = [
    HomeScreen(),
    AllInPage1Screen(),
    KoperasiPage1Screen(),
    PbmLainPage1Screen(),
    ProfileScreen(),
  ];

  static const List<BottomNavigationBarItem> _navItems = [
    BottomNavigationBarItem(
      icon: Icon(Icons.home_outlined),
      activeIcon: Icon(Icons.home),
      label: 'Beranda',
    ),
    BottomNavigationBarItem(
      icon: Icon(Icons.grid_view_outlined),
      activeIcon: Icon(Icons.grid_view_rounded),
      label: 'ALL IN',
    ),
    BottomNavigationBarItem(
      icon: Icon(Icons.handshake_outlined),
      activeIcon: Icon(Icons.handshake),
      label: 'Koperasi',
    ),
    BottomNavigationBarItem(
      icon: Icon(Icons.business_center_outlined),
      activeIcon: Icon(Icons.business_center),
      label: 'PBM Lain',
    ),
    BottomNavigationBarItem(
      icon: Icon(Icons.person_outlined),
      activeIcon: Icon(Icons.person),
      label: 'Profil',
    ),
  ];

  void _onTabTapped(int index) => setState(() => _currentIndex = index);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _tabs,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withValues(alpha: 0.08),
              blurRadius: 16,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: _onTabTapped,
          type: BottomNavigationBarType.fixed, // Required for >3 items
          items: _navItems,
        ),
      ),
    );
  }
}
