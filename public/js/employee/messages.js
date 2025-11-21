// =======================
// i18n strings
// =======================
const STRINGS = {
  en: {
    brand: "Volunteer",
    dashboard: "Dashboard",
    discover: "Discover Events",
    myRes: "My Reservations",
    submissions: "Post-Event Submissions",
    announcements: "Announcements",
    chat: "Chat",
    profile: "Profile",
    settings: "Settings",
    search: "Search messages…",
    convTitle: "Messages",
    online: "Online",
    offline: "Offline",
    typeMessage: "Type a message..."
  },
  ar: {
    brand: "متطوّع",
    dashboard: "لوحة التحكم",
    discover: "استكشف الفعاليات",
    myRes: "حجوزاتي",
    submissions: "تقارير ما بعد الحدث",
    announcements: "التعميمات",
    chat: "المحادثة",
    profile: "الملف الشخصي",
    settings: "الإعدادات",
    search: "ابحث في الرسائل…",
    convTitle: "الرسائل",
    online: "متصل",
    offline: "غير متصل",
    typeMessage: "اكتب رسالة..."
  }
};

let lang = "en";

// Shortcuts
const $ = (sel) => document.querySelector(sel);
const convList = $("#convList");
const body = document.body;

const contactsUrl = body.getAttribute("data-contacts-url");
const threadBaseUrl = body.getAttribute("data-thread-url-base");
const csrf = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute("content");

// State
let contacts = [];          // full list
let filteredContacts = [];  // filtered by search
let currentContactId = null;
let searchQuery = "";

// =======================
// i18n
// =======================
function i18nApply() {
  const s = STRINGS[lang];
  document.documentElement.dir = lang === "ar" ? "rtl" : "ltr";

  // Only some of these IDs exist in the employee layout, the rest are harmless
  const map = {
    "#brandName": s.brand,
    "#navDashboard": s.dashboard,
    "#navDiscover": s.discover,
    "#navMyRes": s.myRes,
    "#navSubmissions": s.submissions,
    "#navAnnouncements": s.announcements,
    "#navChat": s.chat,
    "#navProfile": s.profile,
    "#navSettings": s.settings,
    "#convTitle": s.convTitle
  };

  Object.entries(map).forEach(([sel, text]) => {
    const el = document.querySelector(sel);
    if (el) el.textContent = text;
  });

  const search = $("#globalSearch");
  if (search) search.placeholder = s.search;
}

// =======================
// Contacts loading / filtering
// =======================
async function loadContacts() {
  try {
    const res = await fetch(contactsUrl, {
      headers: { Accept: "application/json" }
    });
    if (!res.ok) throw new Error("Failed contacts");
    const data = await res.json();
    contacts = data.contacts || [];
    filteredContacts = contacts.slice();
    renderConversations();
  } catch (err) {
    console.error("Contacts error", err);
  }
}

function applyContactFilter() {
  if (!searchQuery) {
    filteredContacts = contacts.slice();
  } else {
    const q = searchQuery.toLowerCase();
    filteredContacts = contacts.filter((c) => {
      const name = (c.name || "").toLowerCase();
      const last = (c.lastMessage || "").toLowerCase();
      return name.includes(q) || last.includes(q);
    });
  }
  renderConversations();
}

function renderConversations() {
  convList.innerHTML = "";

  if (!filteredContacts.length) {
    convList.innerHTML =
      '<div style="padding:16px;color:var(--muted);font-size:13px">No contacts yet.</div>';
    return;
  }

  filteredContacts.forEach((c) => {
    const isActive = Number(c.id) === Number(currentContactId);
    const div = document.createElement("div");
    div.className = "conv-item" + (isActive ? " active" : "");
    div.dataset.id = c.id;

    div.innerHTML = `
      <div class="avatar">
        ${c.avatar}
        ${c.online ? '<div class="online"></div>' : ""}
      </div>
      <div class="conv-info">
        <div class="conv-row">
          <div class="conv-name">${c.name}</div>
          <div class="conv-time">${c.time || ""}</div>
        </div>
        <div class="conv-row">
          <div class="conv-preview">${c.lastMessage || ""}</div>
          ${c.unread > 0 ? `<div class="unread">${c.unread}</div>` : ""}
        </div>
      </div>
    `;
    div.addEventListener("click", () => openChat(c.id));
    convList.appendChild(div);
  });
}

// =======================
// Open chat / thread
// =======================
async function openChat(contactId) {
  currentContactId = contactId;

  // clear unread immediately
  contacts = contacts.map((c) =>
    Number(c.id) === Number(contactId) ? { ...c, unread: 0 } : c
  );
  applyContactFilter(); // re-renders list + active state

  try {
    const res = await fetch(`${threadBaseUrl}/${contactId}`, {
      headers: { Accept: "application/json" }
    });
    if (!res.ok) throw new Error("failed thread");
    const data = await res.json();
    renderChatPanel(data.contact, data.messages || []);
  } catch (err) {
    console.error("Thread error", err);
  }
}

function renderChatPanel(contact, messages) {
  const panel = $("#chatPanel");
  const s = STRINGS[lang];

  panel.innerHTML = `
    <div class="chat-header">
      <div class="chat-user">
        <div class="avatar">${contact.avatar}</div>
        <div>
          <div class="chat-user-name">${contact.name}</div>
          <div class="chat-user-status">${s.online}</div>
        </div>
      </div>
    </div>
    <div class="messages-area" id="messagesArea">
      ${messages
        .map(
          (m) => `
        <div class="message ${m.from_me ? "sent" : ""}">
          <div class="avatar">${m.avatar}</div>
          <div>
            <div class="message-bubble">${m.text}</div>
            <div class="message-time">${m.time}</div>
          </div>
        </div>`
        )
        .join("")}
    </div>
    <div class="message-input-area">
      <textarea class="message-input" id="msgInput"
                placeholder="${s.typeMessage}" rows="1"></textarea>
      <button class="send-btn" id="sendBtn">➤</button>
    </div>
  `;

  const area = $("#messagesArea");
  if (area) {
    area.scrollTop = area.scrollHeight; // only chat scrolls
  }

  $("#sendBtn").addEventListener("click", sendCurrentMessage);
  $("#msgInput").addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      sendCurrentMessage();
    }
  });
}

// =======================
// Send message
// =======================
async function sendCurrentMessage() {
  const input = $("#msgInput");
  if (!input || !currentContactId) return;
  const text = input.value.trim();
  if (!text) return;

  try {
    const res = await fetch(`${threadBaseUrl}/${currentContactId}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf,
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json"
      },
      body: JSON.stringify({ message: text })
    });
    if (!res.ok) throw new Error("send failed");
    const data = await res.json();

    appendMessageToUI(data.message);
    input.value = "";

    // update last message in list
    contacts = contacts.map((c) =>
      Number(c.id) === Number(currentContactId)
        ? { ...c, lastMessage: data.message.text, time: data.message.time }
        : c
    );
    applyContactFilter();
  } catch (err) {
    console.error("Send error", err);
    alert("Failed to send message");
  }
}

function appendMessageToUI(m) {
  const area = $("#messagesArea");
  if (!area) return;

  const div = document.createElement("div");
  div.className = `message ${m.from_me ? "sent" : ""}`;
  div.innerHTML = `
    <div class="avatar">${m.avatar}</div>
    <div>
      <div class="message-bubble">${m.text}</div>
      <div class="message-time">${m.time}</div>
    </div>
  `;
  area.appendChild(div);
  area.scrollTop = area.scrollHeight;
}

// =======================
// Theme, language, search
// =======================
document.addEventListener("DOMContentLoaded", () => {
  i18nApply();
  loadContacts();

  const contactSearch = $("#contactSearch");
  if (contactSearch) {
    contactSearch.addEventListener("input", () => {
      searchQuery = contactSearch.value.trim();
      applyContactFilter();
    });
  }

  $("#themeToggle")?.addEventListener("click", () => {
    const isLight = body.getAttribute("data-theme") === "light";
    body.setAttribute("data-theme", isLight ? "dark" : "light");
  });

  $("#langToggle")?.addEventListener("click", () => {
    lang = lang === "en" ? "ar" : "en";
    i18nApply();
    renderConversations();
  });
});
