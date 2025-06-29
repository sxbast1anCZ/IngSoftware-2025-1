<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Cita Médica</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #28a745;">¡Cita Confirmada!</h2>

        <p>Hola <strong>{{ $paciente->name }} {{ $paciente->lastname }}</strong>,</p>

        <p>Te confirmamos que tu cita médica ha sido <strong>agendada correctamente</strong>. Aquí están los detalles:</p>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td><strong>Doctor(a):</strong></td>
                <td>{{ $doctor->name }} {{ $doctor->lastname }}</td>
            </tr>
            <tr>
                <td><strong>Especialidad:</strong></td>
                <td>{{ $doctor->specialty->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Fecha y hora:</strong></td>
                <td>{{ $fechaHora }}</td>
            </tr>
            <tr>
                <td><strong>Duración:</strong></td>
                <td>{{ $duracion }} minutos</td>
            </tr>
            <tr>
                <td><strong>Motivo:</strong></td>
                <td>{{ $cita->reason }}</td>
            </tr>
            <tr>
                <td><strong>Precio:</strong></td>
                <td>${{ number_format($cita->price, 0, ',', '.') }}</td>
            </tr>
        </table>

        <p style="margin-top: 20px;">Gracias por confiar en nosotros.</p>

        <p style="font-size: 12px; color: #999;">Este es un correo automático, por favor no respondas.</p>
    </div>
</body>
</html>
