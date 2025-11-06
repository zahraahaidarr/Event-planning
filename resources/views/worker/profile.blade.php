<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Profile • Volunteer</title>

  {{-- CSS (public/css/worker/profile.css) --}}
  <link rel="stylesheet" href="{{ asset('css/worker/profile.css') }}">
</head>
<body data-theme="dark">
  <div class="wrap">
    <!-- Top bar -->
    <div class="topbar">
      <div class="search" role="search">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="m21 21-4.2-4.2M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke="currentColor" stroke-width="1.6" opacity=".55"/></svg>
        <input id="globalSearch" placeholder="Search..." aria-label="Search">
      </div>
      <div class="bar-actions">
        <button class="btn ghost" id="langToggle" title="Switch Language">EN/AR</button>
        <button class="btn ghost" id="themeToggle" title="Toggle Theme">🌓</button>
      </div>
    </div>

    <!-- Header -->
    <header class="page-head">
      <div>
        <div class="crumbs"><a href="{{ route('worker.dashboard') }}" class="muted" style="text-decoration:none;color:inherit">Home</a> / <strong>Profile</strong></div>
        <h1 class="title">My Profile</h1>
      </div>
    </header>

    <!-- ROW 1: Account | Profile Photo -->
    <section class="row">
      <article class="card" aria-labelledby="accTitle">
        <h3 id="accTitle">Account</h3>
        <div class="form-row">
          <div class="form-col">
            <label for="fullName">Full name</label>
            <input id="fullName" type="text" value="Fatima Mohammed Al-Hassan">
          </div>
          <div class="form-col">
            <label for="userName">User name</label>
            <input id="userName" type="text" value="fatima@email.com" disabled>
          </div>
        </div>

        <div class="form-row" style="margin-top:10px">
          <div class="form-col">
            <label for="email">Email</label>
            <input id="email" type="email" value="fatima@email.com">
          </div>
          <div class="form-col">
            <label>Roles</label>
            <div><span class="badge">User</span></div>
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
          <div class="avatar" id="avatarInitials" aria-hidden="true">FM</div>
        </div>
        <div class="upload-row">
          <input id="photoFile" type="file" accept="image/*">
          <button class="btn small" id="uploadPhoto">Upload</button>
        </div>
        <div class="muted" style="margin-top:8px">JPG/PNG, up to 2MB.</div>
      </article>
    </section>

    <!-- ROW 2: Certificates (fixed height) | Account Info -->
    <section class="row">
      <article class="card card--cert" aria-labelledby="certsTitle">
        <h3 id="certsTitle">Certificates & Achievements</h3>
        <div class="scroll">
          <div class="info-list" id="certList">
            <div class="info-row">
              <div>
                <div><strong>Media Champion</strong></div>
                <div class="muted">Completed 10+ media coverage events • Issued Dec 2024</div>
              </div>
              <span class="badge">Verified</span>
            </div>
            <div class="info-row">
              <div>
                <div><strong>Community Leader</strong></div>
                <div class="muted">Led 3 community initiatives • Issued Nov 2024</div>
              </div>
              <span class="badge">Verified</span>
            </div>
            <div class="info-row">
              <div>
                <div><strong>First Aid Certified</strong></div>
                <div class="muted">Red Cross Training • Issued Oct 2024</div>
              </div>
              <span class="badge">Verified</span>
            </div>
          </div>
        </div>
      </article>

      <article class="card" aria-labelledby="infoTitle">
        <h3 id="infoTitle">Account Info</h3>
        <div class="info-list">
          <div class="info-row"><span class="info-label">User ID</span><span>7</span></div>
          <div class="info-row"><span class="info-label">Email</span><span id="infoEmail">fatima@email.com</span></div>
          <div class="info-row"><span class="info-label">Created</span><span>01/01/2024, 09:00</span></div>
          <div class="info-row"><span class="info-label">Last login</span><span>—</span></div>
        </div>
      </article>
    </section>

    <!-- ROW 3: Change Password (full width) -->
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

    <!-- ROW 4: Personal Information (full width) -->
    <section class="full">
      <article class="card" aria-labelledby="piTitle">
        <h3 id="piTitle">Personal Information</h3>
        <div class="form-row">
          <div class="form-col">
            <label for="phone">Phone</label>
            <input id="phone" type="text" value="+961 70 123 456">
          </div>
          <div class="form-col">
            <label for="location">Location</label>
            <input id="location" type="text" value="Beirut, Lebanon">
          </div>
        </div>
        <div class="form-row" style="margin-top:10px">
          <div class="form-col">
            <label for="dob">Date of Birth</label>
            <input id="dob" type="text" value="March 12, 1998">
          </div>
          <div class="form-col">
            <label for="memberSince">Member Since</label>
            <input id="memberSince" type="text" value="January 2024">
          </div>
        </div>
        <div class="actions">
          <button class="btn" id="savePersonal">Save</button>
          <button class="btn ghost" id="cancelPersonal">Cancel</button>
        </div>
      </article>
    </section>
  </div>

  {{-- JS (public/js/worker/profile.js) --}}
  <script src="{{ asset('js/worker/profile.js') }}" defer></script>
</body>
</html>
