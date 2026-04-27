/// Compile-time API base (no secrets). Pass via:
/// `flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1`
class Env {
  Env._();

  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://localhost:8000/api/v1',
  );
}
