<?php

namespace App\Http\Controllers;

use App\Models\CustomerSession;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Make payment (works for active sessions AND closed sessions with pending dues)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_session_id' => 'required|exists:customer_sessions,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
        ]);

        $session = CustomerSession::findOrFail(
            $validated['customer_session_id']
        );

        // Calculate current bill
        $gamesTotal = $session->games()->sum('rate');
        $itemsTotal = $session->items()->sum('total');

        $total = $gamesTotal + $itemsTotal;

        $alreadyPaid = $session->payments()->sum('amount');

        $remaining = $total - $alreadyPaid;

        if ($remaining <= 0) {
            return response()->json([
                'message' => 'This session has no remaining dues',
            ], 422);
        }

        if ($validated['amount'] > $remaining) {
            return response()->json([
                'message' => 'Payment cannot be greater than remaining bill',
                'remaining' => $remaining,
            ], 422);
        }

        $payment = Payment::create([
            'customer_session_id' => $session->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'paid_at' => now(),
        ]);

        $newPaid = $alreadyPaid + $validated['amount'];
        $newRemaining = $total - $newPaid;

        $sessionUpdate = [
            'total_amount' => $total,
            'paid_amount' => $newPaid,
            'remaining_amount' => $newRemaining,
        ];

        // Bill fully paid off -> auto-close the session so it
        // drops out of the "active sessions" list.
        $justClosed = false;

        if ($newRemaining <= 0 && $session->status === 'active') {
            $sessionUpdate['status'] = 'closed';
            $sessionUpdate['closed_at'] = now();
            $justClosed = true;
        }

        $session->update($sessionUpdate);

        return response()->json([
            'message' => 'Payment saved successfully',
            'payment' => $payment,
            'total' => $total,
            'paid' => $newPaid,
            'remaining' => $newRemaining,
            'session_closed' => $justClosed,
            'session' => $session->fresh(),
        ], 201);
    }

    // Payment history
    public function index(CustomerSession $customerSession)
    {
        return response()->json(
            $customerSession->payments()->latest()->get()
        );
    }
}