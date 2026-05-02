import 'secure_token_storage.dart';

/// In-memory cache + secure persistence for the delivery Sanctum bearer token.
class DeliveryTokenService {
  DeliveryTokenService(this._storage);

  final SecureTokenStorage _storage;
  String? _cached;

  Future<void> init() async {
    _cached = await _storage.readDeliveryToken();
  }

  Future<String?> getToken() async {
    _cached ??= await _storage.readDeliveryToken();
    return _cached;
  }

  Future<void> setToken(String? token) async {
    if (token == null || token.isEmpty) {
      _cached = null;
      await _storage.clearDeliveryToken();
      return;
    }
    _cached = token;
    await _storage.writeDeliveryToken(token);
  }

  Future<bool> hasToken() async {
    final t = await getToken();
    return t != null && t.isNotEmpty;
  }
}
