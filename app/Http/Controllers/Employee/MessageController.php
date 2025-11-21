<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Message;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        return view('employee.messages');
    }

    /**
     * Contacts for the employee:
     * workers who are reserved in events of this employee.
     */
    public function contacts(Request $request)
    {
        $authUser = $request->user();
        $employee = Employee::where('user_id', $authUser->id)->firstOrFail();

        // Workers connected to this employee via events
        $workers = Worker::query()
            ->select('workers.*')
            ->join('workers_reservations', 'workers_reservations.worker_id', '=', 'workers.worker_id')
            ->join('events', 'events.event_id', '=', 'workers_reservations.event_id')
            ->where('events.created_by', $employee->employee_id)   // adjust if created_by stores user_id
            ->distinct()
            ->get();

        $contacts = $workers->map(function (Worker $worker) use ($authUser) {
            $contactUserId = $worker->user_id;
            $contactUser   = User::find($contactUserId);
            if (! $contactUser) {
                return null;
            }

            // Last message between me (employee) and this worker
            $lastMessage = Message::where(function ($q) use ($authUser, $contactUserId) {
                    $q->where('sender_id', $authUser->id)
                      ->where('receiver_id', $contactUserId);
                })
                ->orWhere(function ($q) use ($authUser, $contactUserId) {
                    $q->where('sender_id', $contactUserId)
                      ->where('receiver_id', $authUser->id);
                })
                ->latest('timestamp')
                ->first();

            // Safe time formatting
            $time = '';
            if ($lastMessage && $lastMessage->timestamp) {
                $time = Carbon::parse($lastMessage->timestamp)->diffForHumans();
            }

            $unreadCount = Message::where('sender_id', $contactUserId)
                ->where('receiver_id', $authUser->id)
                ->where('is_read', false)
                ->count();

            $name     = $contactUser->name ?? ('Worker #' . $worker->worker_id);
            $initials = mb_substr($name, 0, 1);

            return [
                'id'          => $contactUserId,        // ALWAYS users.id
                'name'        => $name,
                'avatar'      => $initials,
                'lastMessage' => $lastMessage?->content ?? '',
                'time'        => $time,
                'unread'      => $unreadCount,
                'online'      => false,
            ];
        })->filter()->values();

        return response()->json([
            'ok'       => true,
            'contacts' => $contacts,
        ]);
    }

    /**
     * Get thread messages with a specific worker (user row).
     */
    public function thread(Request $request, User $user)
    {
        $authUser = $request->user();

        // Mark as read messages from this worker -> me
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $authUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where(function ($q) use ($authUser, $user) {
                $q->where('sender_id', $authUser->id)
                  ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($authUser, $user) {
                $q->where('sender_id', $user->id)
                  ->where('receiver_id', $authUser->id);
            })
            ->orderBy('timestamp')
            ->get()
            ->map(function (Message $m) use ($authUser) {
                $time = '';
                if ($m->timestamp) {
                    $time = Carbon::parse($m->timestamp)->format('H:i');
                }

                return [
                    'id'      => $m->message_id,
                    'from_me' => $m->sender_id === $authUser->id,
                    'text'    => $m->content,
                    'time'    => $time,
                    'avatar'  => mb_substr($m->sender->name ?? 'U', 0, 1),
                ];
            });

        return response()->json([
            'ok'       => true,
            'contact'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'avatar' => mb_substr($user->name ?? 'U', 0, 1),
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send message from employee to worker (user row).
     */
    public function send(Request $request, User $user)
    {
        $authUser = $request->user();

        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $msg = Message::create([
            'sender_id'   => $authUser->id,
            'receiver_id' => $user->id,
            'content'     => $data['message'],
            'timestamp'   => now(),
            'is_read'     => false,
        ]);

        $time = '';
        if ($msg->timestamp) {
            // if you cast timestamp as datetime in Message model, this is already Carbon
            $time = \Carbon\Carbon::parse($msg->timestamp)->format('H:i');
        }

        return response()->json([
            'ok'      => true,
            'message' => [
                'id'      => $msg->message_id,
                'from_me' => true,
                'text'    => $msg->content,
                'time'    => $time,
                'avatar'  => mb_substr($authUser->name ?? 'U', 0, 1),
            ],
        ]);
    }
}
