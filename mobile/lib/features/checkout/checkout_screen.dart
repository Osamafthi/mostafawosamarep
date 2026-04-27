import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';

import '../../core/format/money.dart';
import '../../core/network/api_error.dart';
import '../../models/customer.dart';
import '../../models/order.dart';
import '../../providers/auth_and_api.dart';
import '../../providers/cart_notifier.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _address = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _loadingPrefill = false;
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _prefill();
  }

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _address.dispose();
    super.dispose();
  }

  Future<void> _prefill() async {
    final token = await ref.read(tokenServiceProvider).hasToken();
    if (!token) return;
    setState(() => _loadingPrefill = true);
    try {
      final api = ref.read(apiClientProvider);
      final data = await api.get('/customer/me', auth: true);
      if (data is Map && mounted) {
        final c = Customer.fromJson(Map<String, dynamic>.from(data));
        _name.text = c.name;
        _email.text = c.email;
        if (c.phone != null) _phone.text = c.phone!;
        if (c.defaultShippingAddress != null) {
          _address.text = c.defaultShippingAddress!;
        }
      }
    } catch (_) {
      // ignore prefill errors
    } finally {
      if (mounted) setState(() => _loadingPrefill = false);
    }
  }

  Future<(double?, double?)> _tryGps() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return (null, null);
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.denied ||
        permission == LocationPermission.deniedForever) {
      return (null, null);
    }
    final pos = await Geolocator.getCurrentPosition();
    return (pos.latitude, pos.longitude);
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final cart = ref.read(cartNotifierProvider);
    if (cart.isEmpty) {
      setState(() => _error = 'Cart is empty');
      return;
    }
    setState(() {
      _submitting = true;
      _error = null;
    });
    try {
      double? lat;
      double? lng;
      final useGps = await showDialog<bool>(
        context: context,
        builder: (c) => AlertDialog(
          title: const Text('Share location?'),
          content: const Text(
            'Optional: helps couriers find you. You can skip and rely on the address only.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(c, false),
              child: const Text('Skip'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(c, true),
              child: const Text('Use location'),
            ),
          ],
        ),
      );
      if (useGps == true) {
        final coords = await _tryGps();
        lat = coords.$1;
        lng = coords.$2;
      }

      final body = <String, dynamic>{
        'customer_name': _name.text.trim(),
        'customer_email': _email.text.trim(),
        'shipping_address': _address.text.trim(),
        'items': ref.read(cartNotifierProvider.notifier).toOrderItems(),
      };
      final ph = _phone.text.trim();
      if (ph.isNotEmpty) body['customer_phone'] = ph;
      if (lat != null && lng != null) {
        body['customer_latitude'] = lat;
        body['customer_longitude'] = lng;
      }

      final api = ref.read(apiClientProvider);
      final data = await api.post('/orders', body, auth: true);
      if (data is! Map) throw ApiException('Unexpected response');
      final order = Order.fromJson(Map<String, dynamic>.from(data));
      await ref.read(cartNotifierProvider.notifier).clear();
      if (!mounted) return;
      context.pushReplacement('/checkout/success', extra: order);
    } catch (e) {
      setState(() {
        _error = e is ApiException ? e.message : '$e';
        _submitting = false;
      });
    }
  }

  bool _emailOk(String v) {
    return RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(v.trim());
  }

  @override
  Widget build(BuildContext context) {
    final lines = ref.watch(cartNotifierProvider);
    final subtotal = ref.read(cartNotifierProvider.notifier).subtotal();

    if (lines.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('Checkout')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('Your cart is empty'),
              FilledButton(
                onPressed: () => context.go('/cart'),
                child: const Text('Back to cart'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (_loadingPrefill)
                const LinearProgressIndicator(),
              if (_error != null)
                Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Text(
                    _error!,
                    style: TextStyle(color: Theme.of(context).colorScheme.error),
                  ),
                ),
              Text('Order summary', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              ...lines.map(
                (l) => ListTile(
                  dense: true,
                  title: Text(l.snapshot.name, maxLines: 1, overflow: TextOverflow.ellipsis),
                  subtitle: Text('Qty ${l.qty}'),
                ),
              ),
              Text('Subtotal: ${formatMoney(subtotal)}',
                  style: Theme.of(context).textTheme.titleSmall),
              const Divider(height: 32),
              TextFormField(
                controller: _name,
                decoration: const InputDecoration(labelText: 'Full name'),
                validator: (v) =>
                    v == null || v.trim().length < 2 ? 'Min 2 characters' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _email,
                decoration: const InputDecoration(labelText: 'Email'),
                keyboardType: TextInputType.emailAddress,
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Required';
                  if (!_emailOk(v)) return 'Invalid email';
                  return null;
                },
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _phone,
                decoration: const InputDecoration(
                  labelText: 'Phone (optional)',
                ),
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _address,
                decoration: const InputDecoration(labelText: 'Shipping address'),
                maxLines: 3,
                validator: (v) =>
                    v == null || v.trim().length < 5 ? 'Min 5 characters' : null,
              ),
              const SizedBox(height: 24),
              FilledButton(
                onPressed: _submitting ? null : _submit,
                child: _submitting
                    ? const SizedBox(
                        height: 22,
                        width: 22,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Place order'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
