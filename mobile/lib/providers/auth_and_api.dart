import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/network/api_client.dart';
import '../core/network/api_error.dart';
import '../core/storage/delivery_token_service.dart';
import '../core/storage/secure_token_storage.dart';
import '../core/storage/token_service.dart';
import '../models/customer.dart';

/// Bumped when the token is cleared remotely (401/403) so [authNotifierProvider] rebuilds.
final authSessionVersionProvider = StateProvider<int>((ref) => 0);

/// Bumped when the delivery token is cleared or replaced so [deliveryAuthNotifierProvider] rebuilds.
final deliverySessionVersionProvider = StateProvider<int>((ref) => 0);

final secureStorageProvider = Provider<SecureTokenStorage>(
  (ref) => SecureTokenStorage(),
);

/// Overridden in main() after [TokenService.init].
final tokenServiceProvider = Provider<TokenService>(
  (ref) => throw UnimplementedError('tokenServiceProvider not overridden'),
);

/// Overridden in main() after [DeliveryTokenService.init].
final deliveryTokenServiceProvider = Provider<DeliveryTokenService>(
  (ref) =>
      throw UnimplementedError('deliveryTokenServiceProvider not overridden'),
);

final apiClientProvider = Provider<ApiClient>((ref) {
  final tokens = ref.watch(tokenServiceProvider);
  return ApiClient(
    readToken: tokens.getToken,
    onAuthFailure: () {
      tokens.setToken(null);
      ref.read(authSessionVersionProvider.notifier).state++;
    },
  );
});

final authNotifierProvider =
    AsyncNotifierProvider<AuthNotifier, Customer?>(AuthNotifier.new);

Future<void> _clearDeliverySession(Ref ref) async {
  await ref.read(deliveryTokenServiceProvider).setToken(null);
  ref.read(deliverySessionVersionProvider.notifier).state++;
}

class AuthNotifier extends AsyncNotifier<Customer?> {
  @override
  Future<Customer?> build() async {
    ref.watch(authSessionVersionProvider);
    final tokens = ref.read(tokenServiceProvider);
    final has = await tokens.hasToken();
    if (!has) return null;
    try {
      final api = ref.read(apiClientProvider);
      final data = await api.get('/customer/me', auth: true);
      if (data is! Map) return null;
      return Customer.fromJson(Map<String, dynamic>.from(data));
    } catch (_) {
      await tokens.setToken(null);
      return null;
    }
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final tokens = ref.read(tokenServiceProvider);
      final has = await tokens.hasToken();
      if (!has) return null;
      final api = ref.read(apiClientProvider);
      final data = await api.get('/customer/me', auth: true);
      if (data is! Map) return null;
      return Customer.fromJson(Map<String, dynamic>.from(data));
    });
  }

  Future<void> login(String email, String password) async {
    final api = ref.read(apiClientProvider);
    final tokens = ref.read(tokenServiceProvider);
    final data = await api.post(
      '/auth/customer/login',
      {'email': email.trim(), 'password': password},
      auth: false,
    );
    if (data is! Map) {
      throw ApiException('Unexpected response');
    }
    final token = data['token'] as String?;
    if (token == null || token.isEmpty) {
      throw ApiException('Unexpected response');
    }
    await _clearDeliverySession(ref);
    await tokens.setToken(token);
    final cust = data['customer'];
    if (cust is Map) {
      state = AsyncData(Customer.fromJson(Map<String, dynamic>.from(cust)));
    } else {
      await refresh();
    }
  }

  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
    String? defaultShippingAddress,
  }) async {
    final api = ref.read(apiClientProvider);
    final tokens = ref.read(tokenServiceProvider);
    final body = <String, dynamic>{
      'name': name.trim(),
      'email': email.trim(),
      'password': password,
      'password_confirmation': passwordConfirmation,
    };
    if (phone != null && phone.trim().isNotEmpty) {
      body['phone'] = phone.trim();
    }
    if (defaultShippingAddress != null &&
        defaultShippingAddress.trim().isNotEmpty) {
      body['default_shipping_address'] = defaultShippingAddress.trim();
    }
    final data = await api.post('/auth/customer/register', body, auth: false);
    if (data is! Map) {
      throw ApiException('Unexpected response');
    }
    final token = data['token'] as String?;
    if (token == null || token.isEmpty) {
      throw ApiException('Unexpected response');
    }
    await _clearDeliverySession(ref);
    await tokens.setToken(token);
    final cust = data['customer'];
    if (cust is Map) {
      state = AsyncData(Customer.fromJson(Map<String, dynamic>.from(cust)));
    } else {
      await refresh();
    }
  }

  Future<void> logout() async {
    final api = ref.read(apiClientProvider);
    final tokens = ref.read(tokenServiceProvider);
    try {
      await api.post('/customer/logout', null, auth: true);
    } catch (_) {
      // ignore
    }
    await tokens.setToken(null);
    ref.read(authSessionVersionProvider.notifier).state++;
    state = const AsyncData(null);
  }

  Future<Map<String, dynamic>> resendVerification() async {
    final api = ref.read(apiClientProvider);
    final data = await api.post(
      '/customer/email/verification-notification',
      null,
      auth: true,
    );
    if (data is Map<String, dynamic>) return data;
    if (data is Map) return Map<String, dynamic>.from(data);
    return {};
  }
}
