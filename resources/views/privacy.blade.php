<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $appName = config('app.name', 'Madeline Cafe Bistro');
            $contactEmail = config('mail.from.address') ?: 'info@bbtspr.com';
            $lastUpdated = '10 de febrero de 2026';
        @endphp
        <title>Politica de Privacidad | {{ $appName }}</title>
        <meta name="description" content="Politica de privacidad de {{ $appName }}." />
        <style>
            :root {
                color-scheme: light;
            }
            body {
                margin: 0;
                font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
                background: #f8fafc;
                color: #0f172a;
                line-height: 1.6;
            }
            main {
                max-width: 900px;
                margin: 0 auto;
                padding: 40px 24px 64px;
            }
            header {
                margin-bottom: 32px;
            }
            h1 {
                margin: 0 0 8px;
                font-size: 2.25rem;
                line-height: 1.2;
            }
            h2 {
                margin: 28px 0 8px;
                font-size: 1.25rem;
            }
            p {
                margin: 8px 0;
            }
            ul {
                margin: 8px 0 0 20px;
                padding: 0;
            }
            li {
                margin: 6px 0;
            }
            .meta {
                color: #475569;
                font-size: 0.95rem;
            }
            .card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 20px 24px;
                box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
            }
        </style>
    </head>
    <body>
        <main>
            <header class="card">
                <h1>Politica de Privacidad</h1>
                <p class="meta">Ultima actualizacion: {{ $lastUpdated }}</p>
                <p>
                    Esta politica describe como {{ $appName }} recopila, usa y comparte informacion cuando utilizas
                    la aplicacion movil {{ $appName }} (Android) y los servicios relacionados en nuestro sitio web.
                    La app esta destinada a uso operativo en Puerto Rico (EE. UU.).
                </p>
            </header>

            <section class="card">
                <h2>Informacion que recopilamos</h2>
                <ul>
                    <li>Informacion de cuenta: nombre, correo y credenciales de acceso del personal autorizado.</li>
                    <li>Informacion operativa: ordenes, mesas, tickets, propinas, notas, turnos y estado de pagos.</li>
                    <li>Informacion de clientes: nombre, telefono, correo y datos de fidelidad o lista de espera cuando se proveen.</li>
                    <li>Informacion de pagos y transacciones: metodo, monto, estado, e identificadores de pago generados por el proveedor.</li>
                    <li>Datos tecnicos: direccion IP, modelo del dispositivo, sistema operativo, version de la app y registros de errores.</li>
                    <li>Ubicacion precisa y NFC: solo si otorgas permisos, para habilitar funciones como Tap to Pay y requisitos de los proveedores.</li>
                </ul>
            </section>

            <section class="card">
                <h2>Como usamos la informacion</h2>
                <ul>
                    <li>Autenticar usuarios y administrar accesos.</li>
                    <li>Gestionar operaciones del restaurante, ordenes y pagos.</li>
                    <li>Enviar recibos o notificaciones relacionadas con el servicio.</li>
                    <li>Mejorar la estabilidad, seguridad y rendimiento de la app.</li>
                    <li>Cumplir obligaciones legales y fiscales.</li>
                </ul>
            </section>

            <section class="card">
                <h2>Compartir informacion</h2>
                <p>
                    Compartimos informacion solo cuando es necesario para operar el servicio, por ejemplo con:
                </p>
                <ul>
                    <li>Procesadores de pago (por ejemplo, Stripe y Clover) para completar transacciones.</li>
                    <li>Proveedores de infraestructura y comunicaciones (correo o SMS) para enviar notificaciones.</li>
                    <li>Autoridades legales si es requerido por ley.</li>
                </ul>
                <p>
                    No vendemos informacion personal.
                </p>
            </section>

            <section class="card">
                <h2>Pagos</h2>
                <p>
                    Las transacciones se procesan a traves de proveedores externos. La informacion completa de la tarjeta
                    es manejada por dichos proveedores y no se almacena en nuestros servidores.
                </p>
            </section>

            <section class="card">
                <h2>Retencion</h2>
                <p>
                    Conservamos la informacion el tiempo necesario para operar el servicio y cumplir obligaciones legales.
                    Puedes solicitar acceso o eliminacion segun la ley aplicable.
                </p>
            </section>

            <section class="card">
                <h2>Seguridad</h2>
                <p>
                    Implementamos medidas tecnicas y organizativas razonables para proteger la informacion. Ningun metodo
                    de transmision o almacenamiento es 100% seguro.
                </p>
            </section>

            <section class="card">
                <h2>Menores de edad</h2>
                <p>
                    La app no esta dirigida a menores de 13 anos y no recopilamos intencionalmente informacion de menores.
                    Si crees que recibimos datos de un menor, contactanos para eliminarlos.
                </p>
            </section>

            <section class="card">
                <h2>Transferencias internacionales</h2>
                <p>
                    Nuestros servidores y proveedores pueden estar ubicados en Estados Unidos. Al usar la app, aceptas que
                    la informacion sea procesada alli.
                </p>
            </section>

            <section class="card">
                <h2>Cambios a esta politica</h2>
                <p>
                    Podemos actualizar esta politica ocasionalmente. Publicaremos la version actualizada en esta pagina.
                </p>
            </section>

            <section class="card">
                <h2>Contacto</h2>
                <p>
                    Para preguntas o solicitudes sobre privacidad, escribenos a: {{ $contactEmail }}.
                </p>
            </section>
        </main>
    </body>
</html>
