# 🚀 Incident Management System (Technical Test)

Este repositorio contiene la prueba técnica para un **Sistema de Gestión de Incidentes**, desarrollado utilizando estándares Senior de la industria con **Laravel 11** en el Backend y **Vue 3** en el Frontend. 

El código respeta fielmente los principios SOLID, Clean Code, DRY y **Atomic Design**, garantizando una alta escalabilidad, mantenibilidad y un rendimiento óptimo.

---

## 🏗️ Arquitectura de Contenedores (Docker)

Todo el proyecto está contenerizado utilizando Docker Compose para garantizar que funcione idénticamente en cualquier entorno (Local y Producción).

El ecosistema se compone de los siguientes **5 contenedores**:

1. **`incident_nginx` (Nginx):** El proxy reverso principal y servidor web estático. Sirve la aplicación Frontend empaquetada (Vue) por el puerto `80` (expuesto al `8000` en tu máquina local) y redirige las peticiones `/api` al backend.
2. **`incident_frontend` (Vite/Vue):** Contenedor *Builder*. Se encarga de descargar las dependencias de Node, compilar los archivos `.js` y `.css` (incluyendo SASS), y pasárselos a Nginx.
3. **`incident_app` (PHP-FPM/Laravel):** El corazón del Backend. Procesa todas las reglas de negocio, conexión a base de datos y provee la API RESTful. Expone el puerto `9000` interno.
4. **`incident_reverb` (Laravel Reverb):** Servidor de WebSockets de Laravel para comunicación en tiempo real. Notifica al frontend instantáneamente cuando se crea, edita o borra un incidente. Expone el puerto `8080`.
5. **`incident_db` (MySQL 8):** Base de datos relacional persistida en el volumen `db-data`. Expone el puerto `3306`.

---

## 🛠️ Despliegue y Ejecución Local con Docker

A continuación, los pasos exactos para levantar todo el proyecto con un solo comando.

### Prerrequisitos
- Tener **Docker** y **Docker Compose** instalados en tu máquina.
- Git.

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/prueba-tecnica.git
cd prueba-tecnica
```

### 2. Configurar Variables de Entorno
Debes crear los archivos `.env` basándote en los ejemplos provistos.

**Para el Backend (`/backend/.env`):**
Copia el archivo de ejemplo:
```bash
cp backend/.env.example backend/.env
```
*Asegúrate de que las credenciales de DB coincidan con las del docker-compose.yml (`DB_HOST=db`, `DB_DATABASE=incident_db`, `DB_USERNAME=laravel`, `DB_PASSWORD=secret`).*

**Para el Frontend (`/frontend/.env`):**
Este archivo es crucial para que Vue se conecte con el Backend y WebSockets.
```env
VITE_API_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=tu_reverb_app_key_aqui
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### 3. Levantar los Contenedores
Ejecuta el siguiente comando en la raíz del proyecto para descargar las imágenes, construir el frontend y encender el servidor:

```bash
docker compose up -d --build
```

### 4. Preparar la Base de Datos (Migraciones y Semillas)
Una vez que todos los contenedores digan "Up" o "Started", necesitas crear las tablas en MySQL y llenarlas con datos de prueba:

```bash
# Ejecutar migraciones e insertar datos semilla
docker compose exec app php artisan migrate --seed
```

### 5. Acceder a la aplicación
Abre tu navegador web en: **`http://localhost:8000`**

---

## 🔐 Credenciales de Acceso (Seeders)

El comando `--seed` que ejecutaste arriba crea 10 usuarios en el sistema. Puedes usar cualquiera de los siguientes para iniciar sesión:

> **Contraseña global para TODOS los usuarios:** `P4ssw0rd`

| Rol | Correo Electrónico | Permisos |
| :--- | :--- | :--- |
| **Administrador** | `admin@incidentes.com` | Puede ver, editar y borrar TODOS los incidentes del sistema. |
| **Agente (Fijo)** | `agente@incidentes.com` | Solo puede ver y gestionar los incidentes que se le han asignado. |
| **Agentes (Random)** | *Generados aleatoriamente* | 8 agentes adicionales creados con datos falsos por Faker. |

*Para ver los correos de los agentes aleatorios puedes ejecutar:*
`docker compose exec app php artisan tinker --execute="echo User::where('role', 'agent')->pluck('email')->implode(PHP_EOL);"`

---

## 🚀 Despliegue en Producción (CI/CD)

El proyecto cuenta con integración continua configurada en `.github/workflows/deploy.yml`. 
Cada vez que haces un `git push` a la rama `main`, GitHub Actions se conecta automáticamente a tu servidor (Hetzner) mediante SSH y:
1. Descarga los últimos cambios.
2. Reconstruye de forma segura los contenedores Docker (`docker compose up -d --build`).
3. Actualiza las dependencias de Composer sin afectar la operatividad.
4. Aplica las migraciones de Base de Datos.

**Nota técnica de Nginx:** Nginx está configurado con un **DNS Resolver dinámico (127.0.0.11)** de Docker, lo que garantiza CERO errores de *502 Bad Gateway* cuando los contenedores internos cambian sus direcciones IP tras un despliegue.
