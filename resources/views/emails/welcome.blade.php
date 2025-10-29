<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a SGC</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .welcome-message {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .user-info {
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .user-info h3 {
            margin-top: 0;
            color: #34495e;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido a SGC!</h1>
            <p>Sistema de Gestión de Citas Médicas</p>
        </div>

        <div class="welcome-message">
            <p>Hola <strong>{{ $user->name ?? $user->nombre }} {{ $user->apellido ?? '' }}</strong>,</p>
            <p>¡Bienvenido al Sistema de Gestión de Citas! Tu cuenta ha sido creada exitosamente.</p>
        </div>

        <div class="user-info">
            <h3>Información de tu cuenta:</h3>
            <p><strong>Tipo de usuario:</strong> {{ ucfirst($userType) }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            @if(isset($user->telefono) && $user->telefono)
                <p><strong>Teléfono:</strong> {{ $user->telefono }}</p>
            @endif
        </div>

        <p>Ahora puedes acceder a tu cuenta y comenzar a utilizar nuestros servicios. Si eres un paciente, puedes agendar citas con nuestros especialistas. Si eres un doctor, puedes gestionar tus horarios y citas.</p>

        <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            <p>&copy; 2024 SGC - Sistema de Gestión de Citas. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>