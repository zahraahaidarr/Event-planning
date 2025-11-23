<!doctype html>
<html lang="en" dir="ltr" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Post-Event Submissions • Volunteer</title>
  <script src="{{ asset('js/preferences.js') }}" defer></script>
  {{-- CSS (public/css/worker/post-event-submission.css) --}}
  <link rel="stylesheet" href="{{ asset('css/worker/post-event-submission.css') }}">
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <div class="logo-icon">V</div>
        <span class="logo-text" id="brandName">VolunteerHub</span>
      </div>

      <nav class="nav-section">
        <div class="nav-label">Worker</div>
        <a href="{{ route('worker.dashboard') }}" class="nav-item">
          <span class="nav-icon">🏠</span>
          <span id="navDashboard">Dashboard</span>
        </a>
        <a href="{{ route('worker.events.discover') }}" class="nav-item">
          <span class="nav-icon">🗓️</span>
          <span id="navDiscover">Discover Events</span>
        </a>
        <a href="{{ route('worker.reservations') }}" class="nav-item">
          <span class="nav-icon">✅</span>
          <span id="navMyRes">My Reservations</span>
        </a>
        <a href="{{ route('worker.submissions') }}" class="nav-item active">
          <span class="nav-icon">📝</span>
          <span id="navSubmissions">Post-Event Submissions</span>
        </a>
      </nav>

      <nav class="nav-section">
        <div class="nav-label">Account</div>
        <a href="{{ route('profile') }}" class="nav-item">
          <span class="nav-icon">👤</span>
          <span id="navProfile">Profile</span>
        </a>
        <a href="{{ route('worker.messages') }}" class="nav-item">
          <span class="nav-icon">💬</span>
          <span id="navChat">Chat</span>
        </a>
        <a href="{{ route('worker.announcements.index') }}" class="nav-item">
          <span class="nav-icon">📢</span>
          <span id="navAnnouncements">Announcements</span>
        </a>
        <a href="{{ route('settings') }}" class="nav-item">
          <span class="nav-icon">⚙️</span>
          <span id="navSettings">Settings</span>
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="content" id="main">
      <!-- Top bar -->
      <div class="topbar">
        <div class="search" role="search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"
                  stroke="currentColor" stroke-width="1.6" opacity=".55" />
          </svg>
          <input id="globalSearch" placeholder="Search submissions…" aria-label="Search submissions"/>
        </div>
      </div>

      <!-- Page Header -->
      <section class="page-header">
        <h1 id="pageTitle">Post-Event Submissions</h1>
        <p id="pageSubtitle">
          Submit your post-event reports within 24 hours of event completion.
          Include photos, videos, and detailed descriptions.
        </p>
      </section>

      <!-- Submission Form -->
      <section class="form-card" id="submissionForm">
        <h2 id="formTitle" style="margin-top:0">Submit New Report</h2>

        <form id="reportForm"
              method="POST"
              action="{{ route('worker.submissions.store') }}"
              data-store-url="{{ route('worker.submissions.store') }}"
              enctype="multipart/form-data">
          @csrf
          <input type="hidden" id="submissionId" name="submission_id" value="">

          {{-- EVENT is chosen by worker --}}
          <div class="form-group">
            <label for="eventSelect">Select Event</label>
            <select id="eventSelect" name="worker_reservation_id" required>
              <option value="">Choose an event...</option>
              @foreach($reservations as $res)
                @php
                  $eventName = $res->event->name
                      ?? $res->event->title
                      ?? 'Event #'.$res->event_id;

                  $date = $res->event->start_time
                      ? $res->event->start_time->format('M d, Y')
                      : '';

                  $roleType   = optional(optional($res->workRole)->roleType);
                  $roleLabel  = $roleType->name ?? 'Unknown role';
                  $roleSlugDb = \Illuminate\Support\Str::slug($roleLabel, '_');
                @endphp

                <option value="{{ $res->reservation_id }}"
                        data-role-slug="{{ $roleSlugDb }}"
                        data-role-name="{{ $roleLabel }}">
                  {{ $eventName }}
                  @if($date) - {{ $date }} @endif
                  • Role: {{ $roleLabel }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- ROLE is derived from the reservation; worker cannot change it --}}
          <div class="form-group">
            <label>Your Role</label>
            <input id="roleLabel"
                   type="text"
                   readonly
                   placeholder="Choose an event first…">
          </div>

          <!-- ROLE-SPECIFIC FORMS -->
          <div id="roleForms">
            {{-- Organizer --}}
            <fieldset class="role-set" data-role="organizer" style="display:none">
              <legend>Organizer • Crowd control, order, entry flow</legend>
              <div class="two-col">
                <div class="form-group">
                  <label for="org_attendance">Mark attendance</label>
                  <select id="org_attendance">
                    <option value="" disabled selected>Select attendance…</option>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="partial">Partial</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="org_issues">Issues or incidents</label>
                <textarea id="org_issues"
                          placeholder="Fights, disorganization, entry bottlenecks..."></textarea>
              </div>
              <div class="form-group">
                <label for="org_improve">Suggestions for crowd management (optional)</label>
                <textarea id="org_improve"
                          placeholder="Improvements for future events…"></textarea>
              </div>
            </fieldset>

            {{-- Civil Defense --}}
            <fieldset class="role-set" data-role="civil" style="display:none">
              <legend>Civil Defense • Safety, first aid, emergency response</legend>
              <div class="two-col">
                <div class="form-group">
                  <label for="cd_check">Attendance</label>
                  <select id="cd_check">
                    <option value="" disabled selected>Select attendance…</option>
                    <option value="checked">Checked-in & out</option>
                    <option value="in">Checked-in only</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="cd_total_cases">Total cases handled</label>
                  <input id="cd_total_cases" type="number" min="0" value="0">
                </div>
              </div>

              <div class="form-group">
                <label>Case details</label>
                <div class="two-col" id="cd_cases_list"></div>
                <button type="button" class="btn small" id="cd_add_case">+ Add Case</button>
              </div>

              <div class="form-group">
                <label for="cd_concerns">Safety concerns / recommendations</label>
                <textarea id="cd_concerns" placeholder="Hazards noticed, recommendations…"></textarea>
              </div>

              <div class="form-group">
                <label for="cd_forms">Upload incident forms / documentation</label>
                <input id="cd_forms"
                       name="cd_forms[]"
                       type="file"
                       multiple
                       accept="image/*,application/pdf">
              </div>
            </fieldset>

            {{-- Media --}}
            <fieldset class="role-set" data-role="media" style="display:none">
              <legend>Media Staff • Photography, videography, coverage</legend>
              <div class="form-group">
                <label for="media_files">Upload event photos/videos</label>
                <input id="media_files"
                       name="media_files[]"
                       type="file"
                       multiple
                       accept="image/*,video/*">
              </div>
              <div class="two-col">
                <div class="form-group">
                  <label for="media_labels">Labels / categories</label>
                  <input id="media_labels" type="text"
                         placeholder='e.g., "Ashoura-Speaker", "Crowd"'>
                </div>
                <div class="form-group">
                  <label for="media_report_photos">Photos taken (count)</label>
                  <input id="media_report_photos" type="number" min="0" value="0">
                </div>
              </div>
              <div class="two-col">
                <div class="form-group">
                  <label for="media_report_videos">Videos taken (count)</label>
                  <input id="media_report_videos" type="number" min="0" value="0">
                </div>
                <div class="form-group">
                  <label for="media_problems">Media problems (optional)</label>
                  <input id="media_problems" type="text"
                         placeholder="Blurred images, camera issues…">
                </div>
              </div>
              <div class="form-group">
                <label for="media_captions">Captions / notes (optional)</label>
                <textarea id="media_captions" placeholder="Optional captions or notes…"></textarea>
              </div>
            </fieldset>

            {{-- Tech --}}
            <fieldset class="role-set" data-role="tech" style="display:none">
              <legend>Tech Support • Audio, video, projector, mic setup</legend>
              <div class="two-col">
                <div class="form-group">
                  <label for="tech_ok">All equipment functioning?</label>
                  <select id="tech_ok">
                    <option value="" disabled selected>Select option…</option>
                    <option value="yes">Yes, functioning</option>
                    <option value="partial">Partially</option>
                    <option value="no">No</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="tech_returned">Borrowed devices returned?</label>
                  <select id="tech_returned">
                    <option value="" disabled selected>Select option…</option>
                    <option value="yes">Yes</option>
                    <option value="partial">Partially</option>
                    <option value="no">No</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="tech_issues">Tech issues (include timestamps)</label>
                <textarea id="tech_issues"
                          placeholder="e.g., Mic failed at 7:20 PM, projector reboot at 8:05 PM…"></textarea>
              </div>
              <div class="form-group">
                <label for="tech_recording">Upload recorded session (if applicable)</label>
                <input id="tech_recording"
                       name="tech_recording"
                       type="file"
                       accept="video/*,audio/*">
              </div>
              <div class="form-group">
                <label for="tech_suggest">Technical improvements (optional)</label>
                <textarea id="tech_suggest"
                          placeholder='e.g., "Need backup mic", extra HDMI cable…'></textarea>
              </div>
            </fieldset>

            {{-- Cleaner --}}
            <fieldset class="role-set" data-role="cleaner" style="display:none">
              <legend>Cleaner • Clean event location before/after</legend>
              <div class="two-col">
                <div class="form-group">
                  <label for="clean_zones">Task completion (zones cleaned)</label>
                  <input id="clean_zones" type="number" min="0" placeholder="e.g., 5">
                </div>
                <div class="form-group">
                  <label for="clean_extra">Was extra help needed?</label>
                  <select id="clean_extra">
                    <option value="" disabled selected>Select option…</option>
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="clean_notes">Trash volume / forgotten items</label>
                <textarea id="clean_notes"
                          placeholder="Notes on trash volume, lost & found…"></textarea>
              </div>
              <div class="form-group">
                <label for="clean_suggest">Logistics suggestions</label>
                <textarea id="clean_suggest"
                          placeholder="Better placement of bins, more time between sessions…"></textarea>
              </div>
            </fieldset>

            {{-- Decorator --}}
            <fieldset class="role-set" data-role="decorator" style="display:none">
              <legend>Decorator • Setup/teardown of decorations</legend>
              <div class="form-group">
                <label for="dec_photos">Upload photos (setup & final layout)</label>
                <input id="dec_photos"
                       name="dec_photos[]"
                       type="file"
                       multiple
                       accept="image/*">
              </div>
              <div class="two-col">
                <div class="form-group">
                  <label for="dec_used">Decorations used</label>
                  <textarea id="dec_used" placeholder="List items used…"></textarea>
                </div>
                <div class="form-group">
                  <label for="dec_damaged">Damaged / missing</label>
                  <textarea id="dec_damaged" placeholder="Items damaged or missing…"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label for="dec_replace">To replace / repair</label>
                <textarea id="dec_replace" placeholder="What needs replacement or repair…"></textarea>
              </div>
              <div class="form-group">
                <label for="dec_feedback">Time/space/design challenges</label>
                <textarea id="dec_feedback" placeholder="Any challenges encountered…"></textarea>
              </div>
            </fieldset>

            {{-- Cooking --}}
            <fieldset class="role-set" data-role="cooking" style="display:none">
              <legend>Cooking Team • Food prep, serving, hygiene</legend>
              <div class="form-group">
                <label for="cook_meals">Meals served & quantity breakdown</label>
                <textarea id="cook_meals"
                          placeholder="e.g., Rice plates: 150, Sandwiches: 80…"></textarea>
              </div>
              <div class="two-col">
                <div class="form-group">
                  <label for="cook_ingredients">Ingredients / donations used</label>
                  <textarea id="cook_ingredients"
                            placeholder="Key ingredients or donated items…"></textarea>
                </div>
                <div class="form-group">
                  <label for="cook_leftovers">Leftovers / shortages</label>
                  <textarea id="cook_leftovers"
                            placeholder="What remained or ran out…"></textarea>
                </div>
              </div>
              <div class="two-col">
                <div class="form-group">
                  <label for="cook_hygiene">Cleaning / hygiene issues</label>
                  <textarea id="cook_hygiene"
                            placeholder="Sanitation notes, issues observed…"></textarea>
                </div>
                <div class="form-group">
                  <label for="cook_photos">Upload photos (serving area)</label>
                  <input id="cook_photos"
                         name="cook_photos[]"
                         type="file"
                         multiple
                         accept="image/*">
                </div>
              </div>
            </fieldset>

            {{-- Waiter --}}
            <fieldset class="role-set" data-role="waiter" style="display:none">
              <legend>Waiter</legend>
              <div class="two-col">
                <div class="form-group">
                  <label for="wait_attendance">Mark attendance</label>
                  <select id="wait_attendance">
                    <option value="" disabled selected>Select attendance…</option>
                    <option value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="partial">Partial</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="wait_leftovers">Leftovers / Waste (optional)</label>
                  <textarea id="wait_leftovers"
                            placeholder="Notes on leftovers or waste…"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label for="wait_items">List of items served</label>
                <textarea id="wait_items"
                          placeholder="e.g., Water 200, Juice 120, Plates 180…"></textarea>
              </div>
              <div class="form-group">
                <label for="wait_issues">Service issues</label>
                <textarea id="wait_issues"
                          placeholder="Delays, missing tools, queue issues…"></textarea>
              </div>
            </fieldset>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn" id="submitBtn">Submit Report</button>
            <button type="reset" class="btn ghost" id="resetBtn">Clear Form</button>
          </div>
        </form>
      </section>

      <!-- Previous Submissions -->
      <section>
        <h2>Previous Submissions</h2>
        <div class="list" id="submissionsList">
          @forelse ($submissions as $sub)
            @php
              $eventName = $sub->event->name
                ?? $sub->event->title
                ?? ('Event #'.$sub->event_id);

              $submittedAt = ($sub->submitted_at ?? $sub->created_at)
                ? ($sub->submitted_at ?? $sub->created_at)->format('d M Y, H:i')
                : '—';

              switch ($sub->status) {
                  case 'submitted':
                  case 'reviewed':
                      $statusLabel = 'Submitted';
                      $chipClass   = 'chip-submitted';
                      break;
                  default:
                      $statusLabel = 'Pending Review';
                      $chipClass   = 'chip-pending';
              }

              // Editable for 24h while pending
              $canEdit = $sub->submitted_at &&
                         $sub->submitted_at->gt(now()->subDay()) &&
                         $sub->status === 'pending';

              $civilCasesData = $sub->civilCases->map(function ($c) {
                return [
                    'type'   => $c->case_type,
                    'age'    => $c->age,
                    'gender' => $c->gender,
                    'action' => $c->action_taken,
                    'notes'  => $c->notes,
                ];
              });
            @endphp

            <article class="card"
                     data-sub-id="{{ $sub->id }}"
                     data-res-id="{{ $sub->worker_reservation_id }}"
                     data-role-slug="{{ $sub->role_slug }}"
                     data-can-edit="{{ $canEdit ? '1' : '0' }}"
                     data-data='@json($sub->data ?? [])'
                     data-civil='@json($civilCasesData)'>
              <div class="card-header">
                <div class="card-title">{{ $eventName }}</div>
                <span class="chip-status {{ $chipClass }}">{{ $statusLabel }}</span>
              </div>

              <div class="meta">
                <span>📅 Submitted: {{ $submittedAt }}</span>
                @if($canEdit)
                  <span>🕒 Editable for 24h</span>
                @endif
              </div>

              <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button class="btn small ghost"
                        type="button"
                        data-act="view"
                        data-id="{{ $sub->id }}">
                  View Report
                </button>
                @if($canEdit)
                  <span class="hint-editable">You can still edit this report.</span>
                @else
                  <span class="hint-locked">View only (locked).</span>
                @endif
              </div>
            </article>
          @empty
            <div style="text-align:center;padding:40px;color:var(--muted)">
              No submissions yet.
            </div>
          @endforelse
        </div>
      </section>

    </main>
  </div>

  <!-- Confirm submit modal -->
  <div id="confirmModal" class="modal-backdrop" style="display:none">
    <div class="modal-dialog">
      <h3>Submit report?</h3>
      <p>
        Once you submit this report, you can edit it for the next 24 hours while it is still pending.
        Are you sure you want to continue?
      </p>
      <div class="modal-actions">
        <button type="button" id="confirmSubmit" class="btn">Yes, submit</button>
        <button type="button" id="cancelSubmit" class="btn ghost">Cancel</button>
      </div>
    </div>
  </div>

  {{-- JS (public/js/worker/post-event-submission.js) --}}
  <script src="{{ asset('js/worker/post-event-submission.js') }}" defer></script>
</body>
</html>
