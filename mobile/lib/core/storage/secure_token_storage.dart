import 'package:flutter_secure_storage/flutter_secure_storage.dart';

const _kCustomerToken = 'customerToken';

class SecureTokenStorage {
  SecureTokenStorage({FlutterSecureStorage? storage})
      : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  Future<String?> readToken() => _storage.read(key: _kCustomerToken);

  Future<void> writeToken(String token) =>
      _storage.write(key: _kCustomerToken, value: token);

  Future<void> clearToken() => _storage.delete(key: _kCustomerToken);
}
