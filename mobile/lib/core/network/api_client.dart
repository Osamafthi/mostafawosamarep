import 'package:dio/dio.dart';

import '../config/env.dart';
import 'api_error.dart';

class ApiClient {
  ApiClient({
    required Future<String?> Function() readToken,
    required void Function() onAuthFailure,
    String? baseUrl,
  })  : _readToken = readToken,
        _onAuthFailure = onAuthFailure,
        _dio = Dio(
          BaseOptions(
            baseUrl: baseUrl ?? Env.apiBaseUrl,
            connectTimeout: const Duration(seconds: 30),
            receiveTimeout: const Duration(seconds: 30),
            headers: {'Accept': 'application/json'},
            validateStatus: (code) => code != null && code < 500,
          ),
        ) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final auth = options.extra['auth'] == true;
          if (auth) {
            final token = await _readToken();
            if (token != null && token.isNotEmpty) {
              options.headers['Authorization'] = 'Bearer $token';
            }
          }
          return handler.next(options);
        },
        onResponse: (response, handler) {
          final auth = response.requestOptions.extra['auth'] == true;
          final code = response.statusCode ?? 0;
          if (auth && (code == 401 || code == 403)) {
            _onAuthFailure();
          }
          final data = response.data;
          if (data is Map && data['success'] == false) {
            final ex = ApiException.fromResponseBody(
              Map<String, dynamic>.from(data),
              response.statusCode,
            );
            return handler.reject(
              DioException(
                requestOptions: response.requestOptions,
                response: response,
                type: DioExceptionType.badResponse,
                error: ex,
              ),
            );
          }
          return handler.next(response);
        },
        onError: (err, handler) {
          final auth = err.requestOptions.extra['auth'] == true;
          final res = err.response;
          final code = res?.statusCode;
          if (auth && (code == 401 || code == 403)) {
            _onAuthFailure();
          }
          final data = res?.data;
          if (data is Map && data['success'] == false) {
            final ex = ApiException.fromResponseBody(
              Map<String, dynamic>.from(data),
              code,
            );
            return handler.reject(
              DioException(
                requestOptions: err.requestOptions,
                response: res,
                type: DioExceptionType.badResponse,
                error: ex,
                message: ex.message,
              ),
            );
          }
          final ex = err.error is ApiException
              ? err.error as ApiException
              : ApiException(
                  err.message ?? 'Network error',
                  statusCode: code,
                );
          return handler.reject(
            DioException(
              requestOptions: err.requestOptions,
              response: res,
              type: err.type,
              error: ex,
              message: ex.message,
            ),
          );
        },
      ),
    );
  }

  final Future<String?> Function() _readToken;
  final void Function() _onAuthFailure;
  final Dio _dio;

  dynamic _unwrap(Response response) {
    final data = response.data;
    if (data is Map && data.containsKey('data')) {
      return data['data'];
    }
    return data;
  }

  Future<dynamic> get(
    String path, {
    Map<String, dynamic>? query,
    bool auth = false,
  }) async {
    try {
      final res = await _dio.get<dynamic>(
        path,
        queryParameters: query,
        options: Options(extra: {'auth': auth}),
      );
      return _unwrap(res);
    } on DioException catch (e) {
      if (e.error is ApiException) throw e.error as ApiException;
      throw ApiException(e.message ?? 'Network error', statusCode: e.response?.statusCode);
    }
  }

  Future<dynamic> post(
    String path,
    dynamic body, {
    bool auth = false,
  }) async {
    try {
      final res = await _dio.post<dynamic>(
        path,
        data: body,
        options: Options(
          extra: {'auth': auth},
          headers: {'Content-Type': 'application/json'},
        ),
      );
      return _unwrap(res);
    } on DioException catch (e) {
      if (e.error is ApiException) throw e.error as ApiException;
      throw ApiException(e.message ?? 'Network error', statusCode: e.response?.statusCode);
    }
  }
}
