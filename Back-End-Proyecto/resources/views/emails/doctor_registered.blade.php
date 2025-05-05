<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registro de Médico</title>
</head>
<body>
    <h2>Bienvenido, {{ $user->name }} {{ $user->lastname }}</h2>
    <p>Has sido registrado como médico en el sistema.</p>
    <p>Tu contraseña es: <strong>{{ $password }}</strong></p>
</body>
</html>
