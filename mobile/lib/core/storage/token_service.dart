import 'secure_token_storage.dart';

/// In-memory cache + secure persistence for the Sanctum bearer token.
class TokenService {
  TokenService(this._storage);

  final SecureTokenStorage _storage;
  String? _cached;

  Future<void> init() async {
    _cached = await _storage.readToken();
  }

  Future<String?> getToken() async {
    _cached ??= await _storage.readToken();
    return _cached;
  }

  Future<void> setToken(String? token) async {
    if (token == null || token.isEmpty) {
      _cached = null;
      await _storage.clearToken();
      return;
    }
    _cached = token;
    await _storage.writeToken(token);
  }

  Future<bool> hasToken() async {
    final t = await getToken();
    return t != null && t.isNotEmpty;
  }
}
