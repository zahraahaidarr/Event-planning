<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Send Announcement</title>
  <link rel="stylesheet" href="{{ asset('css/announcements.css') }}">
</head>
<body>
  
<div class="container">
  <h1>📢 Send Announcement</h1>

  @if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert danger">
      <ul>
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('announcements.store') }}">
    @csrf

    <label>Title</label>
    <input type="text" name="title" value="{{ old('title') }}" required>

    <label>Description</label>
    <textarea name="body" required>{{ old('body') }}</textarea>

    @php
        // Your DB stores role as "ADMIN"
        $user = auth()->user();
        $roleValue = strtoupper($user->role ?? '');
        $isAdmin = ($roleValue === 'ADMIN');
    @endphp

    @if($isAdmin)
      <label>Audience</label>
      <select name="audience" required>
        <option value="workers"   {{ old('audience')==='workers' ? 'selected' : '' }}>Workers</option>
        <option value="employees" {{ old('audience')==='employees' ? 'selected' : '' }}>Employees</option>
        <option value="both"      {{ old('audience')==='both' ? 'selected' : '' }}>Both</option>
      </select>
    @else
      {{-- Employees cannot choose the audience --}}
      <input type="hidden" name="audience" value="workers">
      <div class="hint">Audience: Workers you manage</div>
    @endif

    <button type="submit">Send Announcement</button>
  </form>
</div>

<script src="{{ asset('js/announcements.js') }}"></script>
</body>
</html>
