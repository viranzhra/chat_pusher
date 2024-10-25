<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\ChatEvent;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function room($room) {
        // Mendapatkan data room
        $room = DB::table('chat_rooms')->where('id', $room)->first();
    
        // Pesan otomatis dari admin
        $adminMessage = "Hai, apakah ada yang bisa dibantu?";
        $userId = Auth::id(); // ID user yang sedang login
    
        // Cek apakah user sudah pernah mendapatkan pesan otomatis
        $messageExists = Chat::where('chat_room_id', $room->id)
                              ->where('user_id', 1) // Admin ID
                              ->where('message', $adminMessage)
                              ->exists();
    
        // Jika user belum menerima pesan otomatis, kirim pesan
        if (!$messageExists) {
            // Simpan pesan dari admin ke dalam database
            Chat::create([
                'chat_room_id' => $room->id,
                'user_id' => 1, // Gantilah dengan ID admin yang sesuai
                'message' => $adminMessage,
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            // Memicu event broadcast untuk mengirim pesan ke pengguna lain dalam room
            broadcast(new ChatEvent($room->id, $adminMessage, 1))->toOthers(); // ID admin
        }
    
        // Mengirim data room dan users ke view 'chat'
        return view('chat', compact('room'));
    }
    
    public function getChat($room) {
        // Join with user
        $chats = DB::table('chats')
            ->join('users', 'users.id', '=', 'chats.user_id')
            ->where('chat_room_id', $room)
            ->select('chats.*', 'users.name as user_name')
            ->get();

        return response()->json($chats);
    }

    // Send chat
    public function sendChat(Request $request) {
        $chat = DB::table('chats')->insert([
            'chat_room_id' => $request->room,
            'user_id' => auth()->user()->id,
            'message' => $request->message,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Trigger event
        broadcast(new ChatEvent($request->room, $request->message, auth()->user()->id));

        return response()->json($chat);
    }

    // Hapus chat
    // public function deleteChat($chatId) {
    //     $chat = Chat::findOrFail($chatId);
    //     $chat->delete();

    //     return response()->json(['success' => true]);
    // }

    public function deleteAll($roomId)
    {
        try {
            Chat::where('chat_room_id', $roomId)->delete();
            return response()->json(['message' => 'Chat berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus chat.'], 500);
        }
    }



    public function chat($user) {
        $my_id = auth()->user()->id;
        $target_id = $user;

        $my_room = DB::table('chat_room_users');
        $target_room = clone $my_room;

        // Get my room
        $my_room = $my_room->where('user_id', $my_id)->get()->keyBy('chat_room_id')->toArray();
        // Get target room
        $target_room = $target_room->where('user_id', $target_id)->get()->keyBy('chat_room_id')->toArray();

        // Check room
        $room = array_intersect_key($my_room, $target_room);

        // If room exists
        if($room) return redirect()->route('chat.room', ['room' => array_keys($room)[0]]);

        // If room doesn't exist
        $uuid = Str::orderedUuid();
        $room = DB::table('chat_rooms')->insert([
            'id' => $uuid,
            'name' => 'generate by system',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Add users to room
        DB::table('chat_room_users')->insert([
            [
                'chat_room_id' => $uuid,
                'user_id' => $my_id,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'chat_room_id' => $uuid,
                'user_id' => $target_id,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        return redirect()->route('chat.room', ['room' => $uuid]);
    }
}
