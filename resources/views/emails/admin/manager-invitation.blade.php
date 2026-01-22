<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Invitación de acceso</title>
</head>
<body style="font-family: 'Helvetica Neue', Arial, sans-serif; background-color:#f8fafc; color:#0f172a; margin:0; padding:0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding:32px 0;">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" align="center" style="margin:auto; background:#ffffff; border-radius:24px; box-shadow:0 20px 60px rgba(15,23,42,0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:32px 40px 16px;">
                            <p style="text-transform:uppercase; letter-spacing:0.35em; color:#f97316; font-size:11px; margin:0 0 12px;">Panel administrativo</p>
                            <h1 style="font-size:24px; margin:0 0 12px;">Hola {{ $user->name }}</h1>
                            <p style="margin:0; font-size:15px; line-height:1.6;">
                                Te hemos invitado a colaborar como gerente en el panel de Café Negro.
                                Para activar tu acceso y definir una contraseña segura, presiona el botón a continuación.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 24px;">
                            <p style="margin:0 0 16px; font-size:14px; color:#475569;">El enlace expira cuando completes el registro o generes una nueva invitación.</p>
                            <a href="{{ $url }}" style="display:inline-block; padding:14px 28px; border-radius:999px; text-decoration:none; font-weight:600; background:#0f172a; color:#ffffff;">Activar mi acceso</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 40px 32px; font-size:13px; color:#64748b;">
                            <p style="margin:0 0 12px;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                            <p style="word-break:break-all; margin:0; color:#334155;">
                                <a href="{{ $url }}" style="color:#0f172a; text-decoration:none;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="text-align:center; font-size:12px; color:#94a3b8; margin-top:24px;">
                    &copy; {{ date('Y') }} Café Negro · Seguridad ante todo.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
