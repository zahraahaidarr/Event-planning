{{-- resources/views/auth/register.blade.php --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      data-theme="dark">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>{{ config('app.name','VolunteerHub') }} • @lang('Register')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    /* Tokens + base styles identical to login for perfect homogeneity */
    :root{ --bg:#0f1222; --surface:#14183a; --surface-2:#0e1330; --card:#171c42; --text:#eaf0ff;
      --muted:#aab3d6; --primary:#4f7cff; --primary-2:#7aa0ff; --accent:#9c6cff; --success:#36d399;
      --warning:#f4bf50; --danger:#ff6b6b; --ring:rgba(111,140,255,.55); --radius:18px; --shadow:0 10px 30px rgba(0,0,0,.25);
      --bg-primary:var(--surface); --bg-secondary:var(--surface-2); --bg-tertiary:var(--card); --text-primary:var(--text);
      --text-secondary:var(--muted); --text-tertiary:#7a84b0; --border-color:rgba(255,255,255,.08);
      --accent-primary:var(--primary); --accent-secondary:var(--accent); --shadow-sm:0 4px 12px rgba(0,0,0,.15);
      --shadow-md:0 8px 20px rgba(0,0,0,.2); --shadow-lg:var(--shadow); --radius-sm:10px; --radius-md:14px }
    [data-theme="light"]{ --bg:#f6f7fb; --surface:#fff; --surface-2:#f2f5ff; --card:#fff; --text:#12152b; --muted:#5a6387;
      --primary:#3a66ff; --primary-2:#6f90ff; --accent:#7b5cff; --success:#16a34a; --warning:#d97706; --danger:#ef4444;
      --ring:rgba(58,102,255,.35); --shadow:0 10px 24px rgba(13,24,61,.10); --bg-primary:var(--surface); --bg-secondary:var(--surface-2);
      --bg-tertiary:var(--card); --text-primary:var(--text); --text-secondary:var(--muted); --text-tertiary:#7a84b0; --border-color:#e0e5f5 }
    *{box-sizing:border-box} html,body{height:100%}
    body{ margin:0; font:14px/1.55 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial; color:var(--text);
      background: radial-gradient(1200px 800px at -10% -10%, #1a2050 0%, var(--bg) 55%), radial-gradient(1200px 700px at 110% -10%, #281a55 0%, transparent 50%), var(--bg) }
    .wrap{ min-height:100vh; display:grid; place-items:center; padding:24px }
    .card{ width:min(720px,100%); background:var(--card); border-radius:22px; box-shadow:var(--shadow); padding:24px; position:relative; overflow:hidden }
    .card::before{ content:""; position:absolute; inset:-60px -40px auto auto; width:240px; height:240px; border-radius:50%;
      background:radial-gradient(closest-side, rgba(79,124,255,.20), transparent); filter:blur(10px) }
    .head{ display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px }
    .brand{ display:flex; align-items:center; gap:10px; font-weight:800 }
    .logo{ width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff;
      background:linear-gradient(135deg,var(--primary),var(--accent)); font-weight:800 }
    .title{ margin:0; font-size:22px } .muted{ color:var(--muted) }
    .grid{ display:grid; gap:12px; grid-template-columns:repeat(2,minmax(0,1fr)) }
    @media (max-width:560px){ .grid{ grid-template-columns:1fr } }
    .field label{ display:block; font-weight:600; margin:8px 0 6px }
    .input{ width:100%; padding:12px 14px; border-radius:12px; border:0; background:var(--surface-2); color:var(--text); outline:1px solid var(--border-color) }
    .row{ display:flex; gap:10px; align-items:center }
    .btn{ appearance:none; border:0; border-radius:12px; padding:12px 14px; cursor:pointer; font-weight:700; color:#fff; background:var(--primary); box-shadow:0 8px 18px rgba(79,124,255,.35) }
    .btn.ghost{ background:transparent; color:var(--text); outline:1px solid var(--border-color) }
    .btn.full{ width:100% }
    a{ color:var(--primary-2); text-decoration:none } a:hover{text-decoration:underline}
    .footer-note{ text-align:center; margin-top:10px; color:var(--text-secondary) }

    .certificate-bar { margin-top:32px; background:var(--surface-2); border-radius:16px; padding:24px; box-shadow:var(--shadow-sm); border:1px solid var(--border-color) }
    .bar-title { font-size:18px; font-weight:700; margin-bottom:6px; color:var(--text-primary) }
    .bar-desc { font-size:14px; color:var(--text-secondary); margin-bottom:16px }
    .bar-form { display:flex; flex-wrap:wrap; gap:12px; align-items:center }
    .upload-field { flex:1 }
    .upload-field label { display:block; font-size:13px; font-weight:600; margin-bottom:6px }
    .upload-field input[type="file"] { width:100%; padding:10px; border-radius:var(--radius-md); background:var(--surface); color:var(--text); border:1px solid var(--border-color) }

    .error{ color:var(--danger); font-size:12px; margin-top:6px }
    .status{ background:rgba(79,124,255,.12); color:var(--text); padding:10px 12px; border-radius:12px; margin:8px 0; }
      /* put this with your existing styles */
.card::before{
  pointer-events: none;   /* <-- allow clicks to pass through */
  z-index: 0;             /* keep it behind real content */
}

.head{ 
  position: relative; 
  z-index: 1;             /* ensure header is above any backgrounds */
}

    @media (max-width:560px){ .bar-form{ flex-direction:column; align-items:stretch } }
  </style>
</head>
<body>
  <div class="wrap">
    <article class="card" aria-labelledby="regTitle">
      <header class="head">
        <div class="brand">
          <div class="logo">V</div>
          <div>
            <div style="font-weight:800">{{ config('app.name','VolunteerHub') }}</div>
            <div class="muted" id="regDesc">@lang('Create your volunteer account')</div>
          </div>
        </div>
        <div class="row">
          <button class="btn ghost" id="langToggle" type="button">EN/AR</button>
          <button class="btn ghost" id="themeToggle" type="button">🌓</button>
        </div>
      </header>

      <h1 class="title" id="regTitle">@lang('Register')</h1>

      {{-- Session status (optional) --}}
      @if (session('status'))
        <div class="status" role="status">{{ session('status') }}</div>
      @endif

      {{-- MAIN FORM (single form, supports file upload) --}}
      <form id="regForm" method="POST" action="{{ route('register') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="grid">
          <div class="field">
            <label for="first">@lang('First Name')</label>
            <input class="input" id="first" name="first_name" value="{{ old('first_name') }}" required placeholder="Fatima"/>
            @error('first_name') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label for="last">@lang('Last Name')</label>
            <input class="input" id="last" name="last_name" value="{{ old('last_name') }}" required placeholder="Hassan"/>
            @error('last_name') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label for="email">@lang('Email')</label>
            <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com" autocomplete="username"/>
            @error('email') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label for="phone">@lang('Phone')</label>
            <input class="input" id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="+961 xx xxx xxx"/>
            @error('phone') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label for="city">@lang('City')</label>
            <input class="input" id="city" name="city" value="{{ old('city') }}" placeholder="Beirut"/>
            @error('city') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
  <label for="role">@lang('Role')</label>
  <select class="input" id="role" name="role_type_id" required>
    <option value="">@lang('Select…')</option>
    @forelse ($roleTypes as $rt)
      <option value="{{ $rt->role_type_id }}" @selected(old('role_type_id') == $rt->role_type_id)>
        {{ $rt->name }}
      </option>
    @empty
      <option value="" disabled>@lang('No roles available')</option>
    @endforelse
  </select>
  @error('role_type_id') <div class="error">{{ $message }}</div> @enderror
</div>


          <div class="field">
            <label for="pass">@lang('Password')</label>
            <input class="input" id="pass" type="password" name="password" required minlength="6" placeholder="••••••••" autocomplete="new-password"/>
            @error('password') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="field">
            <label for="confirm">@lang('Confirm Password')</label>
            <input class="input" id="confirm" type="password" name="password_confirmation" required minlength="6" placeholder="••••••••" autocomplete="new-password"/>
          </div>
        </div>

        {{-- Certificate upload (part of the same form) --}}
        <section class="certificate-bar">
          <h2 class="bar-title">@lang('Upload Your Volunteer Certificates')</h2>
          <p class="bar-desc">@lang('If you already have participation certificates, you can upload them here to verify your experience.')</p>

          <div class="bar-form">
            <div class="upload-field">
              <label for="certificateFile">@lang('Choose File')</label>
              <input type="file" id="certificateFile" name="certificate" accept=".pdf,.jpg,.jpeg,.png"/>
              @error('certificate') <div class="error">{{ $message }}</div> @enderror
            </div>
          </div>
        </section>

        <div class="row" style="justify-content:space-between; margin-top:12px">
          <label class="row" style="font-size:13px;color:var(--text-secondary)">
            <input type="checkbox" id="tos" name="terms" {{ old('terms') ? 'checked' : '' }} required/>
            <span id="tosText">@lang('I agree to the Terms')</span>
          </label>

          @if (Route::has('login'))
            <a href="{{ route('login') }}" id="toLogin">@lang('Have an account? Login')</a>
          @endif
        </div>

        <button class="btn full" type="submit" id="regBtn" style="margin-top:12px">@lang('Create Account')</button>
      </form>

      <p class="footer-note">@lang('Your information helps us match you with the right opportunities.')</p>
    </article>
  </div>

  {{-- JS (verbatim so Blade won’t parse it) --}}
  <script>
  @verbatim
    const STR = {
      en:{ title:'Register', desc:'Create your volunteer account', first:'First Name', last:'Last Name',
        email:'Email', phone:'Phone', city:'City', role:'Role', pass:'Password', confirm:'Confirm Password',
        tos:'I agree to the Terms', have:'Have an account? Login', create:'Create Account' },
      ar:{ title:'إنشاء حساب', desc:'أنشئ حسابك كمتطوّع', first:'الاسم الأول', last:'اسم العائلة',
        email:'البريد الإلكتروني', phone:'الهاتف', city:'المدينة', role:'الدور', pass:'كلمة المرور', confirm:'تأكيد كلمة المرور',
        tos:'أوافق على الشروط', have:'لديك حساب؟ تسجيل الدخول', create:'إنشاء الحساب' }
    };
    let lang = document.documentElement.getAttribute('dir') === 'rtl' ? 'ar' : 'en';

    function t(){
      const s=STR[lang];
      document.documentElement.dir = (lang==='ar')?'rtl':'ltr';
      regTitle.textContent=s.title; regDesc.textContent=s.desc;
      document.querySelector('label[for="first"]').textContent=s.first;
      document.querySelector('label[for="last"]').textContent=s.last;
      document.querySelector('label[for="email"]').textContent=s.email;
      document.querySelector('label[for="phone"]').textContent=s.phone;
      document.querySelector('label[for="city"]').textContent=s.city;
      document.querySelector('label[for="role"]').textContent=s.role;
      document.querySelector('label[for="pass"]').textContent=s.pass;
      document.querySelector('label[for="confirm"]').textContent=s.confirm;
      document.getElementById('tosText').textContent=s.tos;
      document.getElementById('toLogin').textContent=s.have;
      document.getElementById('regBtn').textContent=s.create;
    }
    langToggle.onclick=()=>{ lang=(lang==='en')?'ar':'en'; t(); };
    themeToggle.onclick=()=>{ const html=document.documentElement; html.setAttribute('data-theme', html.getAttribute('data-theme')==='light'?'dark':'light'); };

    // Client-side checks (server will validate again)
    regForm.addEventListener('submit',(e)=>{
      const pass = document.getElementById('pass').value;
      const confirm = document.getElementById('confirm').value;
      const tos = document.getElementById('tos').checked;
      if(pass !== confirm){ e.preventDefault(); alert('Passwords do not match'); return; }
      if(!tos){ e.preventDefault(); alert('Please agree to the Terms'); return; }
    });
    t();
  @endverbatim
  </script>
</body>
</html>
