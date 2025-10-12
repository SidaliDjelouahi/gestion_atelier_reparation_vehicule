<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

// 🔒 Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm = trim($_POST["confirm"]);
    $rank = "user"; // par défaut

    if (empty($username) || empty($password) || empty($confirm)) {
        $message = "Veuillez remplir tous les champs.";
    } elseif ($password !== $confirm) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifie si le nom d'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $message = "Ce nom d'utilisateur existe déjà.";
        } else {
            // Hash du mot de passe
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password, rank) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed, $rank])) {
                header("Location: index.php?signup=success");
                exit;
            } else {
                $message = "Erreur lors de la création du compte.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte - Atelier de Réparation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #4ca1af, #2c3e50);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-container {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }

        .signup-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .btn-custom {
            background-color: #2c3e50;
            color: white;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background-color: #1a252f;
        }

        .link {
            text-align: center;
            margin-top: 1rem;
        }

        .logo {
            text-align: center;
            margin-bottom: 1rem;
        }

        .logo i {
            font-size: 60px;
            color: #2c3e50;
        }

        @media (max-width: 576px) {
            .signup-container {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="signup-container">
    <div class="logo">
        <i class="bi bi-tools"></i>
    </div>
    <h2>Créer un compte</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger text-center"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" placeholder="Choisissez un nom d'utilisateur" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" placeholder="Créez un mot de passe" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="confirm" class="form-control" placeholder="Confirmez votre mot de passe" required>
        </div>

        <button type="submit" class="btn btn-custom w-100">S'inscrire</button>

        <div class="link">
            <a href="index.php">Déjà un compte ? Se connecter</a>
        </div>
    </form>
</div>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

</body>
</html>
