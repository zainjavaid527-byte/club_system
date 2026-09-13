<?php

namespace App\Http\Controllers;

use App\Models\CustomerSession;
use Illuminate\Http\Request;

class CustomerSessionController extends Controller
{
    // Start customer session
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        // Check if customer already has an active session
        $activeSession = CustomerSession::where('customer_id', $validated['customer_id'])
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return response()->json([
                'message' => 'Customer already has an active session',
                'session' => $activeSession,
            ], 422);
        }

        $session = CustomerSession::create([
            'customer_id' => $validated['customer_id'],
            'started_at' => now(),
            'status' => 'active',
            'total_amount' => 0,
            'paid_amount' => 0,
            'remaining_amount' => 0,
        ]);

        return response()->json([
            'message' => 'Customer session started',
            'session' => $session,
        ], 201);
    }

    // Get active session with complete bill
 public function show(CustomerSession $customerSession)
{
    $customerSession->load([
        'customer',
        'games',
        'items.product',
        'payments',
    ]);

    $gamesTotal = $customerSession->games->sum('rate');
    $itemsTotal = $customerSession->items->sum('total');
    $paidTotal = $customerSession->payments->sum('amount');

    $total = $gamesTotal + $itemsTotal;
    $remaining = max(0, $total - $paidTotal);

    // No DB write here — this endpoint is called after every
    // single action (add game, add item, add payment), so
    // persisting on every read was adding an unnecessary write.
    // total_amount / paid_amount / remaining_amount columns are
    // already kept accurate by PaymentController::store() and
    // CustomerSessionController::close().

    return response()->json([
        'session' => $customerSession,
        'games_total' => $gamesTotal,
        'canteen_total' => $itemsTotal,
        'total' => $total,
        'paid' => $paidTotal,
        'remaining' => $remaining,
    ]);
}

    // Close session
    public function close(CustomerSession $customerSession)
    {
        $customerSession->load(['games', 'items', 'payments']);

        $gamesTotal = $customerSession->games->sum('rate');
        $itemsTotal = $customerSession->items->sum('total');
        $paidTotal = $customerSession->payments->sum('amount');

        $total = $gamesTotal + $itemsTotal;
        $remaining = max(0, $total - $paidTotal);

        $customerSession->update([
            'total_amount' => $total,
            'paid_amount' => $paidTotal,
            'remaining_amount' => $remaining,
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Session closed successfully',
            'session' => $customerSession->fresh(),
        ]);
    }

    // Udhaar list: all closed sessions that still have money pending
    public function dues()
    {
        $dues = CustomerSession::with('customer')
            ->where('status', 'closed')
            ->where('remaining_amount', '>', 0)
            ->latest('closed_at')
            ->get();

        return response()->json([
            'total_due' => $dues->sum('remaining_amount'),
            'sessions' => $dues,
        ]);
    }
}