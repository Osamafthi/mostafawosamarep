class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.fieldErrors});

  final String message;
  final int? statusCode;
  final Map<String, List<String>>? fieldErrors;

  @override
  String toString() => 'ApiException($statusCode): $message';

  static String formatFirstFieldError(Map<String, dynamic>? errors) {
    if (errors == null || errors.isEmpty) return '';
    for (final entry in errors.entries) {
      final v = entry.value;
      if (v is List && v.isNotEmpty && v.first is String) {
        return v.first as String;
      }
    }
    return '';
  }

  static ApiException fromResponseBody(
    dynamic body,
    int? statusCode,
  ) {
    if (body is Map<String, dynamic>) {
      final err = body['error'];
      final msg = err is String ? err : 'Request failed';
      Map<String, List<String>>? fields;
      final raw = body['errors'];
      if (raw is Map) {
        fields = {};
        for (final e in raw.entries) {
          final k = e.key.toString();
          final v = e.value;
          if (v is List) {
            fields[k] = v.map((x) => x.toString()).toList();
          }
        }
      }
      final first = formatFirstFieldError(body['errors'] is Map ? Map<String, dynamic>.from(body['errors'] as Map) : null);
      return ApiException(
        first.isNotEmpty ? first : msg,
        statusCode: statusCode,
        fieldErrors: fields,
      );
    }
    return ApiException('HTTP ${statusCode ?? '?'}', statusCode: statusCode);
  }
}
