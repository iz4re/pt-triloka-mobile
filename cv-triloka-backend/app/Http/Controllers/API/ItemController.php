<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of items
     */
    public function index(Request $request)
    {
        $query = Item::query();

        // Filter active items only
        if ($request->has('active_only')) {
            $query->active();
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('item_code', 'like', '%' . $request->search . '%');
            });
        }

        $items = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can create items',
            ], 403);
        }

        $request->validate([
            'item_code' => 'required|string|unique:items,item_code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_threshold' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $item = Item::create($request->all());

        // Check if low stock immediately
        if ($item->isLowStock()) {
            // Notify all admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::createFor(
                    $admin,
                    'stock_alert',
                    'Low Stock Alert',
                    "Item {$item->name} ({$item->item_code}) has low stock: {$item->stock_quantity} {$item->unit}",
                    $item
                );
            }
        }

        ActivityLog::log('create_item', "Item {$item->name} ({$item->item_code}) created", $item, $user);

        return response()->json([
            'success' => true,
            'message' => 'Item created successfully',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified item
     */
    public function show($id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can update items',
            ], 403);
        }

        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        $request->validate([
            'item_code' => 'sometimes|string|unique:items,item_code,' . $id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'sometimes|string',
            'stock_quantity' => 'sometimes|numeric|min:0',
            'min_stock_threshold' => 'sometimes|numeric|min:0',
            'unit_price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $item->update($request->all());

        // Check if stock just became low
        if ($request->has('stock_quantity') && $item->isLowStock()) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::createFor(
                    $admin,
                    'stock_alert',
                    'Low Stock Alert',
                    "Item {$item->name} ({$item->item_code}) has low stock: {$item->stock_quantity} {$item->unit}",
                    $item
                );
            }
        }

        ActivityLog::log('update_item', "Item {$item->name} ({$item->item_code}) updated", $item, $user);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully',
            'data' => $item,
        ]);
    }

    /**
     * Remove the specified item
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can delete items',
            ], 403);
        }

        $item = Item::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        // Check if item is used in any invoice
        if ($item->invoiceItems()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete item used in invoices. Deactivate instead.',
            ], 400);
        }

        $itemName = $item->name;
        $item->delete();

        ActivityLog::log('delete_item', "Item {$itemName} deleted", null, $user);

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully',
        ]);
    }

    /**
     * Get low stock items (Miskin List)
     */
    public function lowStock(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can view low stock items',
            ], 403);
        }

        $lowStockItems = Item::lowStock()->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockItems,
        ]);
    }
}
