import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:image/image.dart' as img;

class ImageHelper {
  static Future<File> compressImage(
    File file, {
    int maxWidth = 1024,
    int quality = 85,
  }) async {
    try {
      final bytes = await file.readAsBytes();
      final image = img.decodeImage(bytes);
      if (image == null) return file;
      img.Image resized = image;
      if (image.width > maxWidth) {
        resized = img.copyResize(image, width: maxWidth);
      }
      final compressed = img.encodeJpg(resized, quality: quality);
      final tempDir = file.parent;
      final tempFile = File(
        '${tempDir.path}/compressed_${DateTime.now().millisecondsSinceEpoch}.jpg',
      );
      await tempFile.writeAsBytes(compressed);
      return tempFile;
    } catch (e) {
      debugPrint('Error compressing image: $e');
      return file;
    }
  }
  static Future<double> getFileSizeMB(File file) async {
    final bytes = await file.length();
    return bytes / (1024 * 1024);
  }
  static Future<bool> validateFileSize(File file, {int maxSizeMB = 5}) async {
    final sizeMB = await getFileSizeMB(file);
    return sizeMB <= maxSizeMB;
  }
  static bool validateImageType(String path) {
    final ext = path.split('.').last.toLowerCase();
    return ['jpg', 'jpeg', 'png'].contains(ext);
  }
}
