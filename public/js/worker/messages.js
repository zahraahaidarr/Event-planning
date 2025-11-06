// i18n
const STRINGS = {
  en: {
    brand:"Volunteer", dashboard:"Dashboard", discover:"Discover Events",
    myRes:"My Reservations", submissions:"Post-Event Submissions",
    announcements:"Announcements", chat:"Chat", profile:"Profile", settings:"Settings",
    search:"Search messages…", convTitle:"Messages", online:"Online", offline:"Offline",
    typeHere:"Type a message..."
  },
  ar: {
    brand:"متطوّع", dashboard:"لوحة التحكم", discover:"استكشف الفعاليات",
    myRes:"حجوزاتي", submissions:"تقارير ما بعد الحدث",
    announcements:"التعميمات", chat:"المحادثة", profile:"الملف الشخصي", settings:"الإعدادات",
    search:"ابحث في الرسائل…", convTitle:"الرسائل", online:"متصل", offline:"غير متصل",
    typeHere:"اكتب رسالة..."
  }
};
let lang = "en";

// Demo data
const conversations = [
  { id:1, name:"Ahmed Al-Rashid", avatar:"AA", lastMessage:"Thanks for organizing the event!", time:"2m", unread:2, online:true },
  { id:2, name:"Event Coordinators", avatar:"EC", lastMessage:"Meeting tomorrow at 10 AM", time:"1h", unread:1, online:false },
  { id:3, name:"Fatima Hassan", avatar:"FH", lastMessage:"See you at the cleanup event!", time:"3h", unread:0, online:true },
  { id:4, name:"Garden Team", avatar:"GT", lastMessage:"Great work everyone!", time:"Yesterday", unread:0, online:false },
];

const messages = {
  1: [
    { sender:"them", text:"Hi! Are you joining the garden cleanup tomorrow?", time:"10:30 AM", avatar:"AA" },
    { sender:"me", text:"Yes, I'll be there! What time does it start?", time:"10:32 AM", avatar:"FM" },
    { sender:"them", text:"It starts at 9 AM. We'll meet at the main entrance.", time:"10:33 AM", avatar:"AA" },
    { sender:"me", text:"Perfect! I'll bring some extra tools.", time:"10:35 AM", avatar:"FM" },
    { sender:"them", text:"Thanks for organizing the event!", time:"Just now", avatar:"AA" }
  ]
};

// Shortcuts
const $ = (sel) => document.querySelector(sel);
const convList = $('#convList');
const chatPanel = $('#chatPanel');

// i18n apply
function i18nApply(){
  const s = STRINGS[lang];
  document.documentElement.dir = (lang === 'ar') ? 'rtl' : 'ltr';
  $('#brandName')?.textContent = s.brand;
  $('#navDashboard')?.textContent = s.dashboard;
  $('#navDiscover')?.textContent = s.discover;
  $('#navMyRes')?.textContent = s.myRes;
  $('#navSubmissions')?.textContent = s.submissions;
  $('#navAnnouncements')?.textContent = s.announcements;
  $('#navChat')?.textContent = s.chat;
  $('#navProfile')?.textContent = s.profile;
  $('#navSettings')?.textContent = s.settings;
  $('#globalSearch')?.setAttribute('placeholder', s.search);
  $('#convTitle')?.textContent = s.convTitle;
}

// Render conversation list
function renderConversations(){
  convList.innerHTML = '';
  conversations.forEach(c=>{
    const div = document.createElement('div');
    div.className = 'conv-item';
    div.innerHTML = `
      <div class="avatar">
        ${c.avatar}
        ${c.online? '<div class="online"></div>':''}
      </div>
      <div class="conv-info">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
          <div class="conv-name">${c.name}</div>
          <div class="conv-time">${c.time}</div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div class="conv-preview">${c.lastMessage}</div>
          ${c.unread>0? `<div class="unread">${c.unread}</div>`:''}
        </div>
      </div>
    `;
    div.addEventListener('click', ()=> openChat(c.id, div));
    convList.appendChild(div);
  });
}

// Open a chat
function openChat(id, itemEl){
  const s = STRINGS[lang];
  const conv = conversations.find(c=> c.id===id);
  const msgs = messages[id] || [];

  // active item highlight
  document.querySelectorAll('.conv-item').forEach(x=> x.classList.remove('active'));
  itemEl?.classList.add('active');

  chatPanel.innerHTML = `
    <div class="chat-header">
      <div class="chat-user">
        <div class="avatar">${conv.avatar}</div>
        <div>
          <div class="chat-user-name">${conv.name}</div>
          <div class="chat-user-status">${conv.online? s.online : s.offline}</div>
        </div>
      </div>
    </div>
    <div class="messages-area" id="messagesArea">
      ${msgs.map(m=> `
        <div class="message ${m.sender==='me'?'sent':''}">
          <div class="avatar">${m.avatar}</div>
          <div>
            <div class="message-bubble">${m.text}</div>
            <div class="message-time">${m.time}</div>
          </div>
        </div>
      `).join('')}
    </div>
    <div class="message-input-area">
      <textarea class="message-input" id="msgInput" placeholder="${s.typeHere}" rows="1"></textarea>
      <button class="send-btn" id="sendBtn">➤</button>
    </div>
  `;

  // Auto-scroll to bottom
  const area = $('#messagesArea');
  area.scrollTop = area.scrollHeight;

  // Send message (demo)
  $('#sendBtn').addEventListener('click', ()=> sendMessage(id));
  $('#msgInput').addEventListener('keydown', (e)=>{
    if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); sendMessage(id); }
  });
}

function sendMessage(convId){
  const input = $('#msgInput');
  const text = (input.value || '').trim();
  if(!text) return;
  const now = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});

  // Append to UI (demo)
  const area = $('#messagesArea');
  const node = document.createElement('div');
  node.className = 'message sent';
  node.innerHTML = `
    <div class="avatar">ME</div>
    <div>
      <div class="message-bubble">${text}</div>
      <div class="message-time">${now}</div>
    </div>`;
  area.appendChild(node);
  area.scrollTop = area.scrollHeight;
  input.value = '';

  // Update last message preview (demo)
  const conv = conversations.find(c=> c.id === convId);
  conv.lastMessage = text;
  renderConversations();
}

document.getElementById('themeToggle').addEventListener('click', ()=>{
  const root = document.documentElement; // <html>
  const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  root.setAttribute('data-theme', next);
  document.body.setAttribute('data-theme', next); // optional safety
  console.log('Theme ->', next); // quick sanity check
});



// Init
i18nApply();
renderConversations();
