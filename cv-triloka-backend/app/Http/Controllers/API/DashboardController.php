<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Item;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary
     */
    public function summary(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } else {
            return $this->klienDashboard($user);
        }
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
        // Invoice statistics for this klien
        $myInvoices = Invoice::where('klien_id', $user->id);
        
        $totalInvoices = $myInvoices->count();
        $unpaidInvoices = (clone $myInvoices)->where('status', 'unpaid')->count();
        $overdueInvoices = (clone $myInvoices)->where('status', 'overdue')->count();
        $paidInvoices = (clone $myInvoices)->where('status', 'paid')->count();

        // Financial statistics
        $totalPaid = (clone $myInvoices)->where('status', 'paid')->sum('total');
        $totalOutstanding = (clone $myInvoices)->whereIn('status', ['unpaid', 'overdue'])->sum('total');

        // My payments
        $myPayments = Payment::whereHas('invoice', function($q) use ($user) {
            $q->where('klien_id', $user->id);
        });

        $totalPaymentsMade = $myPayments->count();
        $thisMonthPayments = (clone $myPayments)->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Upcoming due invoices
        $upcomingDue = Invoice::where('klien_id', $user->id)
            ->whereIn('status', ['unpaid', 'draft'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->get();

        // Recent payments (last 5)
        $recentPayments = Payment::whereHas('invoice', function($q) use ($user) {
                $q->where('klien_id', $user->id);
            })
            ->with('invoice')
            ->latest()
            ->take(5)
            ->get();

        // Recent invoices (last 5)
        $recentInvoices = Invoice::where('klien_id', $user->id)
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();

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
                    'total_paid' => $totalPaid,
                    'total_outstanding' => $totalOutstanding,
                ],
                'payments' => [
                    'total' => $totalPaymentsMade,
                    'this_month' => $thisMonthPayments,
                ],
                'upcoming_due_invoices' => $upcomingDue,
                'recent_payments' => $recentPayments,
                'recent_invoices' => $recentInvoices,
            ],
        ]);
    }
}
