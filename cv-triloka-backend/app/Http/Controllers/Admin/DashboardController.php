<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectRequest;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Get stats for current month
        $thisMonth = now()->startOfMonth();
        
        $stats = [
            'total_requests' => ProjectRequest::where('created_at', '>=', $thisMonth)->count(),
            'pending_requests' => ProjectRequest::where('status', 'pending')->count(),
            'total_clients' => User::where('role', 'klien')->count(),
            'total_revenue' => Invoice::where('status', 'paid')
                ->where('created_at', '>=', $thisMonth)
                ->where('invoice_type', 'project')  // Exclude survey fees from revenue
                ->sum('total'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'survey_fees_collected' => Invoice::where('status', 'paid')
                ->where('invoice_type', 'survey')
                ->sum('total'),
        ];

        // Recent project requests
        $recentRequests = ProjectRequest::with('klien')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending approvals
        $pendingApprovals = ProjectRequest::where('status', 'pending')
            ->with('klien')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent payments (new!)
        $recentPayments = Payment::with(['invoice.klien'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRequests', 'pendingApprovals', 'recentPayments'));
    }
}
