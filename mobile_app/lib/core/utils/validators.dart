String? validateRequired(String? value) {
  if (value == null || value.trim().isEmpty) {
    return 'Required';
  }
  return null;
}

String? validateEmail(String? value) {
  final v = value?.trim() ?? '';
  if (v.isEmpty) return 'Required';
  const pattern = r'^[^@\s]+@[^@\s]+\.[^@\s]+$';
  final regExp = RegExp(pattern);
  if (!regExp.hasMatch(v)) return 'Invalid email';
  return null;
}

String? validatePassword(String? value) {
  final v = value ?? '';
  if (v.length < 6) return 'Minimum 6 characters';
  return null;
}
