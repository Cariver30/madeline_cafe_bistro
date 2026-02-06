<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activa tu acceso</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f5; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden;">
        <tr>
            <td style="padding: 32px;">
                <p style="text-transform: uppercase; letter-spacing: 0.3em; font-size: 12px; color: #a16207; margin-bottom: 16px;">{{ config('app.name', 'Madeleine Cafe Bistro') }} · Accesos</p>
                <h1 style="font-size: 22px; margin: 0 0 12px; color: #111827;">Hola {{ $user->name }},</h1>
                <p style="color: #4b5563; line-height: 1.5; margin-bottom: 24px;">
                    Fuiste invitado como {{ $roleLabel ?? 'mesero' }} para {{ $roleDescription ?? 'gestionar mesas y fidelización' }}. Para activar tu cuenta y definir una contraseña segura, haz clic en el botón:
                </p>
                <p style="text-align: center; margin: 32px 0;">
                    <a href="{{ $url }}" style="background: #f59e0b; color: #111827; padding: 12px 24px; border-radius: 9999px; text-decoration: none; font-weight: bold;">Crear mi contraseña</a>
                </p>
                <p style="color: #6b7280; font-size: 14px;">
                    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                    <span style="color: #1d4ed8;">{{ $url }}</span>
                </p>
                <p style="color: #9ca3af; font-size: 12px; margin-top: 32px;">Este enlace vence en 48 horas. Si no solicitaste este acceso, ignora este mensaje.</p>
            </td>
        </tr>
    </table>
</body>
</html>
