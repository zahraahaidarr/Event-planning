{{-- resources/views/auth/login.blade.php --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      data-theme="dark">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>{{ config('app.name', 'VolunteerHub') }} • @lang('Login')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    /* ===== Design Tokens (copied from your project) ===== */
    :root{
      --bg:#0f1222; --surface:#14183a; --surface-2:#0e1330; --card:#171c42;
      --text:#eaf0ff; --muted:#aab3d6; --primary:#4f7cff; --primary-2:#7aa0ff;
      --accent:#9c6cff; --success:#36d399; --warning:#f4bf50; --danger:#ff6b6b;
      --ring:rgba(111,140,255,.55); --radius:18px; --shadow:0 10px 30px rgba(0,0,0,.25);
      --bg-primary: var(--surface); --bg-secondary: var(--surface-2); --bg-tertiary: var(--card);
      --text-primary: var(--text); --text-secondary: var(--muted); --text-tertiary: #7a84b0;
      --border-color: rgba(255,255,255,.08); --accent-primary: var(--primary); --accent-secondary: var(--accent);
      --shadow-sm: 0 4px 12px rgba(0,0,0,.15); --shadow-md: 0 8px 20px rgba(0,0,0,.2); --shadow-lg: var(--shadow);
      --radius-sm: 10px; --radius-md: 14px;
    }
    [data-theme="light"]{
      --bg:#f6f7fb; --surface:#ffffff; --surface-2:#f2f5ff; --card:#ffffff; --text:#12152b; --muted:#5a6387;
      --primary:#3a66ff; --primary-2:#6f90ff; --accent:#7b5cff; --success:#16a34a; --warning:#d97706; --danger:#ef4444;
      --ring:rgba(58,102,255,.35); --shadow:0 10px 24px rgba(13,24,61,.10);
      --bg-primary: var(--surface); --bg-secondary: var(--surface-2); --bg-tertiary: var(--card);
      --text-primary: var(--text); --text-secondary: var(--muted); --text-tertiary: #7a84b0; --border-color: #e0e5f5;
      --accent-primary: var(--primary); --accent-secondary: var(--accent); --shadow-sm:0 2px 8px rgba(13,24,61,.08);
      --shadow-md:0 4px 16px rgba(13,24,61,.12); --shadow-lg: var(--shadow);
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font:14px/1.55 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial; color:var(--text);
      background: radial-gradient(1200px 800px at -10% -10%, #1a2050 0%, var(--bg) 55%),
                 radial-gradient(1200px 700px at 110% -10%, #281a55 0%, transparent 50%),
                 var(--bg);
    }

    /* ===== Auth Layout ===== */
    .wrap{
      min-height:100vh; display:grid; place-items:center; padding:24px;
    }
    .card{
      width:min(560px,100%); background:var(--card); border-radius:22px; box-shadow:var(--shadow);
      padding:24px; position:relative; overflow:hidden;
    }
    .card::before{
      content:""; position:absolute; inset:-80px -40px auto auto; width:220px; height:220px; border-radius:50%;
      background:radial-gradient(closest-side, rgba(156,108,255,.25), transparent);
      filter:blur(10px);
    }
    .head{
      display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px;
    }
    .brand{ display:flex; align-items:center; gap:10px; font-weight:800; }
    .logo{ width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center;
      color:#fff; background:linear-gradient(135deg,var(--primary),var(--accent)); font-weight:800 }
    .title{ margin:0; font-size:22px }
    .muted{ color:var(--muted) }

    .form{ display:grid; gap:12px; margin-top:6px }
    .field label{ display:block; font-weight:600; margin:8px 0 6px }
    .input{
      width:100%; padding:12px 14px; border-radius:12px; border:0; background:var(--surface-2); color:var(--text);
      outline:1px solid var(--border-color);
    }
    .row{ display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .check{ display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-secondary) }
    .actions{ display:flex; gap:10px; margin-top:6px }
    .btn{
      appearance:none; border:0; border-radius:12px; padding:12px 14px; cursor:pointer; font-weight:700;
      color:#fff; background:var(--primary); box-shadow:0 8px 18px rgba(79,124,255,.35);
    }
    .btn.ghost{ background:transparent; color:var(--text); outline:1px solid var(--border-color) }
    .btn.full{ width:100% }
    .links{ display:flex; justify-content:space-between; gap:10px; margin-top:12px; align-items:center; }
    a{ color:var(--primary-2); text-decoration:none }
    a:hover{ text-decoration:underline }
    .sep{ display:flex; align-items:center; gap:10px; margin:8px 0 }
    .sep span{ height:1px; flex:1; background:var(--border-color) }

    .footer-note{ text-align:center; margin-top:10px; color:var(--text-secondary) }

    /* Small helpers */
    .pill{ display:inline-block; padding:6px 10px; border-radius:999px; background:var(--surface-2); color:var(--muted); font-size:12px }

    /* Error + status styles */
    .error{ color: var(--danger); font-size:12px; margin-top:6px }
    .status{ background:rgba(79,124,255,.12); color:var(--text); padding:10px 12px; border-radius:12px; margin:8px 0; }

    @media (max-width:520px){ .links{flex-direction:column; align-items:flex-start} }
  </style>
</head>
<body>
  <div class="wrap">
    <article class="card" role="dialog" aria-labelledby="authTitle" aria-describedby="authDesc">
      <header class="head">
        <div class="brand">
          <div class="logo">V</div>
          <div>
            <div style="font-weight:800">{{ config('app.name', 'VolunteerHub') }}</div>
            <div class="muted" id="authDesc">@lang('Sign in to continue')</div>
          </div>
        </div>
        <div class="actions">
          {{-- If you have locale routes, point this button(s) to them. Keeping client toggle too. --}}
          <button class="btn ghost" id="langToggle" type="button" title="Language">EN/AR</button>
          <button class="btn ghost" id="themeToggle" type="button" title="Theme">🌓</button>
        </div>
      </header>

      <h1 class="title" id="authTitle">@lang('Login')</h1>
      <p class="muted">@lang('Use your email and password')</p>

      {{-- Session status (e.g., password reset link sent) --}}
      @if (session('status'))
        <div class="status" role="status">
          {{ session('status') }}
        </div>
      @endif

      <form class="form" id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="field">
          <label for="email">@lang('Email')</label>
          <input class="input @error('email') is-invalid @enderror"
                 id="email" type="email" name="email"
                 value="{{ old('email') }}" required autofocus
                 placeholder="you@example.com"
                 autocomplete="username"/>
          @error('email')
            <div class="error" role="alert">{{ $message }}</div>
          @enderror
        </div>

        <div class="field">
          <label for="password">@lang('Password')</label>
          <input class="input @error('password') is-invalid @enderror"
                 id="password" type="password" name="password"
                 required minlength="6"
                 placeholder="••••••••"
                 autocomplete="current-password"/>
          @error('password')
            <div class="error" role="alert">{{ $message }}</div>
          @enderror
        </div>

        <div class="row">
          <label class="check">
            <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}/>
            <span id="rememberLabel">@lang('Remember me')</span>
          </label>

          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" id="forgotLink">@lang('Forgot password?')</a>
          @endif
        </div>

        <button class="btn full" type="submit" id="loginBtn">@lang('Sign In')</button>
      </form>

      <div class="sep" aria-hidden="true"><span></span><small class="muted">@lang('or')</small><span></span></div>

      <div class="links">
        <span class="muted" id="newHereText">@lang('New here?')</span>
        @if (Route::has('register'))
          <a href="{{ route('register') }}" id="toRegister">@lang('Create an account')</a>
        @endif
      </div>

      <p class="footer-note">
        <span class="pill">@lang('Volunteer • Secure Sign-in')</span>
      </p>
    </article>
  </div>

<script>
@verbatim
  // Persist theme across visits
  (function initTheme(){
    try{
      const saved = localStorage.getItem('vh-theme');
      if(saved){ document.documentElement.setAttribute('data-theme', saved); }
    }catch(e){}
  })();

  // EN/AR strings (client-side toggle without reloading)
  const STR = {
    en:{ title:'Login', desc:'Sign in to continue', email:'Email', pass:'Password',
        placeholderEmail:'you@example.com', remember:'Remember me', forgot:'Forgot password?',
        signIn:'Sign In', newHere:'New here?', create:'Create an account', or:'or',
        secure:'Volunteer • Secure Sign-in' },
    ar:{ title:'تسجيل الدخول', desc:'سجّل دخولك للمتابعة', email:'البريد الإلكتروني', pass:'كلمة المرور',
        placeholderEmail:'you@example.com', remember:'تذكّرني', forgot:'نسيت كلمة المرور؟',
        signIn:'تسجيل الدخول', newHere:'مستخدم جديد؟', create:'إنشاء حساب', or:'أو',
        secure:'تسجيل آمن • متطوّع' }
  };

  // Start with app locale direction; default labels come from server translations already
  let lang = document.documentElement.getAttribute('dir') === 'rtl' ? 'ar' : 'en';

  function applyI18n(){
    const s = STR[lang];
    document.documentElement.dir = (lang==='ar')?'rtl':'ltr';
    document.getElementById('authTitle').textContent = s.title;
    document.getElementById('authDesc').textContent = s.desc;
    document.querySelector('label[for="email"]').textContent = s.email;
    document.querySelector('label[for="password"]').textContent = s.pass;
    const emailEl = document.getElementById('email');
    if(emailEl && !emailEl.value){ emailEl.placeholder = s.placeholderEmail; }
    document.getElementById('rememberLabel').textContent = s.remember;
    const forgot = document.getElementById('forgotLink'); if(forgot) forgot.textContent = s.forgot;
    document.getElementById('loginBtn').textContent = s.signIn;
    const newHereText = document.getElementById('newHereText'); if(newHereText) newHereText.textContent = s.newHere;
    const toRegister = document.getElementById('toRegister'); if(toRegister) toRegister.textContent = s.create;
    const sep = document.querySelector('.sep small'); if(sep) sep.textContent = s.or;
    const pill = document.querySelector('.footer-note .pill'); if(pill) pill.textContent = s.secure;
  }

  document.getElementById('langToggle').onclick = ()=>{
    lang = (lang==='en')?'ar':'en';
    applyI18n();
  };

  document.getElementById('themeToggle').onclick = ()=>{
    const html=document.documentElement;
    const next = html.getAttribute('data-theme')==='light' ? 'dark' : 'light';
    html.setAttribute('data-theme', next);
    try{ localStorage.setItem('vh-theme', next); }catch(e){}
  };

  // Leave disabled by default so server-translated text remains until user toggles language
  // applyI18n();
@endverbatim
</script>

</body>
</html>
