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
    [data-theme="light"]{ --bg:#f6f7fb; --surface:#fff; --surface-2:#0e1330; --card:#fff; --text:#12152b; --muted:#5a6387;
      --primary:#3a66ff; --primary-2:#6f90ff; --accent:#7b5cff; --success:#16a34a; --warning:#d97706; --danger:#ef4444;
      --ring:rgba(58,102,255,.35); --shadow:0 10px 24px rgba(13,24,61,.10); --bg-primary:var(--surface); --bg-secondary:var(--surface-2);
      --bg-tertiary:var(--card); --text-primary:var(--text); --text-secondary:var(--muted); --text-tertiary:#7a84b0; --border-color:#e0e5f5 }
    *{box-sizing:border-box} html,body{height:100%}
    body{ margin:0; font:14px/1.55 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial; color:var(--text);
      background: radial-gradient(1200px 800px at -10% -10%, #1a2050 0%, var(--bg) 55%), radial-gradient(1200px 700px at 110% -10%, #281a55 0%, transparent 50%), var(--bg) }
    .wrap{ min-height:100vh; display:grid; place-items:center; padding:24px }
    .card{ width:min(720px,100%); background:var(--card); border-radius:22px; box-shadow:var(--shadow); padding:24px; position:relative; overflow:hidden }
    .card::before{ content:""; position:absolute; inset:-60px -40px auto auto; width:240px; height:240px; border-radius:50%;
      background:radial-gradient(closest-side, rgba(79,124,255,.20), transparent); filter:blur(10px); pointer-events:none; z-index:0 }
    .head{ display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; position:relative; z-index:1 }
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

    /* NEW: role switcher bar */
    .switcher{ display:flex; gap:8px; background:var(--surface-2); padding:6px; border-radius:14px; border:1px solid var(--border-color); align-items:center }
    .switcher button{ flex:1; padding:10px 12px; border-radius:10px; border:0; cursor:pointer; font-weight:700; background:transparent; color:var(--text-secondary) }
    .switcher button[aria-selected="true"]{ background:var(--primary); color:#fff; box-shadow:0 8px 18px rgba(79,124,255,.35) }

    .hidden{ display:none !important }
    .dimmed{ opacity:.6 }
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
            <div style="font-weight:758">CrewConnect</div>
            <div class="muted" id="regDesc">@lang('Create your account')</div>
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

      {{-- ===== ACCOUNT TYPE SWITCHER ===== --}}
      <div class="switcher" role="tablist" aria-label="Register As">
        <button id="tab-worker" role="tab" aria-selected="{{ old('account_type','worker')!=='employee' ? 'true' : 'false' }}" aria-controls="panel-worker" type="button">
          @lang('Worker')
        </button>
        <button id="tab-employee" role="tab" aria-selected="{{ old('account_type')==='employee' ? 'true' : 'false' }}" aria-controls="panel-employee" type="button">
          @lang('Client')
        </button>
      </div>

      {{-- MAIN FORM (single form, supports file upload) --}}
      <form id="regForm" method="POST" action="{{ route('register') }}" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="account_type" id="account_type" value="{{ old('account_type','worker') }}"/>

        {{-- ================= Worker Panel (full form) ================= --}}
        @php $showWorker = old('account_type','worker') !== 'employee'; @endphp
        <section id="panel-worker" role="tabpanel" aria-labelledby="tab-worker" class="{{ $showWorker ? '' : 'hidden' }}">
          <div class="grid">
            <div class="field">
              <label for="first">@lang('First Name')</label>
              <input class="input" id="first" name="first_name" value="{{ old('first_name') }}" required placeholder="First Name"/>
              @error('first_name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="last">@lang('Last Name')</label>
              <input class="input" id="last" name="last_name" value="{{ old('last_name') }}" required placeholder="Last Name"/>
              @error('last_name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="email">@lang('Email')</label>
              <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="you@gmail.com" autocomplete="username"/>
              @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="phone">@lang('Phone')</label>
              <input class="input" id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="+961 xx xxx xxx"/>
              @error('phone') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="city">@lang('City')</label>
              <input class="input" id="city" name="city" value="{{ old('city') }}" placeholder="City"/>
              @error('city') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- NEW: Date of Birth (worker) --}}
            <div class="field">
              <label for="dob">@lang('Date of Birth')</label>
              <input
                  class="input"
                  id="dob"
                  type="date"
                  name="date_of_birth"
                  value="{{ old('date_of_birth') }}"
                  required
              />
              @error('date_of_birth') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="role">@lang('Preferred Role')</label>
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

          {{-- Certificate upload (Worker only) --}}
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
        </section>

        {{-- ================= Employee Panel (minimal fields) ================= --}}
        @php $showEmployee = old('account_type')==='employee'; @endphp
        <section id="panel-employee" role="tabpanel" aria-labelledby="tab-employee" class="{{ $showEmployee ? '' : 'hidden' }}">
          <div class="grid">
            <div class="field">
              <label for="e_first">@lang('First Name')</label>
              <input class="input" id="e_first" name="e_first_name" value="{{ old('e_first_name') }}" placeholder="First Name"/>
              @error('e_first_name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="e_last">@lang('Last Name')</label>
              <input class="input" id="e_last" name="e_last_name" value="{{ old('e_last_name') }}" placeholder="Last Name"/>
              @error('e_last_name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="e_email">@lang('Email')</label>
              <input class="input" id="e_email" type="email" name="e_email" value="{{ old('e_email') }}" placeholder="name@gmail.com" autocomplete="username"/>
              @error('e_email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="e_phone">@lang('Phone')</label>
              <input class="input" id="e_phone" type="tel" name="e_phone"
                     value="{{ old('e_phone') }}" placeholder="+961 xx xxx xxx"/>
              @error('e_phone') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- NEW: Date of Birth (employee) --}}
            <div class="field">
              <label for="e_dob">@lang('Date of Birth')</label>
              <input
                  class="input"
                  id="e_dob"
                  type="date"
                  name="e_date_of_birth"
                  value="{{ old('e_date_of_birth') }}"
                  required
              />
              @error('e_date_of_birth') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="e_pass">@lang('Password')</label>
              <input class="input" id="e_pass" type="password" name="e_password" minlength="6" placeholder="@lang('password')" autocomplete="new-password"/>
              @error('e_password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
              <label for="e_confirm">@lang('Confirm Password')</label>
              <input class="input" id="e_confirm" type="password" name="e_password_confirmation" minlength="6" placeholder="••••••••" autocomplete="new-password"/>
            </div>
          </div>
        </section>

        <button class="btn full" type="submit" id="regBtn" style="margin-top:12px">@lang('Create Account')</button>
      </form>

      <p class="footer-note">@lang('Your information helps us match you with the right opportunities.')</p>
    </article>
  </div>

  {{-- JS (verbatim so Blade won’t parse it) --}}
  <script>
  @verbatim
    const STR = {
      en:{
        title:'Register',
        desc:'Create your account',
        first:'First Name',
        last:'Last Name',
        email:'Email',
        phone:'Phone',
        city:'City',
        role:'Preferred Role',
        pass:'Password',
        confirm:'Confirm Password',
        tos:'I agree to the Terms',
        have:'Have an account? Login',
        create:'Create Account',
        worker:'Worker',
        employee:'Client',
        dob:'Date of Birth'
      },
      ar:{
        title:'إنشاء حساب',
        desc:'أنشئ حسابك',
        first:'الاسم الأول',
        last:'اسم العائلة',
        email:'البريد الإلكتروني',
        phone:'الهاتف',
        city:'المدينة',
        role:'الدور المفضّل',
        pass:'كلمة المرور',
        confirm:'تأكيد كلمة المرور',
        tos:'أوافق على الشروط',
        have:'لديك حساب؟ تسجيل الدخول',
        create:'إنشاء الحساب',
        worker:'متطوّع',
        employee:'عميل',
        dob:'تاريخ الميلاد'
      }
    };
    let lang = document.documentElement.getAttribute('dir') === 'rtl' ? 'ar' : 'en';

    function t(){
      const s=STR[lang];
      document.documentElement.dir = (lang==='ar')?'rtl':'ltr';
      regTitle.textContent=s.title; regDesc.textContent=s.desc;

      // Worker labels
      document.querySelector('label[for="first"]').textContent=s.first;
      document.querySelector('label[for="last"]').textContent=s.last;
      document.querySelector('label[for="email"]').textContent=s.email;
      document.querySelector('label[for="phone"]').textContent=s.phone;
      document.querySelector('label[for="city"]').textContent=s.city;
      document.querySelector('label[for="role"]').textContent=s.role;
      document.querySelector('label[for="pass"]').textContent=s.pass;
      document.querySelector('label[for="confirm"]').textContent=s.confirm;
      document.querySelector('label[for="dob"]').textContent = s.dob;
      document.getElementById('tosText') && (document.getElementById('tosText').textContent=s.tos);
      document.getElementById('toLogin') && (document.getElementById('toLogin').textContent=s.have);
      document.getElementById('regBtn').textContent=s.create;

      // Employee labels
      document.querySelector('label[for="e_first"]').textContent=s.first;
      document.querySelector('label[for="e_last"]').textContent=s.last;
      document.querySelector('label[for="e_email"]').textContent=s.email;
      document.querySelector('label[for="e_phone"]').textContent = s.phone;
      document.querySelector('label[for="e_pass"]').textContent=s.pass;
      document.querySelector('label[for="e_confirm"]').textContent=s.confirm;
      document.querySelector('label[for="e_dob"]').textContent = s.dob;

      // Switcher text
      document.getElementById('tab-worker').textContent=s.worker;
      document.getElementById('tab-employee').textContent=s.employee;
    }

    // Theme toggler
    themeToggle.onclick=()=>{ const html=document.documentElement; html.setAttribute('data-theme', html.getAttribute('data-theme')==='light'?'dark':'light'); };

    // Language toggler
    langToggle.onclick=()=>{ lang=(lang==='en')?'ar':'en'; t(); };

    // ====== Switcher logic ======
    const tabWorker   = document.getElementById('tab-worker');
    const tabEmployee = document.getElementById('tab-employee');
    const panelWorker = document.getElementById('panel-worker');
    const panelEmployee = document.getElementById('panel-employee');
    const accountType = document.getElementById('account_type');

    function setActive(type){
      const isWorker = type==='worker';
      tabWorker.setAttribute('aria-selected', isWorker?'true':'false');
      tabEmployee.setAttribute('aria-selected', isWorker?'false':'true');
      panelWorker.classList.toggle('hidden', !isWorker);
      panelEmployee.classList.toggle('hidden', isWorker);

      accountType.value = type;

      // Disable inputs of the hidden panel so they don't submit
      panelWorker.querySelectorAll('input,select,textarea').forEach(el=>{
        el.disabled = !isWorker;
        if(!isWorker) el.classList.add('dimmed'); else el.classList.remove('dimmed');
      });
      panelEmployee.querySelectorAll('input,select,textarea').forEach(el=>{
        el.disabled = isWorker;
        if(isWorker) el.classList.add('dimmed'); else el.classList.remove('dimmed');
      });
    }

    // Init state (respect old('account_type'))
    setActive(accountType.value || 'worker');

    tabWorker.addEventListener('click', ()=> setActive('worker'));
    tabEmployee.addEventListener('click', ()=> setActive('employee'));

    // Client-side checks (server will validate again)
    regForm.addEventListener('submit',(e)=>{
      const type = accountType.value || 'worker';
      if(type==='worker'){
        const pass = document.getElementById('pass').value;
        const confirm = document.getElementById('confirm').value;
        const tos = document.getElementById('tos')?.checked;
        if(pass !== confirm){ e.preventDefault(); alert('Passwords do not match'); return; }
        if(!tos){ e.preventDefault(); alert('Please agree to the Terms'); return; }
      }else{
        const ep = document.getElementById('e_pass').value;
        const ec = document.getElementById('e_confirm').value;
        if(ep !== ec){ e.preventDefault(); alert('Passwords do not match'); return; }
      }
    });

    t();
  @endverbatim
  </script>
</body>
</html>
