import 'package:flutter/material.dart';
import 'constants.dart';

class ErrorHandler {
  static String getErrorMessage(dynamic error) {
    if (error == null) return AppConstants.genericError;

    String errorString = error.toString().toLowerCase();
    if (errorString.contains('socket') ||
        errorString.contains('network') ||
        errorString.contains('connection')) {
      return AppConstants.networkError;
    }

    if (errorString.contains('500') || errorString.contains('server')) {
      return AppConstants.serverError;
    }

    if (errorString.contains('401') || errorString.contains('unauthorized')) {
      return AppConstants.unauthorizedError;
    }

    if (errorString.contains('404') || errorString.contains('not found')) {
      return AppConstants.notFoundError;
    }

    if (errorString.contains('validation') || errorString.contains('422')) {
      return AppConstants.validationError;
    }

    return AppConstants.genericError;
  }

  static void showError(BuildContext context, dynamic error) {
    final message = getErrorMessage(error);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
        duration: AppConstants.snackBarDuration,
        behavior: SnackBarBehavior.floating,
        action: SnackBarAction(
          label: 'OK',
          textColor: Colors.white,
          onPressed: () {
            ScaffoldMessenger.of(context).hideCurrentSnackBar();
          },
        ),
      ),
    );
  }

  static void showSuccess(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.green,
        duration: AppConstants.snackBarDuration,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  static void showInfo(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.blue,
        duration: AppConstants.snackBarDuration,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }
}
