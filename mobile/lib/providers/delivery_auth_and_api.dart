import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/network/api_client.dart';
import '../core/network/api_error.dart';
import '../models/delivery_person.dart';
import 'auth_and_api.dart';

final deliveryApiClientProvider = Provider<ApiClient>((ref) {
  final tokens = ref.watch(deliveryTokenServiceProvider);
  return ApiClient(
    readToken: tokens.getToken,
    onAuthFailure: () {
      tokens.setToken(null);
      ref.read(deliverySessionVersionProvider.notifier).state++;
    },
  );
});

final deliveryAuthNotifierProvider =
    AsyncNotifierProvider<DeliveryAuthNotifier, DeliveryPersonProfile?>(
        DeliveryAuthNotifier.new);

Future<void> _clearCustomerSession(Ref ref) async {
  await ref.read(tokenServiceProvider).setToken(null);
  ref.read(authSessionVersionProvider.notifier).state++;
}

class DeliveryAuthNotifier extends AsyncNotifier<DeliveryPersonProfile?> {
  @override
  Future<DeliveryPersonProfile?> build() async {
    ref.watch(deliverySessionVersionProvider);
    final tokens = ref.read(deliveryTokenServiceProvider);
    final has = await tokens.hasToken();
    if (!has) return null;
    try {
      final api = ref.read(deliveryApiClientProvider);
      final data = await api.get('/delivery/me', auth: true);
      if (data is! Map) return null;
      return DeliveryPersonProfile.fromJson(Map<String, dynamic>.from(data));
    } catch (_) {
      await tokens.setToken(null);
      return null;
    }
  }

  Future<void> refresh() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final tokens = ref.read(deliveryTokenServiceProvider);
      final has = await tokens.hasToken();
      if (!has) return null;
      final api = ref.read(deliveryApiClientProvider);
      final data = await api.get('/delivery/me', auth: true);
      if (data is! Map) return null;
      return DeliveryPersonProfile.fromJson(Map<String, dynamic>.from(data));
    });
  }

  Future<void> login(String email, String password) async {
    final api = ref.read(deliveryApiClientProvider);
    final tokens = ref.read(deliveryTokenServiceProvider);
    final data = await api.post(
      '/auth/delivery/login',
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
    await _clearCustomerSession(ref);
    await tokens.setToken(token);
    final raw = data['delivery_person'];
    if (raw is Map) {
      state = AsyncData(
        DeliveryPersonProfile.fromJson(Map<String, dynamic>.from(raw)),
      );
    } else {
      await refresh();
    }
  }

  Future<void> logout() async {
    final api = ref.read(deliveryApiClientProvider);
    final tokens = ref.read(deliveryTokenServiceProvider);
    try {
      await api.post('/delivery/logout', null, auth: true);
    } catch (_) {
      // ignore
    }
    await tokens.setToken(null);
    ref.read(deliverySessionVersionProvider.notifier).state++;
    state = const AsyncData(null);
  }
}
