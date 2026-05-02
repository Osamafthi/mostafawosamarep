import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app.dart';
import 'core/storage/delivery_token_service.dart';
import 'core/storage/secure_token_storage.dart';
import 'core/storage/token_service.dart';
import 'providers/auth_and_api.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final storage = SecureTokenStorage();
  final tokens = TokenService(storage);
  final deliveryTokens = DeliveryTokenService(storage);
  await tokens.init();
  await deliveryTokens.init();

  runApp(
    ProviderScope(
      overrides: [
        tokenServiceProvider.overrideWithValue(tokens),
        deliveryTokenServiceProvider.overrideWithValue(deliveryTokens),
      ],
      child: const StoreApp(),
    ),
  );
}
