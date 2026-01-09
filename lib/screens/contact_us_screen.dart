import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:io';

class ContactUsScreen extends StatelessWidget {
  const ContactUsScreen({super.key});

  // Detail kontak asli user
  final String whatsappNumber = '6285329893150'; 
  final String companyAddress = 'Telkom University, Bandung';
  final String emailAddress = 'abizarabrarr@gmail.com';

  Future<void> _launchWhatsApp() async {
    final String message = Uri.encodeComponent('Halo Admin Triloka, saya butuh bantuan terkait aplikasi.');
    // Gunakan whatsapp:// untuk Android, https://wa.me/ untuk universal fallback
    final Uri whatsappUri = Uri.parse('whatsapp://send?phone=$whatsappNumber&text=$message');
    final Uri httpsUri = Uri.parse('https://wa.me/$whatsappNumber?text=$message');
    
    debugPrint('Attempting to launch WhatsApp: $whatsappUri');
    try {
      if (await canLaunchUrl(whatsappUri)) {
        await launchUrl(whatsappUri);
      } else {
        debugPrint('canLaunchUrl(whatsappUri) failed, trying fallback httpsUri');
        await launchUrl(httpsUri, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      debugPrint('Error launching WhatsApp: $e');
      // Final fallback to browser
      await launchUrl(httpsUri, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> _launchMaps() async {
    final Uri mapsUri = Uri.parse('geo:0,0?q=${Uri.encodeComponent(companyAddress)}');
    final Uri webMapsUri = Uri.parse('https://www.google.com/maps/search/?api=1&query=${Uri.encodeComponent(companyAddress)}');

    debugPrint('Attempting to launch Maps: $mapsUri');
    try {
      if (Platform.isAndroid) {
        if (await canLaunchUrl(mapsUri)) {
          await launchUrl(mapsUri);
        } else {
          debugPrint('canLaunchUrl(mapsUri) failed, trying webMapsUri');
          await launchUrl(webMapsUri, mode: LaunchMode.externalApplication);
        }
      } else {
        await launchUrl(webMapsUri, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      debugPrint('Error launching Maps: $e');
      await launchUrl(webMapsUri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: const Text('Hubungi Kami', style: TextStyle(color: Colors.black, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.black),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // Logo atau Image
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: const Color(0xFFF5F5F5),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.support_agent, size: 80, color: Color(0xFF6C5DD3)),
            ),
            const SizedBox(height: 24),
            const Text(
              'Ada Pertanyaan?',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            const Text(
              'Tim kami siap membantu kebutuhan bisnis dan kendala aplikasi Anda.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey, fontSize: 16),
            ),
            const SizedBox(height: 40),

            // WhatsApp Card
            _buildContactCard(
              icon: Icons.chat_bubble_outline,
              title: 'Chat WhatsApp',
              subtitle: 'Respon cepat di jam kerja',
              color: const Color(0xFF25D366),
              onTap: _launchWhatsApp,
            ),
            const SizedBox(height: 16),

            // Location Card
            _buildContactCard(
              icon: Icons.location_on_outlined,
              title: 'Lokasi Kantor',
              subtitle: companyAddress,
              color: const Color(0xFF4285F4),
              onTap: _launchMaps,
            ),
            const SizedBox(height: 16),

            // Email Card
            _buildContactCard(
              icon: Icons.email_outlined,
              title: 'Kirim Email',
              subtitle: emailAddress,
              color: const Color(0xFFEA4335),
              onTap: () async {
                final Uri emailUri = Uri.parse('mailto:$emailAddress?subject=Bantuan Aplikasi');
                if (await canLaunchUrl(emailUri)) {
                  await launchUrl(emailUri);
                }
              },
            ),

            const SizedBox(height: 40),
            const Text(
              'Jam Operasional:',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            const Text('Senin - Jumat: 08.00 - 17.00 WIB'),
          ],
        ),
      ),
    );
  }

  Widget _buildContactCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 15,
              offset: const Offset(0, 5),
            ),
          ],
          border: Border.all(color: const Color(0xFFF0F0F0)),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 28),
            ),
            const SizedBox(width: 20),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: const TextStyle(color: Colors.grey, fontSize: 13),
                  ),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios, size: 16, color: Colors.grey),
          ],
        ),
      ),
    );
  }
}
