import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:bkj_app/core/services/api_service.dart';
import 'package:bkj_app/core/services/local_notification_service.dart';
import 'package:bkj_app/features/home/models/app_notification.dart';

class NotificationViewModel extends ChangeNotifier {
  bool _isLoading = false;
  String? _errorMessage;
  List<AppNotification> _items = [];
  int _unreadCount = 0;
  Timer? _pollingTimer;
  final Set<String> _seenNotifIds = {};
  bool _bootstrapped = false;
  bool _permissionsRequested = false;

  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  List<AppNotification> get items => _items;
  int get unreadCount => _unreadCount;

  Future<void> _ensurePermissions() async {
    if (_permissionsRequested || kIsWeb) return;
    _permissionsRequested = true;
    await LocalNotificationService.instance.init();
    await LocalNotificationService.instance.requestPermissionsIfNeeded();
  }

  void startPolling({Duration interval = const Duration(seconds: 15)}) {
    stopPolling();
    _pollingTimer = Timer.periodic(interval, (_) {
      loadSummary(silent: true);
    });
  }

  void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }

  Future<void> loadSummary({bool silent = false}) async {
    try {
      final res = await ApiService.getNotificationSummary();
      if (res != null) {
        final newUnread = (res['unread'] as num?)?.toInt() ?? 0;
        final changed = newUnread != _unreadCount;
        _unreadCount = newUnread;
        if (newUnread > 0) {
          await loadNotifications(silent: true, onlyPushWhenNew: true);
        } else if (changed) {
          notifyListeners();
        }
      }
    } catch (e) {
      if (!silent) {
        debugPrint('Notification summary error: $e');
      }
    }
  }

  Future<void> loadNotifications({
    bool silent = false,
    bool onlyPushWhenNew = false,
  }) async {
    if (!silent) {
      _isLoading = true;
      notifyListeners();
    }

    try {
      await _ensurePermissions();
      final res = await ApiService.getNotifications();
      if (res != null) {
        final raw = res['data'] as List? ?? [];
        final fetched = raw
            .map((e) => AppNotification.fromJson(e as Map<String, dynamic>))
            .toList();
        _items = fetched;
        _unreadCount = (res['unread'] as num?)?.toInt() ?? 0;
        _errorMessage = null;

        if (_bootstrapped && !kIsWeb) {
          final fresh = fetched.where((n) {
            if (n.isRead) return false;
            if (_seenNotifIds.contains(n.id)) return false;
            return true;
          }).toList();
          for (int i = fresh.length - 1; i >= 0; i--) {
            final n = fresh[i];
            await LocalNotificationService.instance.show(
              title: n.title,
              body: n.message,
              payload: n.id,
            );
          }
        }
        for (final n in fetched) {
          _seenNotifIds.add(n.id);
        }
        if (!_bootstrapped) _bootstrapped = true;
      } else {
        if (!silent) _errorMessage = 'Gagal memuat notifikasi.';
      }
    } catch (e) {
      if (!silent) _errorMessage = 'Gagal memuat notifikasi: $e';
      debugPrint('loadNotifications error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> markAllRead() async {
    try {
      await ApiService.markNotificationsRead();
      for (int i = 0; i < _items.length; i++) {
        if (!_items[i].isRead) {
          _items[i] = AppNotification(
            id: _items[i].id,
            category: _items[i].category,
            type: _items[i].type,
            time: _items[i].time,
            isRead: true,
            title: _items[i].title,
            message: _items[i].message,
            photo: _items[i].photo,
            serviceType: _items[i].serviceType,
            containerNum: _items[i].containerNum,
            orderId: _items[i].orderId,
            orderNumber: _items[i].orderNumber,
            namaPt: _items[i].namaPt,
            source: _items[i].source,
          );
        }
      }
      _unreadCount = 0;
      notifyListeners();
    } catch (e) {
      debugPrint('markAllRead error: $e');
    }
  }
}
