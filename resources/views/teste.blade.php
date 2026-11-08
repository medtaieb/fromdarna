<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test FromDarna</title>
</head>
<body style="font-family: Arial; padding: 20px;">

    <h1>Test de l'environnement Laravel</h1>

    <p><strong>Version de Laravel :</strong> {{ app()->version() }}</p>
    <p><strong>Version de PHP :</strong> {{ phpversion() }}</p>

    <hr>

    <h2>Test de la connexion à la Base de Données</h2>
    
    @php
        try {
            \DB::connection()->getPdo();
            echo '<p style="color: green; font-weight: bold;">✅ Connexion réussie à la base de données : ' . \DB::connection()->getDatabaseName() . '</p>';
        } catch (\Exception $e) {
            echo '<p style="color: red; font-weight: bold;">❌ Erreur de connexion :</p>';
            echo '<p style="color: red;">' . $e->getMessage() . '</p>';
        }
    @endphp

</body>
</html>