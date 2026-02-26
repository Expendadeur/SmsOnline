let lastId = 0;
let currentContactId = null;
let pollingAbortController = null;
let mediaRecorder = null;
let audioChunks = [];
let recordingTimer = null;
let seconds = 0;
let isRecording = false;
let isStartingRecording = false;
let recordingStartTime = 0;

/**
 * SPA UX HELPERS
 */
function showProgress() {
    const bar = document.getElementById('top-progress-bar');
    if (bar) bar.style.display = 'block';
}

function hideProgress() {
    const bar = document.getElementById('top-progress-bar');
    if (bar) bar.style.display = 'none';
}

function playNotification() {
    const sound = document.getElementById('notif-sound');
    if (sound) {
        sound.play().catch(err => {
            console.warn("Notification sound blocked or failed:", err);
        });
    }
}

/**
 * PRODUCTION READY CHAT LOGIC (Optimized for Social Overlay)
 */
function openChat(contactId, contactName, contactPhoto) {
    if (currentContactId === contactId) return;

    if (isRecording) stopAndSendRecording();

    currentContactId = contactId;

    // UI Elements
    const overlay = document.getElementById('chat-overlay');
    const nameEl = document.getElementById('chat-contact-name');
    const photoEl = document.getElementById('chat-contact-photo');
    const msgArea = document.getElementById('messages-area');

    if (overlay) overlay.style.display = 'flex';
    document.body.classList.add('chat-open');

    if (nameEl) nameEl.innerText = contactName;
    if (photoEl) {
        photoEl.src = BASE_URL + '/uploads/' + (contactPhoto || 'default_profile.png');
    }

    if (msgArea) {
        msgArea.innerHTML = '';
        lastId = 0;
    }

    showProgress();
    safeFetch(BASE_URL + '/Chat/getMessages/' + contactId)
        .then(data => {
            hideProgress();
            renderMessages(data);
            startPolling();
        })
        .catch(hideProgress);
}

function closeChat() {
    document.body.classList.remove('chat-open');
    const overlay = document.getElementById('chat-overlay');
    if (overlay) overlay.style.display = 'none';

    if (isRecording) stopAndSendRecording();
    currentContactId = null;
    if (pollingAbortController) pollingAbortController.abort();
}

function renderMessages(messages) {
    const area = document.getElementById('messages-area');
    if (!area) return;

    let hasNew = false;
    messages.forEach(msg => {
        if (msg.id > lastId) {
            lastId = msg.id;
            hasNew = true;

            const isSent = (msg.sender_id == currentUserId);
            const bubble = document.createElement('div');
            bubble.className = `bubble ${isSent ? 'sent' : 'received'}`;

            let contentHtml = '';
            if (msg.type === 'audio') {
                contentHtml = `<audio controls style="max-width: 200px;"><source src="${BASE_URL}/uploads/${msg.message}" type="audio/webm">Your browser does not support audio.</audio>`;
            } else {
                contentHtml = msg.message;
            }

            let statusHtml = '';
            if (isSent) {
                let statusChar = '✓';
                let statusClass = 'status-sent';
                if (msg.status === 'delivered') { statusChar = '✓✓'; statusClass = 'status-delivered'; }
                if (msg.status === 'read') { statusChar = '✓✓'; statusClass = 'status-read'; }
                statusHtml = `<span class="status-icon ${statusClass}">${statusChar}</span>`;
            }

            bubble.innerHTML = `${contentHtml} ${statusHtml}`;
            area.appendChild(bubble);
        }
    });

    if (hasNew) {
        area.scrollTop = area.scrollHeight;
    }
}

function sendMessage() {
    if (isRecording) {
        stopAndSendRecording();
        return;
    }

    const input = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    if (!input) return;

    const text = input.value.trim();
    if (!text || !currentContactId) return;

    input.value = '';
    if (sendBtn) sendBtn.disabled = true;
    showProgress();

    safeFetch(BASE_URL + '/Chat/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `receiver_id=${currentContactId}&message=${encodeURIComponent(text)}`
    })
        .then(data => {
            hideProgress();
            if (sendBtn) sendBtn.disabled = false;
            if (data.error) alert(data.error);
        })
        .catch(() => {
            hideProgress();
            if (sendBtn) sendBtn.disabled = false;
        });
}

/**
 * VOICE MESSAGING LOGIC
 */
const voiceBtn = document.getElementById('voice-btn');
const recordingStatus = document.getElementById('recording-status');
const timerDisplay = document.getElementById('timer');

if (voiceBtn) {
    voiceBtn.addEventListener('click', () => {
        if (isStartingRecording) return;
        if (!isRecording) startRecording();
        else stopAndSendRecording();
    });
}

function startRecording() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("L'enregistrement audio n'est pas supporté par votre navigateur.");
        return;
    }

    isStartingRecording = true;
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = e => {
                if (e.data.size > 0) audioChunks.push(e.data);
            };

            mediaRecorder.onstop = () => {
                const duration = Date.now() - recordingStartTime;
                if (duration < 1000) {
                    console.warn("Recording too short, discarding.");
                } else if (audioChunks.length > 0) {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    sendVoiceMessage(audioBlob);
                }
                stream.getTracks().forEach(track => track.stop());
                resetUI();
            };

            recordingStartTime = Date.now();
            mediaRecorder.start();
            isRecording = true;
            isStartingRecording = false;

            if (voiceBtn) {
                voiceBtn.style.color = '#FF0000';
                voiceBtn.innerText = '⏹️';
            }
            if (recordingStatus) recordingStatus.style.display = 'block';

            seconds = 0;
            if (timerDisplay) timerDisplay.innerText = "0s";
            clearInterval(recordingTimer);
            recordingTimer = setInterval(() => {
                seconds++;
                if (timerDisplay) timerDisplay.innerText = seconds + "s";
            }, 1000);
        })
        .catch(err => {
            console.error("Mic access denied:", err);
            isStartingRecording = false;
            isRecording = false;
            alert("Impossible d'accéder au micro.");
        });
}

function stopAndSendRecording() {
    if (mediaRecorder && mediaRecorder.state === "recording") {
        mediaRecorder.stop();
    } else {
        resetUI();
    }
}

function resetUI() {
    isRecording = false;
    isStartingRecording = false;
    if (voiceBtn) {
        voiceBtn.style.color = '#919191';
        voiceBtn.innerText = '🎙️';
    }
    if (recordingStatus) recordingStatus.style.display = 'none';
    clearInterval(recordingTimer);
}

function sendVoiceMessage(blob) {
    if (!currentContactId) return;

    showProgress();
    const formData = new FormData();
    formData.append('audio', blob, 'recording.webm');
    formData.append('receiver_id', currentContactId);

    safeFetch(BASE_URL + '/Chat/sendVoice', {
        method: 'POST',
        body: formData
    })
        .then(data => {
            hideProgress();
            if (data.error) alert(data.error);
        })
        .catch(hideProgress);
}

/**
 * Polling logic
 */
async function startPolling() {
    if (pollingAbortController) pollingAbortController.abort();
    pollingAbortController = new AbortController();

    while (currentContactId) {
        try {
            const data = await safeFetch(`${BASE_URL}/Chat/poll/${currentContactId}?last_id=${lastId}`, {
                signal: pollingAbortController.signal
            });

            if (data.messages && data.messages.length > 0) {
                renderMessages(data.messages);
                playNotification();
            }
        } catch (err) {
            if (err.name === 'AbortError') break;
            await new Promise(r => setTimeout(r, 5000));
        }
    }
}

async function startGlobalPolling() {
    while (true) {
        try {
            const data = await safeFetch(BASE_URL + '/Chat/checkNotifications');
            if (data && data.length > 0) updateBadges(data);
        } catch (err) {
            await new Promise(r => setTimeout(r, 10000));
        }
    }
}

function updateBadges(unreadData) {
    unreadData.forEach(item => {
        if (item.sender_id == currentContactId) return;
        const userItem = document.querySelector(`.user-item[data-id="${item.sender_id}"]`);
        if (userItem) {
            let badge = userItem.querySelector('.notification-badge');
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                const actionArea = userItem.querySelector('.action-area');
                if (actionArea) actionArea.prepend(badge);
                else userItem.appendChild(badge);
            }
            if (badge.innerText != item.count) {
                badge.innerText = item.count;
                playNotification();
            }
        }
    });
}

startGlobalPolling();
