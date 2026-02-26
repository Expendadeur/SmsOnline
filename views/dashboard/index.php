<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMSOnline - Social Hub</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%23128c7e'/><text x='16' y='22' text-anchor='middle' font-size='18' fill='white'>💬</text></svg>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="social-mode">
    <div id="top-progress-bar" class="progress-bar-container">
        <div class="progress-bar-fill"></div>
    </div>

    <header>
        <div class="header-content">
            <div class="header-user-info">
                <a href="<?php echo BASE_URL; ?>/Dashboard" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo $_SESSION['photo']; ?>" class="user-self-avatar" alt="Moi">
                    <h1 style="font-size: 20px; margin: 0;">SMSOnline</h1>
                </a>
            </div>
            
            <div class="header-nav">
                <a href="<?php echo BASE_URL; ?>/Profile" title="Mon Profil" class="header-icon-btn">👤</a>
                <a href="<?php echo BASE_URL; ?>/Auth/logout" title="Déconnexion" class="header-icon-btn logout-icon">🚪</a>
            </div>
        </div>
    </header>

    <main id="main-content-wrapper" class="main-wrapper">
        <!-- LEFT: MESSAGING SIDEBAR -->
        <aside class="left-sidebar">
            <div class="sidebar-header">
                <h3 style="font-size: 14px;">Messages & Contacts</h3>
            </div>
            <div class="sidebar-tabs">
                <button class="tab-btn active" onclick="switchSidebarTab('chats', this)">Discussions</button>
                <button class="tab-btn" onclick="switchSidebarTab('members', this)">Membres</button>
            </div>
            <div id="users-list" style="overflow-y: auto; flex: 1;">
                <div style="text-align:center; padding: 20px; color: #888;">Chargement...</div>
            </div>
        </aside>

        <!-- CENTER: SOCIAL FEED -->
        <section class="center-content">
            <div class="feed-container">
                <!-- Create Post Card -->
                <div class="post-card" style="padding: 12px; border-bottom: 3px solid var(--whatsapp-green);">
                    <div style="display: flex; gap: 10px;">
                        <img src="<?php echo BASE_URL; ?>/uploads/<?php echo $_SESSION['photo']; ?>" class="post-avatar" alt="Moi">
                        <textarea id="post-content" style="width:100%; border:none; outline:none; font-family:inherit; resize:none; padding-top:10px;" placeholder="Exprimez-vous, <?php echo $_SESSION['prenom']; ?>..."></textarea>
                    </div>
                    
                    <div id="media-preview" style="margin-top: 10px; display: none; position: relative; border-radius:8px; overflow:hidden;">
                         <button onclick="clearMedia()" style="position: absolute; top:5px; right:5px; background: rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:22px; height:22px; cursor:pointer;">✕</button>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px solid #f0f2f5;">
                         <div style="display: flex; gap: 12px;">
                             <label for="media-upload" style="cursor: pointer; font-size: 18px;" title="Media">📷 / 🎥</label>
                             <input type="file" id="media-upload" accept="image/*,video/*" style="display: none;" onchange="previewMedia(this)">
                         </div>
                         <button onclick="publishPost()" class="btn-premium" style="padding: 8px 20px; width: auto; font-size: 13px;">Publier</button>
                    </div>
                </div>

                <!-- Posts List -->
                <div id="social-feed">
                    <div style="text-align:center; padding: 40px; color: #aaa;">Chargement du fil...</div>
                </div>
            </div>
        </section>

        <!-- RIGHT: TRENDS -->
        <aside class="right-sidebar">
            <div class="trending-card">
                <h4>⭐ Vérifiés</h4>
                <div id="verified-users-list"></div>
            </div>

            <div class="trending-card">
                <h4>📊 Stats Plateforme</h4>
                <p style="font-size: 13px; color:#555; margin-bottom:5px;">Vues Totales: <span id="global-views-count" style="font-weight:bold;">0</span></p>
                <p style="font-size: 13px; color:#555;">En ligne: <span id="online-count" style="font-weight:bold;">0</span></p>
            </div>
        </aside>
    </main>

    <!-- MOBILE BOTTOM NAV -->
    <nav class="bottom-nav">
        <div class="nav-item active" id="nav-chats" onclick="switchMainView('chats')">
            <i>💬</i>
            <span>Discussions</span>
        </div>
        <div class="nav-item" id="nav-feed" onclick="switchMainView('feed')">
            <i>📱</i>
            <span>Fil d'actualité</span>
        </div>
        <div class="nav-item" id="nav-profile" onclick="location.href='<?php echo BASE_URL; ?>/Profile'">
            <i>👤</i>
            <span>Profil</span>
        </div>
    </nav>
    <!-- SHARE MODAL -->
    <div id="share-modal" class="sms-modal" style="display:none;">
        <div class="sms-modal-box">
            <div class="sms-modal-header">
                <span>🔁 Partager avec...</span>
                <button onclick="closeModal('share-modal')" class="modal-close">✕</button>
            </div>
            <div id="share-friends-list" style="max-height:300px; overflow-y:auto; padding: 10px 0;">
                <div style="text-align:center; color:#aaa;">Chargement...</div>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="edit-modal" class="sms-modal" style="display:none;">
        <div class="sms-modal-box">
            <div class="sms-modal-header">
                <span>✏️ Modifier le post</span>
                <button onclick="closeModal('edit-modal')" class="modal-close">✕</button>
            </div>
            <textarea id="edit-post-content" style="width:100%; min-height:100px; border:1px solid #ddd; border-radius:10px; padding:12px; font-family:inherit; font-size:14px; resize:vertical; outline:none;"></textarea>
            <button onclick="submitEditPost()" class="btn-premium" style="width:100%; margin-top:10px;">Enregistrer</button>
        </div>
    </div>

    <!-- DELETE CONFIRM -->
    <div id="delete-modal" class="sms-modal" style="display:none;">
        <div class="sms-modal-box" style="max-width:320px;">
            <div class="sms-modal-header">
                <span>🗑️ Supprimer le post</span>
                <button onclick="closeModal('delete-modal')" class="modal-close">✕</button>
            </div>
            <p style="font-size:14px; color:#555; margin: 10px 0;">Êtes-vous sûr de vouloir supprimer ce post ? Cette action est irréversible.</p>
            <div style="display:flex; gap:10px;">
                <button onclick="closeModal('delete-modal')" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px; cursor:pointer; background:none;">Annuler</button>
                <button onclick="submitDeletePost()" style="flex:1; padding:10px; border:none; border-radius:8px; cursor:pointer; background:#e74c3c; color:white; font-weight:bold;">Supprimer</button>
            </div>
        </div>
    </div>

    <div class="chat-overlay" id="chat-overlay">
        <div id="chat-header" style="background: var(--whatsapp-dark-green); padding: 10px; display: flex; align-items: center; color:white;">
             <button onclick="closeChatOverlay()" style="background:none; border:none; color:white; font-size:18px; cursor:pointer; margin-right: 12px;">✕</button>
             <img id="chat-contact-photo" src="" class="post-avatar" style="width: 32px; height: 32px; border:1px solid white;" alt="">
             <span id="chat-contact-name" style="font-weight: bold; margin-left: 10px; font-size:15px;">Discussion</span>
        </div>
        <div id="messages-area" style="flex: 1; overflow-y: auto; padding: 15px; background: #efe7dd;"></div>
        <div id="recording-status" style="display:none; padding:5px; text-align:center; color:#ff4b2b; font-size:11px; font-weight:bold;">Recording... <span id="timer">0s</span></div>
        <div style="padding: 10px; background: #fff; border-top:1px solid #eee; display: flex; gap: 8px; align-items: center;">
             <button id="voice-btn" style="background:none; border:none; font-size:22px; cursor:pointer; color:#919191;">🎙️</button>
             <input type="text" id="message-input" placeholder="Message..." style="flex:1; padding: 10px 15px; border-radius:20px; border:1px solid #ddd; outline:none; font-size:14px;">
             <button id="send-btn" onclick="sendMessage()" style="background:var(--whatsapp-green); color:white; border:none; width:35px; height:35px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">➤</button>
        </div>
    </div>

    <audio id="notif-sound" src="<?php echo BASE_URL; ?>/assets/music/2358-preview.mp3" preload="auto"></audio>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        let currentSidebarTab = 'chats';
    </script>
    <script src="<?php echo BASE_URL; ?>/assets/js/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/chat.js"></script>
    <script>
        // --- MOBILE NAVIGATION ---
        function switchMainView(view) {
            const wrapper = document.getElementById('main-content-wrapper');
            const navChats = document.getElementById('nav-chats');
            const navFeed = document.getElementById('nav-feed');

            if (view === 'feed') {
                wrapper.classList.add('mobile-active-feed');
                navFeed.classList.add('active');
                navChats.classList.remove('active');
                loadFeed(true);
            } else {
                wrapper.classList.remove('mobile-active-feed');
                navChats.classList.add('active');
                navFeed.classList.remove('active');
            }
        }

        // --- SOCIAL FEED LOGIC ---
        let socialOffset = 0;
        let mediaFile = null;

        function previewMedia(input) {
            if (input.files && input.files[0]) {
                mediaFile = input.files[0];
                const reader = new FileReader();
                const preview = document.getElementById('media-preview');
                preview.innerHTML = `<button onclick="clearMedia()" style="position: absolute; top:5px; right:5px; background: rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:22px; height:22px; cursor:pointer;">✕</button>`;
                
                reader.onload = function(e) {
                    if (mediaFile.type.startsWith('image/')) {
                        preview.innerHTML += `<img src="${e.target.result}" style="width:100%; display:block;">`;
                    } else if (mediaFile.type.startsWith('video/')) {
                        preview.innerHTML += `<video src="${e.target.result}" controls style="width:100%; display:block;"></video>`;
                    }
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(mediaFile);
            }
        }

        function clearMedia() {
            mediaFile = null;
            document.getElementById('media-upload').value = '';
            document.getElementById('media-preview').style.display = 'none';
        }

        function publishPost() {
            const content = document.getElementById('post-content').value.trim();
            if (!content && !mediaFile) return;

            showProgress();
            const formData = new FormData();
            formData.append('content', content);
            if (mediaFile) formData.append('media', mediaFile);

            safeFetch(BASE_URL + '/Post/create', { method: 'POST', body: formData })
            .then(data => {
                hideProgress();
                if (data.success) {
                    document.getElementById('post-content').value = '';
                    clearMedia();
                    loadFeed(true);
                    updateSidebarData();
                } else alert(data.error);
            });
        }

        let feedRefreshTimer = null;

        function loadFeed(reset = false) {
            if (reset) {
                socialOffset = 0;
                document.getElementById('social-feed').innerHTML = '';
            }
            showProgress();
            safeFetch(BASE_URL + '/Post/getFeed?offset=' + socialOffset)
            .then(posts => {
                hideProgress();
                if (!Array.isArray(posts)) return; // guard against error objects
                const container = document.getElementById('social-feed');
                if (reset && posts.length === 0) {
                    container.innerHTML = '<div style="text-align:center;padding:30px;color:#aaa;font-size:13px;">Aucun post pour le moment.</div>';
                    return;
                }
                posts.forEach(post => container.insertAdjacentHTML('beforeend', renderPost(post)));
                socialOffset += posts.length;
                observePosts();
            })
            .catch(() => hideProgress()); // ALWAYS stop the spinner

            // Schedule auto-refresh every 30 seconds
            clearTimeout(feedRefreshTimer);
            feedRefreshTimer = setTimeout(silentRefreshFeed, 30000);
        }

        function silentRefreshFeed() {
            safeFetch(BASE_URL + '/Post/getFeed?offset=0')
            .then(posts => {
                if (!Array.isArray(posts) || posts.length === 0) return;
                const container = document.getElementById('social-feed');
                const existingIds = new Set(
                    [...container.querySelectorAll('.post-card')].map(el => el.dataset.postId)
                );
                let hasNew = false;
                posts.forEach(post => {
                    if (!existingIds.has(String(post.id))) {
                        container.insertAdjacentHTML('afterbegin', renderPost(post));
                        hasNew = true;
                    }
                });
                if (hasNew) updateSidebarData();
            })
            .catch(() => {})
            .finally(() => {
                feedRefreshTimer = setTimeout(silentRefreshFeed, 30000);
            });
        }

        function renderPost(post) {
            const isVerified = (post.is_verified == 1);
            const isOwn = (post.user_id == currentUserId);
            let mediaHtml = '';
            if (post.media_path) {
                if (post.media_type === 'image') mediaHtml = `<div class="post-media"><img src="${BASE_URL}/uploads/${post.media_path}" loading="lazy"></div>`;
                else if (post.media_type === 'video') mediaHtml = `<div class="post-media"><video src="${BASE_URL}/uploads/${post.media_path}" controls preload="metadata"></video></div>`;
            }
            const menuHtml = isOwn ? `
                <div class="post-menu">
                    <button class="post-menu-btn" onclick="togglePostMenu(${post.id})">⋯</button>
                    <div id="menu-${post.id}" class="post-menu-dropdown" style="display:none;">
                        <div onclick="openEditModal(${post.id}, '${(post.content||'').replace(/'/g,"\\'")}')" class="menu-item">✏️ Modifier</div>
                        <div onclick="openDeleteModal(${post.id})" class="menu-item menu-item-danger">🗑️ Supprimer</div>
                    </div>
                </div>` : '';

            return `
                <div class="post-card" data-post-id="${post.id}" id="post-${post.id}">
                    <div class="post-header">
                        <img src="${BASE_URL}/uploads/${post.photo || 'default_profile.png'}" class="post-avatar" onerror="this.src='${BASE_URL}/uploads/default_profile.png'">
                        <div class="post-meta">
                            <div class="post-author">${post.prenom} ${post.nom} ${isVerified ? '<span class="verified-badge">✓</span>' : ''}</div>
                            <div class="post-time">${post.created_at}</div>
                        </div>
                        ${menuHtml}
                    </div>
                    <div class="post-content" id="post-content-${post.id}">${post.content || ''}</div>
                    ${mediaHtml}
                    <div class="post-actions">
                        <button class="action-btn" id="like-btn-${post.id}" onclick="likePost(${post.id}, this)">👍 ${post.likes_count}</button>
                        <button class="action-btn" onclick="toggleComments(${post.id})">💬 ${post.comments_count}</button>
                        <button class="action-btn" onclick="openShareModal(${post.id})">🔁 ${post.shares_count || 0}</button>
                    </div>
                    <div id="comments-${post.id}" style="display:none; padding:10px; background:#f9f9f9; border-top:1px solid #f0f0f0;">
                         <div id="comments-list-${post.id}"></div>
                         <div style="display:flex; gap:5px; margin-top:8px;">
                              <input type="text" id="comment-input-${post.id}" placeholder="Répondre..." style="flex:1; border-radius:15px; border:1px solid #ddd; padding:5px 12px; font-size:12px; outline:none;">
                              <button onclick="sendComment(${post.id})" style="border:none;background:none;cursor:pointer;">➤</button>
                         </div>
                    </div>
                </div>`;
        }

        function likePost(id, btn) {
            fetch(BASE_URL + '/Post/like/' + id)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    let count = parseInt(btn.innerText.match(/\d+/)[0]);
                    btn.innerText = (data.status === 'liked' ? '👍 ' + (count + 1) : '👍 ' + (count - 1));
                    btn.classList.toggle('active', data.status === 'liked');
                }
            });
        }

        function toggleComments(id) {
            const area = document.getElementById('comments-' + id);
            if (area.style.display === 'none') { area.style.display = 'block'; loadComments(id); } 
            else area.style.display = 'none';
        }

        function loadComments(id) {
            fetch(BASE_URL + '/Post/getComments/' + id)
            .then(res => res.json())
            .then(comments => {
                document.getElementById('comments-list-' + id).innerHTML = comments.map(c => `
                    <div style="display:flex; gap:8px; margin-bottom:8px;">
                         <img src="${BASE_URL}/uploads/${c.photo}" style="width:24px; height:24px; border-radius:50%;">
                         <div style="background:#fff; padding:6px 10px; border-radius:12px; font-size:12px; box-shadow:0 1px 1px rgba(0,0,0,0.05); flex:1;">
                              <strong>${c.prenom}</strong> ${c.content}
                         </div>
                    </div>`).join('');
            });
        }

        function sendComment(id) {
            const input = document.getElementById('comment-input-' + id);
            const val = input.value.trim();
            if (!val) return;
            fetch(BASE_URL + '/Post/comment/' + id, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'content=' + encodeURIComponent(val) })
            .then(res => res.json())
            .then(data => { if(data.success) { input.value = ''; loadComments(id); } });
        }

        // --- POST ACTIONS (EDIT, DELETE, SHARE) ---
        let currentPostId = null;

        function togglePostMenu(id) {
            const menu = document.getElementById('menu-' + id);
            const isVisible = menu.style.display === 'block';
            document.querySelectorAll('.post-menu-dropdown').forEach(m => m.style.display = 'none');
            menu.style.display = isVisible ? 'none' : 'block';
            
            // Close menu when clicking outside
            if (!isVisible) {
                setTimeout(() => {
                    document.onclick = (e) => {
                        if (!e.target.closest('.post-menu')) {
                            menu.style.display = 'none';
                            document.onclick = null;
                        }
                    };
                }, 10);
            }
        }

        function openEditModal(id, content) {
            currentPostId = id;
            document.getElementById('edit-post-content').value = content;
            document.getElementById('edit-modal').style.display = 'flex';
            document.getElementById('menu-' + id).style.display = 'none';
        }

        function submitEditPost() {
            const content = document.getElementById('edit-post-content').value.trim();
            if (!content) return;
            
            fetch(BASE_URL + '/Post/editPost/' + currentPostId, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'content=' + encodeURIComponent(content)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('post-content-' + currentPostId).innerText = content;
                    closeModal('edit-modal');
                } else {
                    alert(data.error || 'Erreur lors de la modification');
                }
            });
        }

        function openDeleteModal(id) {
            currentPostId = id;
            document.getElementById('delete-modal').style.display = 'flex';
            document.getElementById('menu-' + id).style.display = 'none';
        }

        function submitDeletePost() {
            fetch(BASE_URL + '/Post/deletePost/' + currentPostId)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const postEl = document.getElementById('post-' + currentPostId);
                    postEl.style.opacity = '0';
                    setTimeout(() => postEl.remove(), 300);
                    closeModal('delete-modal');
                } else {
                    alert(data.error || 'Erreur lors de la suppression');
                }
            });
        }

        function openShareModal(id) {
            currentPostId = id;
            const container = document.getElementById('share-friends-list');
            container.innerHTML = '<div style="text-align:center; padding:10px; color:#aaa;">Chargement...</div>';
            document.getElementById('share-modal').style.display = 'flex';

            safeFetch(BASE_URL + '/Dashboard/getUserListJson')
            .then(users => {
                const friends = users.filter(u => u.friendship_status === 'accepted');
                if (friends.length === 0) {
                    container.innerHTML = '<div style="text-align:center; padding:20px; color:#888;">Vous n\'avez pas d\'amis avec qui partager.</div>';
                    return;
                }
                container.innerHTML = friends.map(friend => `
                    <div onclick="shareWithFriend(${friend.id})" class="friend-share-item">
                        <img src="${BASE_URL}/uploads/${friend.photo}" class="post-avatar" style="width:30px; height:30px;">
                        <span>${friend.prenom} ${friend.nom}</span>
                        <span class="share-send-btn">Envoyer</span>
                    </div>
                `).join('');
            });
        }

        function shareWithFriend(friendId) {
            // Logic: sharing here creates a new shared entry in DB, 
            // but for the UI we just show success and close
            fetch(BASE_URL + '/Post/share/' + currentPostId, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'receiver_id=' + friendId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update the share counter on the post
                    const shareBtn = document.querySelector(`#post-${currentPostId} .action-btn:last-child`);
                    if (shareBtn) shareBtn.innerHTML = `🔁 ${data.shares_count}`;
                    
                    alert('Partagé avec succès !');
                    closeModal('share-modal');
                } else {
                    alert(data.error || 'Erreur lors du partage');
                }
            });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function observePosts() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const postId = entry.target.getAttribute('data-post-id');
                        fetch(BASE_URL + '/Post/trackView/' + postId);

                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            document.querySelectorAll('.post-card').forEach(card => observer.observe(card));
        }

        function updateSidebarData() {
            safeFetch(BASE_URL + '/Post/getSidebarData')
            .then(data => {
                const verifiedList = document.getElementById('verified-users-list');
                if (data.verified) {
                    verifiedList.innerHTML = data.verified.map(u => `
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                            <img src="${BASE_URL}/uploads/${u.photo}" style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                            <div style="font-size:12px; font-weight:bold;">${u.prenom} ${u.nom} <span class="verified-badge">✓</span></div>
                        </div>`).join('');
                }
                document.getElementById('online-count').innerText = data.stats.online_count;
                document.getElementById('global-views-count').innerText = data.stats.total_views;
            });
        }

        // --- MESSAGING SIDEBAR ---
        function switchSidebarTab(tab, btn) {
            currentSidebarTab = tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            refreshUserList();
        }

        function refreshUserList() {
            safeFetch(BASE_URL + '/Dashboard/getUserListJson')
            .then(users => {
                const list = document.getElementById('users-list');
                let filtered = (currentSidebarTab === 'chats') 
                    ? users.filter(u => u.friendship_status === 'accepted')
                    : users.filter(u => u.friendship_status !== 'accepted');

                list.innerHTML = filtered.map(user => `
                    <div class="user-item" onclick="${user.friendship_status === 'accepted' ? `openChatOverlay(${user.id}, '${user.prenom} ${user.nom}', '${user.photo}')` : ''}" style="display:flex; align-items:center; padding:12px 15px; gap:12px; border-bottom:1px solid #f5f5f5; cursor:pointer;">
                        <img src="${BASE_URL}/uploads/${user.photo}" style="width:42px; height:42px; border-radius:50%; object-fit:cover;">
                        <div style="flex:1;">
                             <div style="font-weight:bold; font-size:14px; color:#111;">${user.prenom} ${user.nom} ${user.is_verified == 1 ? '<span class="verified-badge" style="background:#1d9bf0;">✓</span>' : ''}</div>
                             <div style="font-size:11px; color:#888;">${user.is_online ? 'En ligne 🟢' : 'Hors ligne'}</div>
                        </div>
                    </div>`).join('');
            });
        }

        function openChatOverlay(id, name, photo) {
            document.body.classList.add('chat-open');
            openChat(id, name, photo); 
        }
        function closeChatOverlay() { document.body.classList.remove('chat-open'); closeChat(); }

        loadFeed();
        refreshUserList();
        updateSidebarData();
        setInterval(refreshUserList, 30000);
        setInterval(updateSidebarData, 60000);
    </script>
</body>
</html>
