<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\PostEventSubmission;
use App\Models\PostEventSubmissionFile;
use App\Models\PostEventCivilCase;
use App\Models\WorkerReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostEventSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $worker = $user->worker; // workers.worker_id

        $reservations = WorkerReservation::with(['event', 'workRole.roleType'])
            ->where('worker_id', $worker->worker_id)
            ->where('status', 'RESERVED')   // adjust if you use other statuses
            ->orderByDesc('reserved_at')
            ->get();

        $submissions = PostEventSubmission::with(['event', 'role', 'civilCases'])
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
            'submission_id'         => ['nullable', 'exists:post_event_submissions,id'],
            'role_slug'             => ['required', 'string'],
            'data'                  => ['nullable', 'string'],  // JSON string
            'civil_cases'           => ['nullable', 'string'],  // JSON string
        ]);

        $user   = $request->user();
        $worker = $user->worker;

        // If editing an existing submission, load & enforce 24h + ownership
        $existing = null;
        if ($request->filled('submission_id')) {
            $existing = PostEventSubmission::where('id', $request->submission_id)
                ->where('worker_id', $worker->worker_id)
                ->firstOrFail();

            if (
                !$existing->submitted_at ||
                $existing->submitted_at->lt(now()->subDay()) ||
                $existing->status !== 'pending'
            ) {
                abort(403, 'This report can no longer be edited.');
            }
        }

        $reservation = WorkerReservation::with(['event', 'worker', 'workRole'])
            ->where('reservation_id', $request->worker_reservation_id)
            ->where('worker_id', $worker->worker_id)
            ->firstOrFail();

        $data       = json_decode($request->input('data', '{}'), true) ?? [];
        $civilCases = json_decode($request->input('civil_cases', '[]'), true) ?? [];

        return DB::transaction(function () use ($request, $reservation, $data, $civilCases, $existing) {

            if ($existing) {
                // UPDATE existing submission
                $existing->update([
                    'worker_reservation_id' => $reservation->reservation_id,
                    'event_id'              => $reservation->event_id,
                    'worker_id'             => $reservation->worker_id,
                    'work_role_id'          => $reservation->work_role_id,
                    'role_slug'             => $request->role_slug,
                    'general_notes'         => $request->input('general_notes'),
                    'data'                  => $data,
                    'status'                => 'pending',
                    'submitted_at'          => now(), // reset window
                ]);

                // Replace civil cases
                $existing->civilCases()->delete();
                $submission = $existing;
            } else {
                // CREATE new submission
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
            }

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

            // Files (all roles) – we only ADD new files; we don't delete old ones on edit
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
                'updated'    => (bool) $existing,
            ]);
        });
    }
}
