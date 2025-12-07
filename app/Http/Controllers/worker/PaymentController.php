<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerReservation;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // get the logged-in worker profile
        $worker = Worker::where('user_id', $user->id)->firstOrFail();

        // 🔐 block volunteers
        if ($worker->is_volunteer) {
            abort(403, 'This page is only available for paid workers.');
        }

        // completed reservations only
        $reservations = WorkerReservation::with('event')
            ->where('worker_id', $worker->worker_id)
            ->where('status', 'COMPLETED')
            ->orderByDesc('check_out_time')
            ->get();

        $totalHours = $reservations->sum('credited_hours');
        $hourlyRate = $worker->hourly_rate ?? 0;
        $totalPay   = round($totalHours * $hourlyRate, 2);

        return view('Worker.payments', [
            'worker'       => $worker,
            'reservations' => $reservations,
            'totalHours'   => $totalHours,
            'hourlyRate'   => $hourlyRate,
            'totalPay'     => $totalPay,
        ]);
    }
}
