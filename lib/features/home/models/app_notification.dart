import 'package:flutter/foundation.dart';

class AppNotification {
  final String id;
  final String category;
  final String type;
  final DateTime time;
  final bool isRead;
  final String title;
  final String message;
  final String? photo;
  final String? serviceType;
  final String? containerNum;
  final int? orderId;
  final String? orderNumber;
  final String? namaPt;
  final String? source;

  AppNotification({
    required this.id,
    required this.category,
    required this.type,
    required this.time,
    required this.isRead,
    required this.title,
    required this.message,
    this.photo,
    this.serviceType,
    this.containerNum,
    this.orderId,
    this.orderNumber,
    this.namaPt,
    this.source,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    DateTime? parsedTime;
    try {
      parsedTime = DateTime.tryParse(json['time']?.toString() ?? '');
    } catch (_) {}
    parsedTime ??= DateTime.now();

    return AppNotification(
      id: json['id']?.toString() ?? UniqueKey().toString(),
      category: json['category']?.toString() ?? 'progress',
      type: json['type']?.toString() ?? 'IN',
      time: parsedTime,
      isRead: json['is_read'] == true || json['isRead'] == true,
      title: json['title']?.toString() ?? 'Notifikasi',
      message: json['message']?.toString() ?? '',
      photo: json['photo']?.toString(),
      serviceType: json['service_type']?.toString(),
      containerNum: json['container_num']?.toString(),
      orderId: int.tryParse(json['order_id']?.toString() ?? ''),
      orderNumber: json['order_number']?.toString(),
      namaPt: json['nama_pt']?.toString(),
      source: json['source']?.toString(),
    );
  }
}
