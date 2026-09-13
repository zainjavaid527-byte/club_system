<?php

namespace App\Http\Controllers;

use App\Models\CustomerSession;
use App\Models\Product;
use App\Models\SessionItem;
use Illuminate\Http\Request;

class SessionItemController extends Controller
{
    // Add canteen item to customer session
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_session_id' => 'required|exists:customer_sessions,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $session = CustomerSession::findOrFail(
            $validated['customer_session_id']
        );

        if ($session->status !== 'active') {
            return response()->json([
                'message' => 'This customer session is already closed',
            ], 422);
        }

        $product = Product::findOrFail(
            $validated['product_id']
        );

        $total = $product->price * $validated['quantity'];

        $item = SessionItem::create([
            'customer_session_id' => $session->id,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'price' => $product->price,
            'total' => $total,
        ]);

        return response()->json([
            'message' => 'Canteen item added successfully',
            'item' => $item->load('product'),
        ], 201);
    }

    // Delete canteen item
    public function destroy(SessionItem $sessionItem)
    {
        $sessionItem->delete();

        return response()->json([
            'message' => 'Canteen item removed successfully',
        ]);
    }
}