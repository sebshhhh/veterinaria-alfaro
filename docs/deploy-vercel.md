# Despliegue en Vercel

Este proyecto es un sistema Laravel para gestión veterinaria. Para una empresa se recomienda usar un repositorio privado en GitHub y conectar Vercel desde ese repositorio.

## Decisión recomendada

- Repositorio: privado.
- Base de datos: MySQL externa, no XAMPP.
- Variables sensibles: solo en Vercel, nunca en GitHub.
- Archivos subidos: para producción real usar almacenamiento externo como S3, Cloudinary o similar.

## Variables de entorno necesarias en Vercel

Configurar en Project Settings > Environment Variables:

```env
APP_NAME="Dra. Alfaro"
APP_ENV=production
APP_KEY=base64:GENERAR_CON_php_artisan_key_generate_show
APP_DEBUG=false
APP_URL=https://tu-dominio.vercel.app

DB_CONNECTION=mysql
DB_HOST=HOST_DE_TU_MYSQL
DB_PORT=3306
DB_DATABASE=NOMBRE_DB
DB_USERNAME=USUARIO_DB
DB_PASSWORD=PASSWORD_DB

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_CHANNEL=stderr
VIEW_COMPILED_PATH=/tmp/views
```

## Pasos

1. Crear repositorio privado en GitHub.
2. Subir el proyecto sin `.env`, `vendor`, `node_modules`, backups ni archivos subidos.
3. Crear una base de datos MySQL externa.
4. Importar la estructura con migraciones:

```bash
php artisan migrate --force
```

5. En Vercel, importar el repositorio desde GitHub.
6. Agregar las variables de entorno.
7. Desplegar.

## Importante

Vercel ejecuta Laravel como función serverless. Eso sirve para demo o despliegue ligero, pero no conserva archivos subidos dentro del servidor. Si la clínica usará fotos de mascotas en producción, se debe conectar un servicio externo de almacenamiento.
