import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bkj_app/core/theme/app_theme.dart';
import 'package:bkj_app/core/utils/app_formatters.dart';
import 'package:bkj_app/features/home/models/app_notification.dart';
import 'package:bkj_app/features/home/viewmodels/notification_viewmodel.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationViewModel>().loadNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    final vm = context.watch<NotificationViewModel>();

    return Scaffold(
      backgroundColor: const Color(0xFFF4F6FB),
      appBar: AppBar(
        title: const Text('Notifikasi'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        actions: [
          if (vm.unreadCount > 0)
            TextButton.icon(
              onPressed: () => vm.markAllRead(),
              icon: const Icon(Icons.done_all, size: 18, color: Colors.white),
              label: const Text(
                'Tandai Baca',
                style: TextStyle(color: Colors.white, fontSize: 12),
              ),
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: vm.isLoading && vm.items.isEmpty
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () => vm.loadNotifications(),
              child: vm.items.isEmpty
                  ? _buildEmptyState(context)
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      itemCount: vm.items.length,
                      itemBuilder: (ctx, i) => _NotifTile(notif: vm.items[i]),
                    ),
            ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        SizedBox(height: MediaQuery.of(context).size.height * 0.22),
        const Icon(Icons.notifications_none, size: 80, color: AppColors.disabled),
        const SizedBox(height: 16),
        Text(
          'Belum ada notifikasi',
          textAlign: TextAlign.center,
          style: AppTextStyles.heading3.copyWith(color: AppColors.textSecondary),
        ),
        const SizedBox(height: 6),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 48),
          child: Text(
            'Aktivitas baru seperti order masuk, bukti IN/OUT dari lapangan, atau update status akan muncul disini.',
            textAlign: TextAlign.center,
            style: AppTextStyles.body2.copyWith(color: AppColors.textHint),
          ),
        ),
      ],
    );
  }
}

class _NotifTile extends StatelessWidget {
  final AppNotification notif;

  const _NotifTile({required this.notif});

  @override
  Widget build(BuildContext context) {
    final style = _styleFor(notif.type, notif.category);
    final isUnread = !notif.isRead;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isUnread
              ? const Color(0xFFE0E7FF)
              : const Color(0xFFE5E7EB),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E3A8A).withValues(alpha: 0.04),
            blurRadius: 14,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 52,
              height: 52,
              decoration: BoxDecoration(
                color: const Color(0xFFEEF2FF),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Icon(
                style.icon,
                size: 28,
                color: const Color(0xFF1E40AF),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Text(
                          notif.title,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF0F172A),
                            height: 1.25,
                          ),
                        ),
                      ),
                      if (isUnread)
                        Container(
                          margin: const EdgeInsets.only(left: 10, top: 6),
                          width: 12,
                          height: 12,
                          decoration: BoxDecoration(
                            color: const Color(0xFF1E40AF),
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    notif.message,
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 14.5,
                      fontWeight: FontWeight.w500,
                      color: const Color(0xFF475569).withValues(alpha: 0.92),
                      height: 1.45,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      if (style.chipLabel != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: style.chipBg,
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            style.chipLabel!,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: style.chipColor,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ),
                      if (notif.serviceType != null && notif.serviceType!.isNotEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: const Color(0xFFDBEAFE),
                            borderRadius: BorderRadius.circular(999),
                            border: Border.all(
                              color: const Color(0xFFBFDBFE).withValues(alpha: 0.9),
                              width: 1,
                            ),
                          ),
                          child: Text(
                            notif.serviceType!,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1D4ED8),
                            ),
                          ),
                        ),
                      if (notif.orderNumber != null && notif.orderNumber!.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(left: 2),
                          child: Text(
                            notif.orderNumber!,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF94A3B8).withValues(alpha: 0.95),
                              letterSpacing: 0.15,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Text(
                    AppFormatters.toRelativeTime(notif.time),
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: const Color(0xFF94A3B8).withValues(alpha: 0.95),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  _NotifStyle _styleFor(String? type, String? category) {
    final t = (type ?? '').toUpperCase();
    final c = (category ?? '').toLowerCase();

    if (t == 'NEW' || c == 'new_task') {
      return const _NotifStyle(
        icon: Icons.assignment_turned_in_outlined,
        chipLabel: 'BARU',
        chipColor: Color(0xFF1E40AF),
        chipBg: Color(0xFFE0E7FF),
      );
    }
    if (t == 'ORDER' || c == 'order_status') {
      return const _NotifStyle(
        icon: Icons.receipt_long_outlined,
        chipLabel: 'ORDER',
        chipColor: Color(0xFF0F766E),
        chipBg: Color(0xFFCCFBF1),
      );
    }
    if (t == 'OUT') {
      return const _NotifStyle(
        icon: Icons.outbox_outlined,
        chipLabel: 'OUT',
        chipColor: Color(0xFF166534),
        chipBg: Color(0xFFBBF7D0),
      );
    }
    return const _NotifStyle(
      icon: Icons.inbox_outlined,
      chipLabel: 'IN',
      chipColor: Color(0xFF1E40AF),
      chipBg: Color(0xFFDBEAFE),
    );
  }
}

class _NotifStyle {
  final IconData icon;
  final String? chipLabel;
  final Color chipColor;
  final Color chipBg;

  const _NotifStyle({
    required this.icon,
    required this.chipLabel,
    required this.chipColor,
    required this.chipBg,
  });
}
