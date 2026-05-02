import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/account/account_screen.dart';
import '../features/auth/login_screen.dart';
import '../features/auth/register_screen.dart';
import '../features/cart/cart_screen.dart';
import '../features/catalog/category_screen.dart';
import '../features/catalog/home_screen.dart';
import '../features/catalog/product_detail_screen.dart';
import '../features/catalog/search_screen.dart';
import '../features/checkout/checkout_screen.dart';
import '../features/checkout/checkout_success_screen.dart';
import '../features/delivery/delivery_login_screen.dart';
import '../features/delivery/delivery_order_detail_screen.dart';
import '../features/delivery/delivery_orders_screen.dart';
import '../features/shell/main_shell.dart';
import '../models/order.dart';
import '../providers/auth_and_api.dart';
import '../providers/delivery_auth_and_api.dart';
import '../features/orders/orders_screen.dart';
import '../features/orders/order_detail_screen.dart';
import 'go_router_refresh.dart';

final _rootNavigatorKey = GlobalKey<NavigatorState>(debugLabel: 'root');

final appRouterProvider = Provider<GoRouter>((ref) {
  final refresh = GoRouterRefresh(ref);
  ref.onDispose(refresh.dispose);

  return GoRouter(
    navigatorKey: _rootNavigatorKey,
    initialLocation: '/home',
    refreshListenable: refresh,
    redirect: (context, state) {
      final loc = state.matchedLocation;
      final auth = ref.read(authNotifierProvider);
      final deliveryAuth = ref.read(deliveryAuthNotifierProvider);

      if (auth.isLoading || deliveryAuth.isLoading) return null;

      final customer = auth.valueOrNull;
      final courier = deliveryAuth.valueOrNull;

      final loggingIn = loc == '/login' || loc == '/register';
      final shopperOrders = loc == '/orders' || loc.startsWith('/orders/');

      if (courier != null) {
        if (loc.startsWith('/delivery/orders')) return null;
        if (loc == '/delivery/login') return '/delivery/orders';
        return '/delivery/orders';
      }

      if (loc.startsWith('/delivery/orders')) {
        return '/delivery/login';
      }

      if (customer == null && shopperOrders) {
        return '/login?redirect=${Uri.encodeComponent(loc)}';
      }

      if (loggingIn && customer != null) {
        final redir = state.uri.queryParameters['redirect'];
        if (redir != null &&
            redir.startsWith('/') &&
            !redir.startsWith('//')) {
          return redir;
        }
        return '/home';
      }

      return null;
    },
    routes: [
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) {
          return MainShell(navigationShell: navigationShell);
        },
        branches: [
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/home',
                builder: (context, state) => const HomeScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/search',
                builder: (context, state) {
                  final q = state.uri.queryParameters['q'];
                  return SearchScreen(initialQuery: q);
                },
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/cart',
                builder: (context, state) => const CartScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: '/account',
                builder: (context, state) => const AccountScreen(),
              ),
            ],
          ),
        ],
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/login',
        builder: (context, state) {
          final redirect = state.uri.queryParameters['redirect'];
          return LoginScreen(redirectPath: redirect);
        },
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/register',
        builder: (context, state) {
          final redirect = state.uri.queryParameters['redirect'];
          return RegisterScreen(redirectPath: redirect);
        },
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/category/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return CategoryScreen(categoryId: id);
        },
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/product/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return ProductDetailScreen(productId: id);
        },
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/checkout',
        builder: (context, state) => const CheckoutScreen(),
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/checkout/success',
        builder: (context, state) {
          final extra = state.extra;
          if (extra is Order) {
            return CheckoutSuccessScreen(order: extra);
          }
          return const Scaffold(
            body: Center(child: Text('Missing order')),
          );
        },
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/orders',
        builder: (context, state) => const OrdersScreen(),
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/delivery/login',
        builder: (context, state) => const DeliveryLoginScreen(),
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/delivery/orders',
        builder: (context, state) => const DeliveryOrdersScreen(),
      ),
      GoRoute(
        parentNavigatorKey: _rootNavigatorKey,
        path: '/delivery/orders/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return DeliveryOrderDetailScreen(orderId: id);
        },
      ),
    ],
  );
});
