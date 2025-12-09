<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get mitra user (for testing)
        $mitraUser = User::where('email', 'mitra@example.com')->first();
        
        if (!$mitraUser) {
            $this->command->warn('Mitra user not found. Run UserSeeder first.');
            return;
        }

        // Clear existing test notifications
        Notification::where('user_id', $mitraUser->id)->delete();

        // Create sample notifications
        $notifications = [
            [
                'user_id' => $mitraUser->id,
                'type' => 'invoice',
                'title' => 'Invoice Baru Dibuat',
                'message' => 'Invoice #INV-2024-001 telah dibuat untuk proyek Website Development. Total: Rp 15.000.000',
                'is_read' => false,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'user_id' => $mitraUser->id,
                'type' => 'payment',
                'title' => 'Pembayaran Diterima',
                'message' => 'Pembayaran sebesar Rp 5.000.000 untuk Invoice #INV-2024-001 telah diterima',
                'is_read' => false,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            [
                'user_id' => $mitraUser->id,
                'type' => 'reminder',
                'title' => 'Jatuh Tempo Invoice',
                'message' => 'Invoice #INV-2024-002 akan jatuh tempo dalam 3 hari. Mohon segera lakukan pembayaran.',
                'is_read' => false,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'user_id' => $mitraUser->id,
                'type' => 'invoice',
                'title' => 'Invoice Telah Dibayar',
                'message' => 'Invoice #INV-2023-099 telah lunas. Terima kasih atas pembayaran Anda.',
                'is_read' => true,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => $mitraUser->id,
                'type' => 'info',
                'title' => 'Selamat Datang di CV Triloka',
                'message' => 'Terima kasih telah bergabung dengan sistem manajemen keuangan CV Triloka. Jangan ragu untuk menghubungi kami jika ada pertanyaan.',
                'is_read' => true,
                'created_at' => now()->subWeek(),
                'updated_at' => now()->subWeek(),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }

        $this->command->info('Sample notifications created successfully!');
    }
}
