<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Message;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        return view('worker.messages');
    }

    public function contacts(Request $request)
    {
        $authUser = $request->user();
        $worker   = Worker::where('user_id', $authUser->id)->firstOrFail();

        $employeeUserIds = Employee::query()
            ->join('events', 'events.created_by', '=', 'employees.employee_id')
            ->join('workers_reservations', 'workers_reservations.event_id', '=', 'events.event_id')
            ->where('workers_reservations.worker_id', $worker->worker_id)
            ->pluck('employees.user_id')
            ->filter()
            ->unique()
            ->values();

        if ($employeeUserIds->isEmpty()) {
            return response()->json([
                'ok'       => true,
                'contacts' => [],
            ]);
        }

        $allMessages = Message::query()
            ->where(function ($q) use ($authUser) {
                $q->where('sender_id', $authUser->id)
                  ->orWhere('receiver_id', $authUser->id);
            })
            ->orderByDesc('timestamp')
            ->get();

        $threads = [];

        foreach ($allMessages as $m) {
            $otherUserId = $m->sender_id == $authUser->id
                ? $m->receiver_id
                : $m->sender_id;

            if (! $otherUserId || ! $employeeUserIds->contains($otherUserId)) {
                continue;
            }

            if (! isset($threads[$otherUserId])) {
                // safe time formatting
                $time = '';
                if ($m->timestamp) {
                    $time = Carbon::parse($m->timestamp)->diffForHumans();
                }

                $threads[$otherUserId] = [
                    'id'          => $otherUserId,
                    'name'        => null,
                    'avatar'      => 'U',
                    'lastMessage' => $m->content,
                    'time'        => $time,
                    'unread'      => 0,
                    'online'      => false,
                ];
            }

            if ($m->receiver_id == $authUser->id && ! $m->is_read) {
                $threads[$otherUserId]['unread']++;
            }
        }

        if (empty($threads)) {
            return response()->json([
                'ok'       => true,
                'contacts' => [],
            ]);
        }

        $users = User::whereIn('id', array_keys($threads))->get()->keyBy('id');

        foreach ($threads as $uid => &$t) {
            if ($u = $users->get($uid)) {
                $t['name']   = $u->name ?? ('User #' . $uid);
                $t['avatar'] = mb_substr($u->name ?? 'U', 0, 1);
            }
        }
        unset($t);

        return response()->json([
            'ok'       => true,
            'contacts' => array_values($threads),
        ]);
    }

    public function thread(Request $request, User $user)
    {
        $authUser = $request->user();

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
                     'is_read' => (bool) $m->is_read,
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

        return response()->json([
            'ok'      => true,
            'message' => [
                'id'      => $msg->message_id,
                'from_me' => true,
                'text'    => $msg->content,
                'time'    => $msg->timestamp ? $msg->timestamp->format('H:i') : '',
                'avatar'  => mb_substr($authUser->name ?? 'U', 0, 1),
                'is_read' => (bool) $msg->is_read,
            ],
        ]);
    }
}
