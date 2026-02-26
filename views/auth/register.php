<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - SMSOnline</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .auth-container { max-width: 600px; }
        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 500px) { .grid-form { grid-template-columns: 1fr; } }
        #error-msg {
            display: none;
            background: rgba(255, 0, 0, 0.1);
            color: #d32f2f;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid rgba(255, 0, 0, 0.2);
        }
    </style>
</head>
<body class="auth-bg">
    <div id="top-progress-bar" class="progress-bar-container">
        <div class="progress-bar-fill"></div>
    </div>

    <div class="auth-container">
        <div class="auth-header">
            <h1>Créer un compte</h1>
            <p>Rejoignez la communauté SMSOnline aujourd'hui</p>
        </div>

        <div id="error-msg"></div>

        <form id="register-form" action="<?php echo BASE_URL; ?>/Auth/register" method="POST" enctype="multipart/form-data">
            <div class="grid-form">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" placeholder="Ex: Dupont" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Ex: Jean" required>
                </div>
            </div>

            <div class="form-group">
                <label for="cni">Numéro CNI</label>
                <input type="text" name="cni" id="cni" class="form-control" placeholder="Votre numéro d'identité" required>
            </div>

            <div class="grid-form">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="mon_username" required>
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" name="telephone" id="telephone" class="form-control" placeholder="+237..." required>
                </div>
            </div>

            <div class="grid-form">
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666;">
                        <input type="checkbox" id="togglePassword" style="width: 16px; height: 16px; cursor: pointer;">
                        <label for="togglePassword" style="cursor: pointer; margin: 0; font-weight: normal;">Afficher</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" name="date_naissance" id="date_naissance" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="photo">Photo de profil (Optionnel)</label>
                <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
            </div>

            <button type="submit" id="submit-btn" class="btn-premium">S'inscrire Maintenant</button>
        </form>

        <p style="text-align: center; margin-top: 25px; font-size: 14px; color: #666;">
            Déjà un compte ? <a href="<?php echo BASE_URL; ?>/Auth/login" style="color: var(--whatsapp-dark-green); font-weight: bold; text-decoration: none;">Se connecter</a>
        </p>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });

        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Client-side age validation
            const dobValue = document.getElementById('date_naissance').value;
            if (dobValue) {
                const dob = new Date(dobValue);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                if (age < 15) {
                    alert("Vous devez avoir au moins 15 ans pour vous inscrire.");
                    return;
                }
            }

            const form = this;
            const btn = document.getElementById('submit-btn');
            const progress = document.getElementById('top-progress-bar');
            const errorMsg = document.getElementById('error-msg');
            
            btn.disabled = true;
            progress.style.display = 'block';
            errorMsg.style.display = 'none';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                progress.style.display = 'none';
                
                if (data.success) {
                    window.location.href = '<?php echo BASE_URL; ?>/Auth/login?registered=1';
                } else if (data.errors) {
                    btn.disabled = false;
                    errorMsg.style.display = 'block';
                    let list = '<ul style="margin-left: 20px;">';
                    Object.values(data.errors).forEach(err => {
                        list += `<li>${err}</li>`;
                    });
                    list += '</ul>';
                    errorMsg.innerHTML = list;
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
