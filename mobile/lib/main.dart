import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app.dart';
import 'core/storage/secure_token_storage.dart';
import 'core/storage/token_service.dart';
import 'providers/auth_and_api.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final storage = SecureTokenStorage();
  final tokens = TokenService(storage);
  await tokens.init();

  runApp(
    ProviderScope(
      overrides: [
        tokenServiceProvider.overrideWithValue(tokens),
      ],
      child: const StoreApp(),
    ),
  );
}
