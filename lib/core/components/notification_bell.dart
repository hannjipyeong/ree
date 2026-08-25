import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/features/home/viewmodels/notification_viewmodel.dart';
import 'package:bkj_app/features/home/views/notifications_screen.dart';

class AppNotificationBell extends StatelessWidget {
  final double iconSize;
  final Color iconColor;
  final bool showBadgeBorder;

  const AppNotificationBell({
    super.key,
    this.iconSize = 26,
    this.iconColor = Colors.white,
    this.showBadgeBorder = true,
  });

  @override
  Widget build(BuildContext context) {
    final unread = context.watch<NotificationViewModel>().unreadCount;
    return InkWell(
      borderRadius: BorderRadius.circular(20),
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const NotificationsScreen()),
        );
      },
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Icon(
              Icons.notifications_outlined,
              color: iconColor,
              size: iconSize,
            ),
            if (unread > 0)
              Positioned(
                right: -6,
                top: -6,
                child: Container(
                  constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: BoxDecoration(
                    color: AppColors.error,
                    shape: BoxShape.circle,
                    border: showBadgeBorder
                        ? Border.all(color: Colors.white, width: 1.5)
                        : null,
                  ),
                  child: Center(
                    child: Text(
                      unread > 99 ? '99+' : '$unread',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        height: 1.0,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
