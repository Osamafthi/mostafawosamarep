import 'package:intl/intl.dart';

String formatMoney(num value) {
  final f = NumberFormat.currency(symbol: 'E£', decimalDigits: 2);
  return f.format(value);
}

double effectiveUnitPrice(double price, double? discountPrice) {
  if (discountPrice != null && discountPrice > 0) return discountPrice;
  return price;
}

int? discountPercent(double price, double? discountPrice) {
  if (discountPrice == null || discountPrice <= 0 || price <= 0) return null;
  if (discountPrice >= price) return null;
  return (((price - discountPrice) / price) * 100).round();
}
