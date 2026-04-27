import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/network/api_error.dart';
import '../../providers/auth_and_api.dart';

class AccountScreen extends ConsumerWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authNotifierProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Account')),
      body: auth.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, _) => Center(child: Text('$e')),
        data: (customer) {
          if (customer == null) {
            return ListView(
              padding: const EdgeInsets.all(24),
              children: [
                const Text(
                  'Sign in to view your orders and saved details.',
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                FilledButton(
                  onPressed: () => context.push('/login?redirect=/account'),
                  child: const Text('Sign in'),
                ),
                const SizedBox(height: 12),
                OutlinedButton(
                  onPressed: () => context.push('/register?redirect=/account'),
                  child: const Text('Create account'),
                ),
              ],
            );
          }
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              ListTile(
                title: Text(customer.name),
                subtitle: Text(customer.email),
                leading: const CircleAvatar(child: Icon(Icons.person)),
              ),
              if (!customer.emailVerified)
                Card(
                  color: Theme.of(context).colorScheme.errorContainer,
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Email not verified'),
                        const SizedBox(height: 8),
                        FilledButton.tonal(
                          onPressed: () async {
                            try {
                              await ref
                                  .read(authNotifierProvider.notifier)
                                  .resendVerification();
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('Check your inbox'),
                                  ),
                                );
                              }
                            } catch (e) {
                              if (context.mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(
                                      e is ApiException
                                          ? e.message
                                          : 'Could not resend',
                                    ),
                                  ),
                                );
                              }
                            }
                          },
                          child: const Text('Resend verification email'),
                        ),
                      ],
                    ),
                  ),
                ),
              ListTile(
                leading: const Icon(Icons.receipt_long_outlined),
                title: const Text('My orders'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => context.push('/orders'),
              ),
              ListTile(
                leading: const Icon(Icons.logout),
                title: const Text('Sign out'),
                onTap: () async {
                  await ref.read(authNotifierProvider.notifier).logout();
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Signed out')),
                    );
                  }
                },
              ),
            ],
          );
        },
      ),
    );
  }
}
