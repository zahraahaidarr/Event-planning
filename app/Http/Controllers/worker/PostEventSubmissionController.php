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
        $worker = $user->worker;

        // Worker submissions (used to hide already-reported events from dropdown)
        $submissions = PostEventSubmission::with(['event', 'workRole.roleType', 'civilCases'])
            ->where('worker_id', $worker->worker_id)
            ->latest('submitted_at')
            ->get();

        $reportedEventIds = $submissions->pluck('event_id')->filter()->unique()->values();

        // ✅ Only COMPLETED reservations + event exists + NOT already submitted (per event)
        $reservations = WorkerReservation::with(['event', 'workRole.roleType'])
            ->where('worker_id', $worker->worker_id)
            ->whereHas('event')
            ->where('status', 'COMPLETED')
            ->when($reportedEventIds->isNotEmpty(), function ($q) use ($reportedEventIds) {
                $q->whereNotIn('event_id', $reportedEventIds);
            })
            ->orderByDesc('reserved_at')
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
            'owner_rating'          => ['nullable', 'integer', 'min:1', 'max:5'],
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

        // ✅ must be completed
        if ($reservation->status !== 'COMPLETED') {
            return response()->json([
                'ok'      => false,
                'message' => 'You can only submit a report after the event is completed.',
            ], 422);
        }

        // ✅ only ONE submission per EVENT per WORKER (when creating new)
        if (!$request->filled('submission_id')) {
            $already = PostEventSubmission::where('worker_id', $worker->worker_id)
                ->where('event_id', $reservation->event_id)
                ->exists();

            if ($already) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'You already submitted a report for this event.',
                ], 409);
            }
        }

        $data       = json_decode($request->input('data', '{}'), true) ?? [];
        $civilCases = json_decode($request->input('civil_cases', '[]'), true) ?? [];

        $rating = $request->integer('owner_rating');
        if ($rating < 1 || $rating > 5) {
            $rating = null;
        }

        // ---- DB work only ----
        $submission = DB::transaction(function () use ($request, $reservation, $data, $civilCases, $existing, $rating) {

            if ($existing) {
                $existing->update([
                    'worker_reservation_id' => $reservation->reservation_id,
                    'event_id'              => $reservation->event_id,
                    'worker_id'             => $reservation->worker_id,
                    'work_role_id'          => $reservation->work_role_id,
                    'role_slug'             => $request->role_slug,
                    'general_notes'         => $request->input('general_notes'),
                    'data'                  => $data,
                    'status'                => 'pending',
                    'submitted_at'          => now(),
                    'owner_rating'          => $rating,
                ]);

                $existing->civilCases()->delete();
                $submission = $existing;
            } else {
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
                    'owner_rating'          => $rating,
                ]);
            }

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

            foreach ($request->allFiles() as $section => $files) {
                foreach ((array) $files as $file) {
                    $path = $file->store('post-event-submissions', 'public');

                    PostEventSubmissionFile::create([
                        'submission_id' => $submission->id,
                        'section'       => $section,
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getClientMimeType(),
                        'size_bytes'    => $file->getSize(),
                    ]);
                }
            }

            return $submission;
        });

        return response()->json([
            'ok'           => true,
            'submission'   => $submission->id,
            'updated'      => (bool) $existing,
            'owner_rating' => $submission->owner_rating,
        ]);
    }
}
