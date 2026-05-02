import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../providers/auth_and_api.dart';
import '../providers/delivery_auth_and_api.dart';

/// Notifies [GoRouter] when auth/session changes so redirects re-run.
class GoRouterRefresh extends ChangeNotifier {
  GoRouterRefresh(this._ref) {
    _ref.listen(authNotifierProvider, (_, __) => notifyListeners());
    _ref.listen(authSessionVersionProvider, (_, __) => notifyListeners());
    _ref.listen(deliveryAuthNotifierProvider, (_, __) => notifyListeners());
    _ref.listen(deliverySessionVersionProvider, (_, __) => notifyListeners());
  }

  final Ref _ref;
}
