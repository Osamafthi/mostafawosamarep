import 'package:flutter_secure_storage/flutter_secure_storage.dart';

const _kCustomerToken = 'customerToken';
const _kDeliveryToken = 'deliveryToken';

class SecureTokenStorage {
  SecureTokenStorage({FlutterSecureStorage? storage})
      : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  Future<String?> readToken() => _storage.read(key: _kCustomerToken);

  Future<void> writeToken(String token) =>
      _storage.write(key: _kCustomerToken, value: token);

  Future<void> clearToken() => _storage.delete(key: _kCustomerToken);

  Future<String?> readDeliveryToken() => _storage.read(key: _kDeliveryToken);

  Future<void> writeDeliveryToken(String token) =>
      _storage.write(key: _kDeliveryToken, value: token);

  Future<void> clearDeliveryToken() => _storage.delete(key: _kDeliveryToken);
}
