// Mock employees (replace with real data / API later)
const employees = [
  { id:1, name:"Ahmed Al-Rashid", email:"ahmed.rashid@volunteerhub.com", role:"Event Coordinator", status:"active", eventsManaged:24, joinDate:"2024-06-15" },
  { id:2, name:"Fatima Hassan", email:"fatima.hassan@volunteerhub.com", role:"Volunteer Manager", status:"active", eventsManaged:18, joinDate:"2024-07-20" },
  { id:3, name:"Mohammed Ali", email:"mohammed.ali@volunteerhub.com", role:"Event Coordinator", status:"active", eventsManaged:15, joinDate:"2024-08-10" },
  { id:4, name:"Sarah Abdullah", email:"sarah.abdullah@volunteerhub.com", role:"Communications Manager", status:"inactive", eventsManaged:8, joinDate:"2024-09-05" }
];

const grid = document.getElementById('employeesGrid');
const searchInput = document.getElementById('searchInput');

function renderEmployees(list = employees) {
  grid.innerHTML = list.map(emp => {
    const initials = emp.name.split(' ').map(n => n[0]).join('');
    const statusClass = `badge-${emp.status}`;
    const statusText = emp.status.charAt(0).toUpperCase() + emp.status.slice(1);

    return `
      <div class="employee-card">
        <div class="employee-header">
          <div class="employee-avatar">${initials}</div>
          <div class="employee-info">
            <div class="employee-name">${emp.name}</div>
            <div class="employee-role">${emp.role}</div>
            <span class="employee-badge ${statusClass}">${statusText}</span>
          </div>
        </div>

        <div class="employee-meta">
          <div class="meta-item">
            <span class="meta-label">Email</span>
            <span class="meta-value">${emp.email}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Events Managed</span>
            <span class="meta-value">${emp.eventsManaged}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Join Date</span>
            <span class="meta-value">${emp.joinDate}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Status</span>
            <span class="meta-value">${statusText}</span>
          </div>
        </div>

        <div class="employee-actions">
          <button class="btn btn-secondary btn-sm" onclick="editEmployee(${emp.id})">Edit</button>
          <button class="btn btn-secondary btn-sm" onclick="resetPassword(${emp.id})">Reset Password</button>
          <button class="btn btn-danger btn-sm" onclick="deactivateEmployee(${emp.id})">Deactivate</button>
        </div>
      </div>`;
  }).join('');
}

function addEmployee(){ alert('Add Employee form would open here'); }
function editEmployee(id){ alert(`Edit employee ${id}`); }
function resetPassword(id){ if(confirm('Reset password for this employee?')) alert('Password reset email sent'); }
function deactivateEmployee(id){ if(confirm('Deactivate this employee account?')) alert('Employee deactivated'); }

function toggleTheme(){
  const html=document.documentElement;
  const next=html.getAttribute('data-theme')==='dark'?'light':'dark';
  html.setAttribute('data-theme', next);
  const ico=document.getElementById('theme-icon'); if(ico) ico.textContent = next==='dark' ? '☀️' : '🌙';
}
function toggleLanguage(){
  const html=document.documentElement;
  const next=html.getAttribute('lang')==='en'?'ar':'en';
  html.setAttribute('lang', next);
  html.setAttribute('dir', next==='ar'?'rtl':'ltr');
  const ico=document.getElementById('lang-icon'); if(ico) ico.textContent = next==='en' ? 'AR' : 'EN';
}

// Wire up page actions
document.getElementById('btnAddEmployee')?.addEventListener('click', addEmployee);
document.getElementById('btnTheme')?.addEventListener('click', toggleTheme);
document.getElementById('btnLang')?.addEventListener('click', toggleLanguage);

searchInput?.addEventListener('input', (e)=>{
  const q = e.target.value.trim().toLowerCase();
  const filtered = employees.filter(x =>
    x.name.toLowerCase().includes(q) ||
    x.email.toLowerCase().includes(q) ||
    x.role.toLowerCase().includes(q)
  );
  renderEmployees(filtered);
});

// Initial render
renderEmployees();
