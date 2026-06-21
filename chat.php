<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MindGuard Counsellor Chat</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,sans-serif;background:#EAF8FF}
        .topbar{height:58px;background:linear-gradient(90deg,#009CFB,#64C7F2);display:flex;align-items:center;justify-content:space-between;padding:0 18px;color:#fff;font-weight:bold}
        .topbar-right{display:flex;align-items:center;gap:12px}
        .counsellor-name{font-size:17px;white-space:nowrap}

        .wrap{display:flex;height:calc(100vh - 58px)}
        .sidebar{width:340px;background:#fff;border-right:1px solid #d7ecf7;display:flex;flex-direction:column}
        .search-box{margin:16px 14px 12px;height:44px;background:#F4FBFF;border:2px solid #C7E6F4;border-radius:22px;display:flex;align-items:center;padding:0 14px}
        .search-icon{font-size:16px;margin-right:8px;color:#009CFB}
        .search-box input{border:none;outline:none;background:transparent;width:100%;font-size:15px;color:#153243}
        .search-box input::placeholder{color:#6B8794}

        .thread-list{overflow:auto;flex:1;padding:0 10px 10px}
        .thread{padding:14px;border-radius:16px;background:#F4FBFF;margin-bottom:10px;cursor:pointer;border:2px solid transparent;display:flex;align-items:center;gap:12px}
        .thread.active{border-color:#009CFB;background:#E3F5FF}
        .user-avatar{width:48px;height:48px;border-radius:50%;object-fit:cover;background:#C7E6F4;border:2px solid #009CFB;flex-shrink:0}
        .thread-info{flex:1;min-width:0}
        .uname{font-weight:bold;color:#153243}
        .name-row{
    display:flex;
    align-items:center;
    gap:4px;
    max-width:100%;
}

.display-name{
    font-weight:bold;
    color:#153243;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width:145px;
}

.age-text{
    font-weight:bold;
    color:#4d6b7c;
    font-size:14px;
    white-space:nowrap;
    flex-shrink:0;
}

.thread-time{
    display:block;
    text-align:right;
    margin-top:2px;
    font-size:13px;
    color:#000;
    margin-left:auto;
}
        .last-text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .meta{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    margin-top:6px;
    width:100%;
}
        .badge{min-width:24px;height:24px;border-radius:12px;background:#FF3B30;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px;padding:0 8px}

        .chat-area{flex:1;display:flex;flex-direction:column}
        .chat-head{padding:10px 20px;background:#fff;border-bottom:1px solid #d7ecf7;color:#009CFB;font-weight:bold;display:flex;align-items:center;gap:12px;min-height:58px}
        .chat-head-avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;background:#C7E6F4;border:2px solid #009CFB;flex-shrink:0}
        .chat-head-name{color:#009CFB;font-weight:bold}

        .msgs{flex:1;overflow:auto;padding:20px;background:#DFF4FF}
        .row{display:flex;margin-bottom:14px}
        .row.me{justify-content:flex-end}
        .bubble{max-width:68%;padding:10px 12px;border-radius:18px;box-shadow:0 4px 10px rgba(0,0,0,.08)}
        .me .bubble{background:#009CFB;color:#fff;border-bottom-right-radius:4px}
        .other .bubble{background:#fff;color:#000;border-bottom-left-radius:4px}
        .time{font-size:12px;opacity:.85;margin-top:6px;text-align:right}
        .date-divider{display:flex;justify-content:center;margin:14px 0}
        .date-divider span{background:#ffffff;color:#4d6b7c;font-size:13px;font-weight:bold;padding:7px 14px;border-radius:999px;box-shadow:0 3px 10px rgba(0,0,0,.08)}

        .file-card{
            display:flex;
            align-items:center;
            gap:12px;
            min-width:300px;
            max-width:430px;
            padding:12px;
            border-radius:12px;
            background:rgba(255,255,255,.28);
            text-decoration:none;
            color:inherit;
        }

        .other .file-card{
            background:#F1F7FA;
        }

        .file-icon{
            width:42px;
            height:42px;
            border-radius:8px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:22px;
            font-weight:bold;
            color:#fff;
            flex-shrink:0;
        }

        .file-pdf{background:#D93025}
        .file-ppt{background:#D24726}
        .file-doc{background:#2B579A}
        .file-xls{background:#217346}
        .file-img{background:#7B61FF}
        .file-other{background:#607D8B}

        .file-info{
            flex:1;
            min-width:0;
        }

        .file-name{
            font-weight:bold;
            font-size:15px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .file-meta{
            margin-top:4px;
            font-size:12px;
            opacity:.85;
        }

        .image-preview{
            max-width:280px;
            max-height:220px;
            border-radius:12px;
            display:block;
            object-fit:cover;
        }

        .composer{padding:14px;background:#fff;border-top:1px solid #d7ecf7;display:flex;gap:10px;align-items:center}
        .plus-btn{width:44px;height:44px;border:none;border-radius:50%;background:#C7E6F4;font-size:24px;color:#009CFB;cursor:pointer}
        .input{flex:1;padding:12px 14px;border:1px solid #cde8f4;border-radius:24px;font-size:15px}
        .send{height:44px;padding:0 20px;border:none;border-radius:22px;background:#009CFB;color:#fff;font-weight:bold;cursor:pointer}
        .muted{padding:20px;color:#4d6b7c}

        a.logout{background:#FF3B30;width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:0.2s}
        a.logout img{width:20px;height:20px;object-fit:contain}
        a.logout:hover{background:#d92c24;transform:scale(1.05)}

        /* NEW MESSAGES BUTTON */
        .new-msg-btn{position:absolute;bottom:80px;left:50%;transform:translateX(-50%);background:#009CFB;color:#fff;padding:10px 16px;border-radius:20px;font-size:14px;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,.2);display:none;z-index:10;}
    </style>
</head>

<body>
<div class="topbar">
    <div>MindGuard Counsellor Console</div>

    <div class="topbar-right">
        <span class="counsellor-name">
            <?= htmlspecialchars($_SESSION['counsellor_name']) ?>
        </span>

        <a class="logout" href="logout.php" title="Logout" id="logoutBtn">
            <img src="./assets/logout_icon.png" alt="Logout">
        </a>
    </div>
</div>

<div class="wrap">
    <div class="sidebar">
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Search...">
        </div>

        <div class="thread-list" id="threadList"></div>
    </div>

    <div class="chat-area">
        <div class="chat-head" id="chatHead"></div>

        <div class="msgs" id="msgs">
            <div class="muted"></div>
        </div>

        <form class="composer" id="sendForm">
            <input type="file" id="attachmentInput" style="display:none">
            <button class="plus-btn" type="button" id="plusBtn">+</button>
            <input class="input" id="messageInput" type="text" placeholder="Type a message...">
            <button class="send" type="submit">Send</button>
        </form>
    </div>
</div>

<script>
let selectedThreadId = 0;

const threadList = document.getElementById('threadList');
const msgs = document.getElementById('msgs');
const chatHead = document.getElementById('chatHead');
const sendForm = document.getElementById('sendForm');
const messageInput = document.getElementById('messageInput');
const plusBtn = document.getElementById('plusBtn');
const attachmentInput = document.getElementById('attachmentInput');
const searchInput = document.getElementById('searchInput');

let allThreads = [];
let isUserScrolling = false;
let lastMessageCount = 0;

/* NEW BUTTON */
const newMsgBtn = document.createElement('div');
newMsgBtn.className = 'new-msg-btn';
newMsgBtn.innerText = 'New messages ↓';
document.body.appendChild(newMsgBtn);

newMsgBtn.onclick = () => {
    msgs.scrollTo({
        top: msgs.scrollHeight,
        behavior: 'smooth'
    });
    newMsgBtn.style.display = 'none';
};

/* DETECT USER SCROLL */
msgs.addEventListener('scroll', () => {
    const threshold = 100;
    const atBottom = msgs.scrollHeight - msgs.scrollTop - msgs.clientHeight < threshold;

    isUserScrolling = !atBottom;

    if (atBottom) {
        newMsgBtn.style.display = 'none';
    }
});

/* HELPERS */
function escapeHtml(str='') {
    return String(str).replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
}

function formatTimeOnly(dateTime) {
    if (!dateTime) return '';
    const parts = dateTime.split(' ');
    if (parts.length < 2) return dateTime;
    const timeParts = parts[1].split(':');
    return timeParts[0] + ':' + timeParts[1];
}

function getDateKey(dateTime) {
    return dateTime ? dateTime.split(' ')[0] : '';
}

function formatDateDivider(dateTime) {
    if (!dateTime) return '';
    const msgDate = new Date(dateTime.replace(' ', 'T'));
    const now = new Date();

    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);

    const msgDay = new Date(msgDate.getFullYear(), msgDate.getMonth(), msgDate.getDate());

    if (msgDay.getTime() === today.getTime()) return 'Today';
    if (msgDay.getTime() === yesterday.getTime()) return 'Yesterday';

    return msgDate.toLocaleDateString('en-GB', {
        weekday:'long', day:'2-digit', month:'short', year:'numeric'
    });
}

/* FILE UI */
function getFileExtension(fileName=''){ return fileName.split('.').pop().toLowerCase(); }

function getFileIconClass(ext){
    if(ext==='pdf') return 'file-pdf';
    if(['ppt','pptx'].includes(ext)) return 'file-ppt';
    if(['doc','docx'].includes(ext)) return 'file-doc';
    if(['xls','xlsx'].includes(ext)) return 'file-xls';
    if(['jpg','jpeg','png','gif','webp'].includes(ext)) return 'file-img';
    return 'file-other';
}

function getFileIconText(ext){
    if(ext==='pdf') return 'PDF';
    if(['ppt','pptx'].includes(ext)) return 'P';
    if(['doc','docx'].includes(ext)) return 'W';
    if(['xls','xlsx'].includes(ext)) return 'X';
    if(['jpg','jpeg','png','gif','webp'].includes(ext)) return '🖼';
    return '📎';
}

function isImageFile(fileName='',type=''){
    const ext = getFileExtension(fileName);
    return type.startsWith('image/') || ['jpg','jpeg','png','gif','webp'].includes(ext);
}

function buildAttachmentHtml(m){
    if(!m.attachment_url) return '';

    const fileName = m.attachment_name || 'Attachment';
    const ext = getFileExtension(fileName);

    if(isImageFile(fileName, m.attachment_type||'')){
        return `<img class="image-preview" src="${m.attachment_url}">`;
    }

    return `
        <a class="file-card" href="${m.attachment_url}" target="_blank">
            <div class="file-icon ${getFileIconClass(ext)}">${getFileIconText(ext)}</div>
            <div class="file-info">
                <div class="file-name">${escapeHtml(fileName)}</div>
                <div class="file-meta">${ext.toUpperCase()}</div>
            </div>
        </a>
    `;
}

/* THREADS */
function renderThreads(threads){
    const keyword = searchInput.value.toLowerCase();

    threadList.innerHTML='';

    threads.filter(t=>{
        const displayName = (t.full_name && t.full_name.trim() !== '') ? t.full_name : t.username;

        return displayName.toLowerCase().includes(keyword)
            || t.username.toLowerCase().includes(keyword)
            || (t.last_text||'').toLowerCase().includes(keyword);
    }).forEach(t=>{
        const div=document.createElement('div');
        div.className='thread'+(t.thread_id==selectedThreadId?' active':'');

        const img = t.profile_image ? '../'+t.profile_image : '../uploads/default_profile.png';
        const displayName = (t.full_name && t.full_name.trim() !== '') ? t.full_name : t.username;

        div.innerHTML=`
            <img class="user-avatar" src="${img}">
            <div class="thread-info">
                <div class="name-row">
    <span class="display-name">${escapeHtml(displayName)}</span>
    ${t.age ? `<span class="age-text">(Age: ${parseInt(t.age)})</span>` : ''}
</div>

<div class="last-text">${escapeHtml(t.last_text||'')}</div>

<div class="meta">
    <span class="thread-time">${formatTimeOnly(t.last_message_at)}</span>
    ${parseInt(t.unread_count || 0) > 0 ? `<span class="badge">${parseInt(t.unread_count)}</span>` : ''}
</div>
            </div>
        `;

        div.onclick=()=>{
            selectedThreadId=t.thread_id;
            chatHead.innerHTML=`
                <img class="chat-head-avatar" src="${img}">
                <span class="chat-head-name">${escapeHtml(displayName)}</span>
            `;
            loadMessages(true);
        };

        threadList.appendChild(div);
    });
}

/* LOAD THREADS */
async function loadThreads(){
    const res=await fetch('fetch_threads.php');
    const data=await res.json();
    if(!data.success) return;
    allThreads=data.threads;
    renderThreads(allThreads);
}

/* LOAD MESSAGES */
async function loadMessages(forceScroll=false){
    if(!selectedThreadId) return;

    const res=await fetch('fetch_messages.php?thread_id='+selectedThreadId);
    const data=await res.json();

    if(!data.success) return;

    msgs.innerHTML='';
    let lastDate='';

    data.messages.forEach(m=>{
        const dateKey=getDateKey(m.created_at);

        if(dateKey!==lastDate){
            msgs.innerHTML+=`<div class="date-divider"><span>${formatDateDivider(m.created_at)}</span></div>`;
            lastDate=dateKey;
        }

        msgs.innerHTML+=`
            <div class="row ${m.sender_type==='counsellor'?'me':'other'}">
                <div class="bubble">
                    ${m.message_text ? escapeHtml(m.message_text) : ''}
                    ${buildAttachmentHtml(m)}
                    <div class="time">${formatTimeOnly(m.created_at)}</div>
                </div>
            </div>
        `;
    });

    /* SCROLL LOGIC */
    if(forceScroll || !isUserScrolling){
        msgs.scrollTo({
            top: msgs.scrollHeight,
            behavior:'smooth'
        });
    } else {
        newMsgBtn.style.display='block';
    }

    lastMessageCount = data.messages.length;
}

/* SEND */
sendForm.addEventListener('submit',async e=>{
    e.preventDefault();
    if(!selectedThreadId) return;

    const fd=new FormData();
    fd.append('thread_id',selectedThreadId);
    fd.append('message',messageInput.value);

    const res=await fetch('send_message.php',{method:'POST',body:fd});
    const data=await res.json();

    if(data.success){
        messageInput.value='';
        loadMessages(true);
    }
});

/* ATTACH */
plusBtn.onclick=()=>attachmentInput.click();

attachmentInput.onchange=async ()=>{
    const fd=new FormData();
    fd.append('thread_id',selectedThreadId);
    fd.append('attachment',attachmentInput.files[0]);

    const res=await fetch('upload_attachment.php',{method:'POST',body:fd});
    const data=await res.json();

    if(data.success){
        loadMessages(true);
    }
};

/* SEARCH */
searchInput.oninput=()=>renderThreads(allThreads);

/* LOGOUT */
document.getElementById('logoutBtn').onclick=(e)=>{
    e.preventDefault();
    if(confirm('Do you want to logout?')){
        window.location='logout.php';
    }
};

async function heartbeat(){
    await fetch('heartbeat.php');
}

setInterval(()=>{
    heartbeat();
    loadThreads();
    loadMessages();
},3000);

heartbeat();
loadThreads();
</script>
</body>
</html>