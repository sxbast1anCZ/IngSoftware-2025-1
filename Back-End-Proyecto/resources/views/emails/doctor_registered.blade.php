<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="es">

<head>
	<title>Registro de Médico</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" type="text/css">
	<style>
		* {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			padding: 0;
			font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			background-color: #f8f9fa;
		}

		.email-container {
			max-width: 600px;
			margin: 0 auto;
			background-color: #ffffff;
			border-radius: 8px;
			overflow: hidden;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.header {
			background-color: #ff9f1c;
			padding: 40px 20px;
			text-align: center;
		}

		.logo-circle {
			width: 80px;
			height: 80px;
			background-color: #ffffff;
			border-radius: 50%;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 20px;
		}

		.logo-icon {
			font-size: 32px;
			color: #333;
		}

		.content {
			padding: 40px 30px;
			text-align: center;
		}

		.title {
			font-size: 24px;
			font-weight: 600;
			color: #333333;
			margin: 0 0 20px 0;
			line-height: 1.3;
		}

		.subtitle {
			font-size: 16px;
			color: #666666;
			margin: 0 0 30px 0;
			line-height: 1.5;
		}

		.welcome-text {
			font-size: 18px;
			font-weight: 500;
			color: #333333;
			margin: 0 0 15px 0;
		}

		.credentials-box {
			background-color: #f8f9fa;
			border: 1px solid #e9ecef;
			border-radius: 8px;
			padding: 25px;
			margin: 30px 0;
			text-align: left;
		}

		.credential-item {
			margin: 12px 0;
			font-size: 14px;
			color: #495057;
		}

		.credential-label {
			font-weight: 600;
			color: #333333;
		}

		.password-value {
			background-color: #fff3cd;
			border: 1px solid #ffeaa7;
			padding: 8px 12px;
			border-radius: 4px;
			font-family: 'Courier New', monospace;
			font-size: 16px;
			font-weight: 600;
			color: #856404;
			display: inline-block;
			margin-left: 10px;
		}

		.button {
			display: inline-block;
			background-color: #ff9f1c;
			color: #ffffff;
			padding: 14px 30px;
			border-radius: 6px;
			text-decoration: none;
			font-weight: 600;
			font-size: 16px;
			margin: 20px 0;
			transition: background-color 0.3s ease;
		}

		.button:hover {
			background-color: #e8890b;
		}

		.security-note {
			background-color: #fff3cd;
			border: 1px solid #ffeaa7;
			border-radius: 6px;
			padding: 15px;
			margin: 20px 0;
			font-size: 13px;
			color: #856404;
		}

		.footer {
			background-color: #f8f9fa;
			padding: 25px 30px;
			text-align: center;
			border-top: 1px solid #e9ecef;
		}

		.footer-text {
			font-size: 12px;
			color: #868e96;
			margin: 0;
			line-height: 1.5;
		}

		@media (max-width: 600px) {
			.content {
				padding: 30px 20px;
			}
			
			.credentials-box {
				padding: 20px;
			}
			
			.title {
				font-size: 22px;
			}
			
			.button {
				display: block;
				text-align: center;
				margin: 20px 0;
			}
		}
	</style>
</head>

<body>
	<div class="email-container">
		<!-- Header -->
		<div class="header">
			<div class="logo-circle">
				<div class="logo-icon">🏥</div>
			</div>
		</div>

		<!-- Content -->
		<div class="content">
			<h1 class="title">Bienvenido al Sistema Médico</h1>
			
			<p class="welcome-text">Dr./Dra. {{ $user->name }} {{ $user->lastname }}</p>
			
			<p class="subtitle">
				Su cuenta ha sido creada exitosamente. Ya puede acceder a todas las funcionalidades disponibles para profesionales de la salud.
			</p>

			<!-- Credentials Box -->
			<div class="credentials-box">
				<h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">Sus credenciales de acceso:</h3>
				
				<div class="credential-item">
					<span class="credential-label">Usuario:</span> Su email registrado
				</div>
				
				<div class="credential-item">
					<span class="credential-label">Contraseña:</span>
					<span class="password-value">{{ $password }}</span>
				</div>
			</div>

			<!-- CTA Button -->
			<a href="http://localhost:4200/login" class="button">Acceder al Sistema</a>
		</div>

		<!-- Footer -->
		<div class="footer">
			<p class="footer-text">
				© 2025 Sistema Médico. Todos los derechos reservados.<br>
				Este es un correo automático, por favor no responder directamente.
			</p>
		</div>
	</div>
</body>

</html>