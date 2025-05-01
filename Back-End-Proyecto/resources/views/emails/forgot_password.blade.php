<!DOCTYPE html>
<html>
<head>
    <title>Restablecer contraseña</title>
</head>
<body>
    <h2>Restablecer Contraseña</h2>
    <p>Hola {{ $user->name }},</p>
    <p>Para restablecer tu contraseña, ingresa el siguiente código:</p>
    <h3>{{ $resetCode }}</h3>
    <p>Este código expirará en 5 minutos.</p>
</body>
</html>