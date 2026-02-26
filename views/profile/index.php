<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - SMSOnline</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .profile-card {
            text-align: center;
            margin-bottom: 25px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--whatsapp-green);
            box-shadow: var(--shadow);
            margin-bottom: 15px;
        }
        .last-update-hint {
            font-size: 11px;
            color: #d32f2f;
            background: rgba(211, 47, 47, 0.05);
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(211, 47, 47, 0.1);
        }
        #response-msg {
            display: none;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body class="auth-bg">
    <div id="top-progress-bar" class="progress-bar-container">
        <div class="progress-bar-fill"></div>
    </div>

    <header style="position: fixed; width: 100%; top: 0; left: 0;">
        <div class="header-content">
            <div class="header-user-info">
                <a href="<?php echo BASE_URL; ?>/Dashboard" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
                    <img src="<?php echo BASE_URL; ?>/uploads/<?php echo $_SESSION['photo']; ?>" class="user-self-avatar" alt="Moi">
                    <h1 style="font-size: 24px; margin: 0;">SMSOnline</h1>
                </a>
            </div>
            <div class="header-nav">
                <a href="<?php echo BASE_URL; ?>/Dashboard" class="btn-premium nav-btn" style="font-size: 11px; padding: 8px 15px; width: auto; background: rgba(255,255,255,0.15);">Retour</a>
            </div>
        </div>
    </header>

    <div class="auth-container" style="max-width: 500px; margin-top: 80px;">
        <div class="auth-header">
            <h1>Profil Utilisateur</h1>
            <p>Gérez vos informations personnelles</p>
        </div>

        <div class="profile-card">
            <img src="<?php echo BASE_URL; ?>/uploads/<?php echo $data['user']['photo']; ?>" alt="Ma Photo" class="profile-avatar">
            <h2 style="color: var(--whatsapp-dark-green);"><?php echo $data['user']['prenom'] . ' ' . $data['user']['nom']; ?></h2>
            <span style="color: #666; font-size: 14px;">Membre depuis <?php echo date('d/m/Y', strtotime($data['user']['created_at'])); ?></span>
        </div>

        <div id="response-msg"></div>

        <?php if (!$data['cooldown']['can_update']): ?>
        <div class="last-update-hint">
            <strong>🔒 Cooldown :</strong> Pour des raisons de sécurité, vous pourrez modifier votre profil dans <strong><?php echo $data['cooldown']['days_left']; ?> jour(s)</strong>.
        </div>
        <?php else: ?>
        <div class="last-update-hint" style="color: #666; background: rgba(0,0,0,0.03); border-color: rgba(0,0,0,0.05);">
            <strong>Sécurité :</strong> Les identifiants ne peuvent être modifiés qu'une fois tous les 14 jours.
        </div>
        <?php endif; ?>

        <form id="profile-form" action="<?php echo BASE_URL; ?>/Profile/update" method="POST">
            <?php $disabled = !$data['cooldown']['can_update'] ? 'disabled' : ''; ?>
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" class="form-control" value="<?php echo $data['user']['username']; ?>" required <?php echo $disabled; ?>>
            </div>

            <div class="form-group">
                <label for="telephone">Numéro de Téléphone</label>
                <input type="tel" name="telephone" id="telephone" class="form-control" value="<?php echo $data['user']['telephone']; ?>" required <?php echo $disabled; ?>>
            </div>

            <div class="form-group">
                <label for="password">Nouveau Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Laisser vide pour garder l'actuel" <?php echo $disabled; ?>>
                <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666;">
                    <input type="checkbox" id="togglePassword" style="width: 16px; height: 16px; cursor: pointer;" <?php echo $disabled; ?>>
                    <label for="togglePassword" style="cursor: pointer; margin: 0; font-weight: normal;">Afficher le mot de passe</label>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <a href="<?php echo BASE_URL; ?>/Dashboard" class="btn-premium" style="background: #666; text-decoration: none; text-align: center; flex: 1; text-transform: none; display: flex; align-items: center; justify-content: center;">Annuler</a>
                <button type="submit" id="submit-btn" class="btn-premium" style="flex: 2; <?php echo $disabled ? 'opacity: 0.6; cursor: not-allowed; background: #ccc;' : ''; ?>" <?php echo $disabled; ?>>Mettre à jour</button>
            </div>
        </form>
    </div>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        document.getElementById('togglePassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });

        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const btn = document.getElementById('submit-btn');
            const progress = document.getElementById('top-progress-bar');
            const msg = document.getElementById('response-msg');
            
            btn.disabled = true;
            progress.style.display = 'block';
            msg.style.display = 'none';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                progress.style.display = 'none';
                btn.disabled = false;
                
                if (data.success) {
                    msg.style.display = 'block';
                    msg.style.background = 'rgba(37, 211, 102, 0.1)';
                    msg.style.color = '#075E54';
                    msg.style.border = '1px solid rgba(37, 211, 102, 0.2)';
                    msg.innerText = '✅ ' + data.success;
                } else if (data.error) {
                    msg.style.display = 'block';
                    msg.style.background = 'rgba(255, 0, 0, 0.1)';
                    msg.style.color = '#d32f2f';
                    msg.style.border = '1px solid rgba(255, 0, 0, 0.2)';
                    msg.innerText = '⚠️ ' + data.error;
                }
            })
            .catch(() => {
                progress.style.display = 'none';
                btn.disabled = false;
                alert('Une erreur réseau est survenue.');
            });
        });
    </script>
</body>
</html>
