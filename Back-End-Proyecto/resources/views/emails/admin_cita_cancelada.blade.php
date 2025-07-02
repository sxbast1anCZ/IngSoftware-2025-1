<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cita Modificada</title>
</head>
<body>
    <h2>Estimado/a {{ $paciente->name }},</h2>

    <p>Le informamos que su cita médica ha sido modificada por el administrador.</p>

    <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
    <p><strong>Motivo:</strong> {{ $cita->reason }}</p>

    <p>Por favor, revise su cuenta en el sistema para confirmar los detalles.</p>

    <p>Saludos cordiales,<br>Equipo de SaludYa</p>
</body>
</html>