{{-- resources/views/worker/profile.blade.php --}}
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Profile • Volunteer</title>
  <script src="{{ asset('js/preferences.js') }}" defer></script>
  {{-- CSRF for AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- CSS --}}
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
</head>
@php
  $first    = trim($u->first_name ?? '');
  $last     = trim($u->last_name ?? '');
  $full     = method_exists($u,'getFullNameAttribute')
                ? ($u->full_name ?? '')
                : trim($first.' '.$last);
  $email    = $u->email ?? '';
  $role     = strtoupper($u->role ?? 'USER');
  $created  = optional($u->created_at)->format('d/m/Y, H:i') ?? '—';
  $lastIn   = optional($u->last_login_at)->format('d/m/Y, H:i') ?? '—';

  $initials = collect([$first, $last])
                ->filter(fn($p)=>!empty($p))
                ->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))
                ->implode('');
  if (!$initials) {
      $initials = collect(preg_split('/\s+/', trim($full ?? ''), -1, PREG_SPLIT_NO_EMPTY))
                    ->take(2)
                    ->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))
                    ->implode('') ?: 'U';
  }
@endphp

<body data-theme="dark">
  <div class="wrap">
    <!-- Top bar -->
    

    <!-- Header -->
    <header class="page-head">
      <div>
        <div class="crumbs">
          <a href="{{ route('worker.dashboard') }}"
             class="muted"
             style="text-decoration:none;color:inherit">Home</a> /
          <strong>Profile</strong>
        </div>
        <h1 class="title">My Profile</h1>
      </div>
    </header>

    <!-- ROW 1: Account + Photo -->
    <section class="row">
      <article class="card" aria-labelledby="accTitle">
        <h3 id="accTitle">Account</h3>

        <div class="form-row">
          <div class="form-col">
            <label for="firstName">First name</label>
            <input id="firstName" type="text" value="{{ old('first_name', $first) }}">
          </div>
          <div class="form-col">
            <label for="lastName">Last name</label>
            <input id="lastName" type="text" value="{{ old('last_name', $last) }}">
          </div>
        </div>

        <div class="form-row" style="margin-top:10px">
          <div class="form-col">
            <label for="email">Email</label>
            <input id="email" type="email" value="{{ old('email', $email) }}">
          </div>
          <div class="form-col">
            <label>Role</label>
            <div><span class="badge">{{ ucfirst(strtolower($role)) }}</span></div>
          </div>
        </div>

        <div class="actions">
          <button class="btn" id="saveAccount">Save changes</button>
          <button class="btn ghost" id="cancelAccount">Cancel</button>
        </div>
      </article>

      <article class="card" aria-labelledby="photoTitle">
        <h3 id="photoTitle">Profile Photo</h3>

        <div class="avatar-wrap">
          <img
            id="avatarImg"
            src="{{ !empty($u->avatar_path) ? \Illuminate\Support\Facades\Storage::url($u->avatar_path) : '' }}"
            alt="Avatar"
            class="avatar"
            style="object-fit:cover;border-radius:50%;width:96px;height:96px; {{ empty($u->avatar_path) ? 'display:none' : '' }}"
          >
          <div class="avatar"
               id="avatarInitials"
               aria-hidden="true"
               style="{{ !empty($u->avatar_path) ? 'display:none' : '' }}">
            {{ $initials }}
          </div>
        </div>

        <form id="avatarForm"
              class="upload-row"
              enctype="multipart/form-data"
              method="post"
              action="{{ route('profile.avatar') }}">
          @csrf
          <input id="photoFile" name="avatar" type="file" accept="image/*">
          <button type="submit" class="btn small" id="uploadPhoto">Upload</button>
        </form>
        <div class="muted" style="margin-top:8px">JPG/PNG, up to 2MB.</div>
      </article>
    </section>

    <!-- ROW 2: Account Info only -->
    <section class="full">
      <article class="card">
        <h3>Account Info</h3>
        <div class="info-list">
          
          <div class="info-row">
            <span class="info-label">Email</span>
            <span id="infoEmail">{{ $email }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Created</span><span>{{ $created }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Last login</span><span>{{ $lastIn }}</span>
          </div>
        </div>
      </article>
    </section>

    <!-- ROW 3: Password -->
    <section class="full">
      <article class="card" aria-labelledby="pwdTitle">
        <h3 id="pwdTitle">Change Password</h3>
        <div class="form-row">
          <div class="form-col">
            <label for="oldPass">Current password</label>
            <input id="oldPass" type="password" placeholder="••••••••">
          </div>
          <div class="form-col">
            <label for="newPass">New password</label>
            <input id="newPass" type="password" placeholder="New password">
          </div>
        </div>
        <div class="form-row" style="margin-top:10px">
          <div class="form-col">
            <label for="confirmPass">Confirm new password</label>
            <input id="confirmPass" type="password" placeholder="Confirm new password">
          </div>
        </div>
        <div class="actions">
          <button class="btn" id="updatePassword">Update password</button>
        </div>
      </article>
    </section>

    <!-- ROW 4: Personal -->
    <section class="full">
      <article class="card" aria-labelledby="piTitle">
        <h3 id="piTitle">Personal Information</h3>
        <div class="form-row">
          <div class="form-col">
            <label for="phone">Phone</label>
            <input id="phone" type="text" value="{{ old('phone', $u->phone ?? '') }}">
          </div>
          <div class="form-col">
            <label for="dob">Date of Birth</label>
            <input
              id="dob"
              type="date"
              max="{{ now()->toDateString() }}"
              value="{{ optional($u->date_of_birth)->format('Y-m-d') }}"
            >
          </div>
        </div>
        <div class="actions">
          <button class="btn" id="savePersonal">Save</button>
          <button class="btn ghost" id="cancelPersonal">Cancel</button>
        </div>
      </article>
    </section>
  </div>

  {{-- Pass routes for AJAX --}}
  <script>
    window.ROUTES = {
      account:  "{{ route('profile.account') }}",
      personal: "{{ route('profile.personal') }}",
      password: "{{ route('profile.password') }}"
    };
  </script>

  {{-- JS --}}
  <script src="{{ asset('js/profile.js') }}" defer></script>
  @include('notify.widget')
  <script src="{{ asset('js/notify-poll.js') }}" defer></script>

</body>
</html>
