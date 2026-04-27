import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

/// Mirrors web `customerCart` localStorage key and shape.
const _kCart = 'customerCart';

class CartStorage {
  Future<List<Map<String, dynamic>>> loadRaw() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kCart);
    if (raw == null || raw.isEmpty) return [];
    try {
      final decoded = jsonDecode(raw);
      if (decoded is! List) return [];
      return decoded.map((e) => Map<String, dynamic>.from(e as Map)).toList();
    } catch (_) {
      return [];
    }
  }

  Future<void> saveRaw(List<Map<String, dynamic>> items) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kCart, jsonEncode(items));
  }
}
