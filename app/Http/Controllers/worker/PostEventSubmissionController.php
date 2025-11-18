<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\PostEventSubmission;
use App\Models\PostEventSubmissionFile;
use App\Models\PostEventCivilCase;
use App\Models\WorkerReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostEventSubmissionController extends Controller
{
     public function index(Request $request)
    {
        $user = $request->user();

        // get worker row for this user (adjust relation/column if different)
        $worker = $user->worker;    // or Worker::where('user_id',$user->id)->firstOrFail();

        // Reservations for this worker
        $reservations = WorkerReservation::with('event')
            ->where('worker_id', $worker->worker_id)
            // you can change this filter as you like:
            ->where('status', 'RESERVED')   // only active reservations
            // ->whereIn('status', ['RESERVED','CHECKED_IN']) // example alternative
            ->orderByDesc('reserved_at')
            ->get();

        // Existing submissions list (if you already used this)
        $submissions = PostEventSubmission::with(['event', 'role'])
            ->where('worker_id', $worker->worker_id)
            ->latest('submitted_at')
            ->get();

        return view('worker.post-event-submission', [
            'reservations' => $reservations,
            'submissions'  => $submissions,
        ]);
    }

public function store(Request $request)
{
    $request->validate([
        'worker_reservation_id' => ['required', 'exists:workers_reservations,reservation_id'],
        'role_slug'             => ['required', 'string'],
        'data'                  => ['nullable', 'string'],  // JSON string
        'civil_cases'           => ['nullable', 'string'],  // JSON string
    ]);

    $reservation = WorkerReservation::with(['event', 'worker', 'workRole'])
        ->where('reservation_id', $request->worker_reservation_id)
        ->firstOrFail();

    $data = json_decode($request->input('data', '{}'), true) ?? [];
    $civilCases = json_decode($request->input('civil_cases', '[]'), true) ?? [];

    return DB::transaction(function () use ($request, $reservation, $data, $civilCases) {

        $submission = PostEventSubmission::create([
            'worker_reservation_id' => $reservation->reservation_id,
            'event_id'              => $reservation->event_id,
            'worker_id'             => $reservation->worker_id,
            'work_role_id'          => $reservation->work_role_id,
            'role_slug'             => $request->role_slug,
            'general_notes'         => $request->input('general_notes'),
            'data'                  => $data,
            'status'                => 'pending',
            'submitted_at'          => now(),
        ]);

        // Civil cases
        foreach ($civilCases as $case) {
            PostEventCivilCase::create([
                'submission_id' => $submission->id,
                'case_type'     => $case['type'] ?? 'other',
                'age'           => $case['age'] ?? null,
                'gender'        => $case['gender'] ?? null,
                'action_taken'  => $case['action'] ?? 'other',
                'notes'         => $case['notes'] ?? null,
            ]);
        }

        // Files (all roles)
        foreach ($request->allFiles() as $section => $files) {
            foreach ((array) $files as $file) {
                $path = $file->store('post-event-submissions', 'public');

                PostEventSubmissionFile::create([
                    'submission_id' => $submission->id,
                    'section'       => $section, // e.g. 'media_files', 'cd_forms'
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getClientMimeType(),
                    'size_bytes'    => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'ok'         => true,
            'submission' => $submission->id,
        ]);
    });
}


}
