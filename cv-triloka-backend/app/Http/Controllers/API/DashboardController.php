<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Item;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        
        // Cache dashboard untuk 5 menit
        $cacheKey = "dashboard_summary_user_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function() use ($user) {
            if ($user->isAdmin()) {
                return $this->adminDashboard();
            } else {
                return $this->klienDashboard($user);
            }
        });
    }

    /**
     * Admin dashboard
     */
    private function adminDashboard()
    {
        // Invoice statistics
        $totalInvoices = Invoice::count();
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $paidInvoices = Invoice::where('status', 'paid')->count();

        // Financial statistics
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingRevenue = Invoice::whereIn('status', ['unpaid', 'overdue'])->sum('total');
        $thisMonthRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        // Payment statistics
        $totalPayments = Payment::count();
        $thisMonthPayments = Payment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Inventory statistics
        $totalItems = Item::active()->count();
        $lowStockItems = Item::lowStock()->count();
        $outOfStockItems = Item::where('stock_quantity', 0)->where('is_active', true)->count();

        // Upcoming due invoices (next 7 days)
        $upcomingDue = Invoice::whereIn('status', ['unpaid', 'draft'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->with('klien')
            ->get();

        // Recent payments (last 5)
        $recentPayments = Payment::with(['invoice.klien', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        // Recent invoices (last 5)
        $recentInvoices = Invoice::with(['klien', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        // Low stock items
        $lowStockItemsList = Item::lowStock()->take(10)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'invoices' => [
                    'total' => $totalInvoices,
                    'unpaid' => $unpaidInvoices,
                    'overdue' => $overdueInvoices,
                    'paid' => $paidInvoices,
                ],
                'financials' => [
                    'total_revenue' => $totalRevenue,
                    'pending_revenue' => $pendingRevenue,
                    'this_month_revenue' => $thisMonthRevenue,
                ],
                'payments' => [
                    'total' => $totalPayments,
                    'this_month' => $thisMonthPayments,
                ],
                'inventory' => [
                    'total_items' => $totalItems,
                    'low_stock' => $lowStockItems,
                    'out_of_stock' => $outOfStockItems,
                ],
                'upcoming_due_invoices' => $upcomingDue,
                'recent_payments' => $recentPayments,
                'recent_invoices' => $recentInvoices,
                'low_stock_items' => $lowStockItemsList,
            ],
        ]);
    }

    /**
     * Klien dashboard
     */
    private function klienDashboard($user)
    {
        $userId = $user->id;
        
        // OPTIMIZED: 1 query untuk semua count + sum invoice statistics
        $stats = Invoice::where('klien_id', $userId)
            ->selectRaw('
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = "unpaid" THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN status = "overdue" THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as total_paid,
                SUM(CASE WHEN status IN ("unpaid", "overdue") THEN total ELSE 0 END) as total_outstanding
            ')
            ->first();

        // OPTIMIZED: 1 query untuk payment statistics
        $paymentStats = Payment::whereHas('invoice', function($q) use ($userId) {
                $q->where('klien_id', $userId);
            })
            ->selectRaw('
                COUNT(*) as total_payments,
                SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as this_month_payments
            ', [now()->month, now()->year])
            ->first();

        // Upcoming due invoices - limit 5 dengan select columns yang diperlukan saja
        $upcomingDue = Invoice::where('klien_id', $userId)
            ->whereIn('status', ['unpaid', 'draft'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->select('id', 'invoice_number', 'total', 'due_date', 'status')
            ->limit(5)
            ->get();

        // Recent payments - limit 5 dengan eager loading yang optimal
        $recentPayments = Payment::whereHas('invoice', function($q) use ($userId) {
                $q->where('klien_id', $userId);
            })
            ->with('invoice:id,invoice_number,total')
            ->select('id', 'invoice_id', 'amount', 'payment_date', 'status')
            ->latest()
            ->limit(5)
            ->get();

        // Recent invoices - limit 5 dengan select columns yang diperlukan saja
        $recentInvoices = Invoice::where('klien_id', $userId)
            ->select('id', 'invoice_number', 'total', 'status', 'due_date', 'created_at')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'invoices' => [
                    'total' => $stats->total_invoices ?? 0,
                    'unpaid' => $stats->unpaid_count ?? 0,
                    'overdue' => $stats->overdue_count ?? 0,
                    'paid' => $stats->paid_count ?? 0,
                ],
                'financials' => [
                    'total_paid' => $stats->total_paid ?? 0,
                    'total_outstanding' => $stats->total_outstanding ?? 0,
                ],
                'payments' => [
                    'total' => $paymentStats->total_payments ?? 0,
                    'this_month' => $paymentStats->this_month_payments ?? 0,
                ],
                'upcoming_due_invoices' => $upcomingDue,
                'recent_payments' => $recentPayments,
                'recent_invoices' => $recentInvoices,
            ],
        ]);
    }
}
