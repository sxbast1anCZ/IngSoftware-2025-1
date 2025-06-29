<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Licencia Médica - SaludYa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 120px;
            margin-bottom: 10px;
        }

        .section {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
        }

        h1 {
            font-size: 24px;
            color: #009688;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #555;
        }

        p {
            margin: 5px 0;
        }

        strong {
            color: #000;
        }

        .footer {
            text-align: right;
            font-size: 12px;
            margin-top: 40px;
            color: #777;
        }

        .sello {
            width: 80px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('storage/images/saludya_logo.png') }}" class="logo" alt="Logo SaludYa">
        <h1>Licencia Médica</h1>
    </div>

    <div class="section">
        <h2>Paciente</h2>
        <p><strong>Nombre:</strong> {{ $paciente->name }}</p>
        <p><strong>Apellido:</strong> {{ $paciente->lastname ?? 'No disponible' }}</p>
        <p><strong>RUT:</strong> {{ $paciente->rut }}</p>
    </div>

    <div class="section">
        <h2>Médico Tratante</h2>
        <p><strong>Nombre:</strong> {{ $medico->name ?? 'No disponible' }}</p>
        <p><strong>Apellido:</strong> {{ $medico->lastname ?? 'No disponible' }}</p>
        <p><strong>RUT:</strong> {{ $medico->rut ?? 'No disponible' }}</p>
        <p><strong>Especialidad:</strong> {{ $medico->specialty->name ?? 'No especificada' }}</p>
    </div>

    <div class="section">
        <h2>Detalles de la Licencia</h2>
        <p><strong>Fecha de inicio:</strong> {{ \Carbon\Carbon::parse($licencia->fecha_inicio)->format('d/m/Y') }}</p>
        <p><strong>Fecha de término:</strong> {{ \Carbon\Carbon::parse($licencia->fecha_fin)->format('d/m/Y') }}</p>
        <p><strong>Días de reposo:</strong> {{ $licencia->dias }}</p>
        <p><strong>Motivo:</strong> {{ $licencia->motivo }}</p>
    </div>

    <div class="footer">
        <p>Emitido por el sistema SaludYa - {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
        @if(file_exists(public_path('storage/images/sello.png')))
            <img src="{{ public_path('storage/images/sello.png') }}" class="sello" alt="Sello institucional">
        @endif
    </div>
</body>
</html>
