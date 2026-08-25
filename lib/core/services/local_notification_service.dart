import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';

class LocalNotificationService {
  LocalNotificationService._();
  static final LocalNotificationService instance = LocalNotificationService._();

  final FlutterLocalNotificationsPlugin _plugin = FlutterLocalNotificationsPlugin();
  bool _initialized = false;
  int _idCounter = 1000;

  Future<void> init() async {
    if (_initialized) return;

    const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
    const iosInit = DarwinInitializationSettings(
      requestAlertPermission: false,
      requestBadgePermission: false,
      requestSoundPermission: false,
    );
    const settings = InitializationSettings(android: androidInit, iOS: iosInit);

    try {
      await _plugin.initialize(settings);
    } catch (_) {}
    _initialized = true;
  }

  Future<void> requestPermissionsIfNeeded() async {
    try {
      final androidPerm = await Permission.notification.status;
      if (!androidPerm.isGranted) {
        await Permission.notification.request();
      }
    } catch (_) {}
    try {
      await _plugin
          .resolvePlatformSpecificImplementation<IOSFlutterLocalNotificationsPlugin>()
          ?.requestPermissions(alert: true, badge: true, sound: true);
    } catch (_) {}
  }

  Future<void> show({
    required String title,
    required String body,
    String? payload,
  }) async {
    if (!_initialized) await init();

    final id = _idCounter++;
    const android = AndroidNotificationDetails(
      'bkj_notifications_channel',
      'Notifikasi BKJ',
      channelDescription: 'Notifikasi order masuk & update aktivitas lapangan BKJ.',
      importance: Importance.max,
      priority: Priority.high,
      showWhen: true,
      enableVibration: true,
      icon: '@mipmap/ic_launcher',
    );
    const ios = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );
    const details = NotificationDetails(android: android, iOS: ios);

    try {
      await _plugin.show(id, title, body, details, payload: payload);
    } catch (_) {}
  }
}
