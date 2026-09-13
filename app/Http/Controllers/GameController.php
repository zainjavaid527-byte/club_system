<?php

namespace App\Http\Controllers;

use App\Models\CustomerSession;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    // Add game
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_session_id' => 'required|exists:customer_sessions,id',
            'game_type' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0',
        ]);

        $session = CustomerSession::findOrFail(
            $validated['customer_session_id']
        );

        if ($session->status !== 'active') {
            return response()->json([
                'message' => 'This customer session is already closed',
            ], 422);
        }

        $game = Game::create([
            'customer_session_id' => $session->id,
            'game_type' => $validated['game_type'],
            'rate' => $validated['rate'],
            'played_at' => now(),
        ]);

        return response()->json([
            'message' => 'Game added successfully',
            'game' => $game,
        ], 201);
    }

    // Delete game
    public function destroy(Game $game)
    {
        $game->delete();

        return response()->json([
            'message' => 'Game removed successfully',
        ]);
    }
}