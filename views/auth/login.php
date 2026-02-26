<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SMSOnline</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><circle cx='16' cy='16' r='16' fill='%23128c7e'/><text x='16' y='22' text-anchor='middle' font-size='18' fill='white'>💬</text></svg>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="auth-bg">
    <div class="auth-container">
        <div class="auth-header">
            <h1>SMSOnline</h1>
            <p>Connectez-vous pour commencer à discuter</p>
        </div>

        <?php if(isset($data['error'])): ?>
            <div style="background: rgba(255, 0, 0, 0.1); color: #d32f2f; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; border: 1px solid rgba(255, 0, 0, 0.2);">
                <?php echo $data['error']; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/Auth/login" method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="votre_username" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666;">
                    <input type="checkbox" id="togglePassword" style="width: 16px; height: 16px; cursor: pointer;">
                    <label for="togglePassword" style="cursor: pointer; margin: 0; font-weight: normal;">Afficher le mot de passe</label>
                </div>
            </div>

            <button type="submit" class="btn-premium">Se Connecter</button>
        </form>

        <script>
            document.getElementById('togglePassword').addEventListener('change', function() {
                const passwordInput = document.getElementById('password');
                passwordInput.type = this.checked ? 'text' : 'password';
            });
        </script>

        <p style="text-align: center; margin-top: 25px; font-size: 14px; color: #666;">
            Pas de compte ? <a href="<?php echo BASE_URL; ?>/Auth/register" style="color: var(--whatsapp-dark-green); font-weight: bold; text-decoration: none;">S'inscrire gratuitement</a>
        </p>
    </div>
</body>
</html>
