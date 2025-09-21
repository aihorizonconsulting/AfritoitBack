<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $messages = Message::paginate($perPage);
            return response()->json($messages, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve messages - MessageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve messages'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'idU' => 'required|integer|exists:utilisateurs,idU',
                'prenomU' => 'required|string|max:255',
                'nomU' => 'required|string|max:255',
                'message' => 'required|string',
                'emailU' => 'nullable|email|max:255',
                'telphoneU' => 'nullable|string|max:20',
                'profilU' => 'required|string|max:255',
                'urlPhotoU' => 'nullable|url|max:255',
            ]);

            $message = Message::create($request->all());
            return response()->json($message, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create message - MessageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create message'], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $message = Message::findOrFail($id);
            return response()->json($message, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve message - MessageController : ' . $e->getMessage());
            return response()->json(['error' => 'Message not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $message = Message::findOrFail($id);
            $message->delete();
            return response()->json(['message' => 'Message deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete message - MessageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete message'], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids');
            if (empty($ids)) {
                return response()->json(['error' => 'No IDs provided'], 400);
            }

            Message::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Messages deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete messages - MessageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete messages'], 500);
        }
    }
    public function count()
    {
        try {
            $count = Message::count();
            return response()->json(['count' => $count], 200);
        } catch (\Exception $e) {
            Log::error('Failed to count messages - MessageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to count messages:'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $message = Message::findOrFail($id);
            $request->validate([
                'idU' => 'sometimes|required|integer|exists:utilisateurs,idU',
                'prenomU' => 'sometimes|required|string|max:255',
                'nomU' => 'sometimes|required|string|max:255',
                'message' => 'sometimes|required|string',
                'emailU' => 'sometimes|nullable|email|max:255',
                'telphoneU' => 'sometimes|nullable|string|max:20',
                'profilU' => 'sometimes|required|string|max:255',
                'urlPhotoU' => 'sometimes|nullable|url|max:255',
            ]);
            $message->update($request->all());
            return response()->json($message, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update message - MessageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update message'], 500);
        }
    }
}
