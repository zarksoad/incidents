# Incident Management System (Technical Test)

Este repositorio contiene la prueba técnica para un **Sistema de Gestión de Incidentes**, desarrollado utilizando estándares Senior de la industria con **Laravel 11** en el Backend y **Vue 3** en el Frontend. 

El código respeta fielmente los principios SOLID, Clean Code, DRY y **Atomic Design**, garantizando una alta escalabilidad, mantenibilidad y un rendimiento óptimo.

---

## Arquitectura de Contenedores (Docker)

Todo el proyecto está contenerizado utilizando Docker Compose para garantizar que funcione idénticamente en cualquier entorno (Local y Producción).

El ecosistema se compone de los siguientes **5 contenedores**:

1. **`incident_nginx` (Nginx):** El proxy reverso principal y servidor web estático. Sirve la aplicación Frontend empaquetada (Vue) por el puerto `80` (expuesto al `8000` en tu máquina local) y redirige las peticiones `/api` al backend.
2. **`incident_frontend` (Vite/Vue):** Contenedor *Builder*. Se encarga de descargar las dependencias de Node, compilar los archivos `.js` y `.css` (incluyendo SASS), y pasárselos a Nginx.
3. **`incident_app` (PHP-FPM/Laravel):** El corazón del Backend. Procesa todas las reglas de negocio, conexión a base de datos y provee la API RESTful. Expone el puerto `9000` interno.
4. **`incident_reverb` (Laravel Reverb):** Servidor de WebSockets de Laravel para comunicación en tiempo real. Notifica al frontend instantáneamente cuando se crea, edita o borra un incidente. Expone el puerto `8080`.
5. **`incident_db` (MySQL 8):** Base de datos relacional persistida en el volumen `db-data`. Expone el puerto `3306`.

---

## Despliegue y Ejecución Local con Docker

A continuación, los pasos exactos para levantar todo el proyecto con un solo comando.

### Prerrequisitos
- Tener **Docker** y **Docker Compose** instalados en tu máquina.
- Git.

### 1. Clonar el repositorio
```bash
git clone https://github.com/zarksoad/incidents.git
cd incidents
```

### 2. Configurar Variables de Entorno
Debes crear los archivos `.env` basándote en los ejemplos provistos.

**Para el Backend (`/backend/.env`):**
Copia el archivo de ejemplo:
```bash
cp backend/.env.example backend/.env
```
*Asegúrate de que las credenciales de DB coincidan con las del docker-compose.yml (`DB_HOST=db`, `DB_DATABASE=incident_db`, `DB_USERNAME=laravel`, `DB_PASSWORD=secret`).*

**Variables Globales de Frontend (En la raíz del proyecto `.env`):**
Docker Compose pasará automáticamente estas variables al contenedor del frontend al construirlo. Puedes añadirlas a un archivo `.env` en la raíz de tu proyecto:
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

## Credenciales de Acceso (Seeders)

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

## Despliegue en Producción (CI/CD)

El proyecto cuenta con integración continua configurada en `.github/workflows/deploy.yml`. 
Cada vez que haces un `git push` a la rama `main`, GitHub Actions se conecta automáticamente a tu servidor (Hetzner) mediante SSH y:
1. Descarga los últimos cambios.
2. Reconstruye de forma segura los contenedores Docker (`docker compose up -d --build`).
3. Actualiza las dependencias de Composer sin afectar la operatividad.
4. Aplica las migraciones de Base de Datos.

**Nota técnica de Nginx:** Nginx está configurado con un **DNS Resolver dinámico (127.0.0.11)** de Docker, lo que garantiza CERO errores de *502 Bad Gateway* cuando los contenedores internos cambian sus direcciones IP tras un despliegue.

---

## Endpoints de la API (RESTful)

A continuación, se listan las rutas principales de la API expuestas por el backend para ser consumidas por el frontend o para pruebas (vía Postman/Insomnia). Todas las rutas (excepto el login) requieren el token de autenticación (Sanctum).

| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| **POST** | `/api/login` | Iniciar sesión y obtener token de acceso. |
| **POST** | `/api/logout` | Cerrar sesión e invalidar el token actual. |
| **GET** | `/api/user` | Obtener los datos del usuario autenticado actualmente. |
| **GET** | `/api/users` | Listar usuarios (útil para asignar agentes a incidentes). |
| **GET** | `/api/dashboard` | Obtener estadísticas generales para el panel de control. |
| **GET** | `/api/incidents` | Listar incidentes (soporta paginación, filtros y búsqueda). |
| **POST** | `/api/incidents` | Crear un nuevo incidente. |
| **GET** | `/api/incidents/export` | Exportar la lista de incidentes (ej. CSV/Excel). |
| **GET** | `/api/incidents/{id}` | Ver el detalle de un incidente específico. |
| **PUT/PATCH** | `/api/incidents/{id}` | Actualizar un incidente existente. |
| **DELETE** | `/api/incidents/{id}` | Eliminar un incidente del sistema. |

---

## Supuestos y Justificaciones Técnicas: Roles y Permisos

El documento de la prueba técnica solicitaba "Autenticación de usuarios" y un "CRUD de Incidentes" contemplando campos como "usuario creador" y "usuario asignado", pero no especificaba explícitamente las reglas de negocio sobre qué podía hacer cada usuario. 

Basado en la sugerencia del documento de *asumir decisiones razonables*, se implementó un **Sistema de Roles (`admin` y `agent`)** bajo las siguientes justificaciones de negocio para un entorno real de mesa de ayuda (Help Desk):

1. **Rol Administrador (`admin`):**
   - **Alcance:** Tiene una vista global. Puede ver, listar, editar y exportar **absolutamente todos** los incidentes del sistema, independientemente de a quién estén asignados. 
   - **Dashboard:** Ve las métricas globales de toda la operación (totales, vencidos, por estado).
   - **Eliminación:** Es el *único* rol con permisos para **Eliminar (`DELETE`)** incidentes, protegiendo así el sistema contra borrados accidentales o malintencionados por parte de agentes regulares.

2. **Rol Agente (`agent`):**
   - **Alcance:** Tiene una vista restringida. Solo puede ver y gestionar los incidentes donde él sea el **"usuario asignado"**. 
   - **Dashboard:** No tiene acceso al panel de métricas ni estadísticas.
   - **Justificación:** En sistemas de Help Desk reales, el enfoque del agente (o técnico) debe estar 100% en la resolución de su cola de trabajo asignada. Las métricas y el dashboard se reservan para roles administrativos/gerenciales para medir KPIs. Por confidencialidad y orden operativo, el agente solo interactúa con los incidentes bajo su responsabilidad y no con las analíticas globales.

Esta arquitectura de permisos demuestra un enfoque propositivo y orientado al producto, asegurando que el sistema no solo funcione a nivel técnico, sino que tenga coherencia comercial.

---

## Arquitectura y Patrones de Diseño

El proyecto ha sido estructurado siguiendo estándares de calidad de software (Clean Code y principios SOLID), separando responsabilidades para garantizar la escalabilidad y mantenibilidad.

### Backend (Laravel)
- **Service Pattern (Capa de Servicios):** Se extrajo la lógica de negocio pesada de los Controladores (ej. `IncidentController`) y se movió a clases de servicio especializadas (`IncidentService`, `DashboardService`). Esto evita los "Fat Controllers" y promueve la reutilización.
- **Resource Pattern (API Resources):** Se utilizan clases como `IncidentResource` para encapsular y transformar los modelos de Eloquent en respuestas JSON puras, protegiendo la estructura de la base de datos de la interfaz pública.
- **Event-Driven Architecture (Eventos):** Cuando un incidente se crea, actualiza o elimina, se emiten eventos (`IncidentSaved`, `IncidentDeleted`). Esto desacopla procesos paralelos y gatilla automáticamente los WebSockets de **Laravel Reverb** para actualizar el frontend en tiempo real.
- **Form Requests Validation:** Validación centralizada y estricta de los datos entrantes a través de clases `Request` (`StoreIncidentRequest`, `UpdateIncidentRequest`), manteniendo los controladores delgados.
- **Policy/Gate Pattern:** Centralización de la lógica de autorización (`IncidentPolicy`) para determinar de forma segura qué acciones están permitidas por rol.

### Frontend (Vue 3)
- **Composition API (Composables):** Uso moderno de `<script setup>` y abstracción de lógica reactiva en *composables* independientes para lograr un código más limpio en lugar del antiguo Options API.
- **State Management (Pinia):** Gestión del estado global (como el usuario autenticado) aislado de los componentes.
- **API Service & Interceptors:** Centralización de las llamadas al backend mediante un cliente HTTP (Axios) configurado con *interceptors*. Esto inyecta automáticamente el Token JWT/Sanctum en cada petición y captura respuestas 401 para redireccionar al login de forma global.
- **Component-Based / Atomic Design:** División modular de las interfaces. Separación clara entre Layouts (`AppLayout.vue`), Vistas/Páginas (`views/`) y Componentes UI reutilizables (`components/`).

---

## Guía de Pruebas para el Evaluador

Para facilitar la revisión de los puntos "Ideales" solicitados en la prueba técnica, te sugiero las siguientes dinámicas:

### 1. Pruebas Unitarias / Feature (PHPUnit)
El proyecto incluye tests automatizados para garantizar la integridad de los endpoints y las políticas de acceso. Para ejecutarlos dentro del contenedor, corre el siguiente comando en tu terminal:
```bash
docker compose exec app php artisan test
```

### 2. Actualización en Tiempo Real (WebSockets / Reverb)
Para comprobar que el frontend reacciona en vivo a los eventos del backend:
1. Abre dos navegadores (ej. Chrome normal y Chrome Incógnito).
2. Inicia sesión con el **Administrador** en uno, y con el **Agente** en el otro.
3. Crea, edita o elimina un incidente desde la cuenta del Administrador.
4. Observa cómo la tabla de incidentes o el Dashboard en la pantalla del Agente se actualiza instantáneamente sin necesidad de refrescar la página.

### 3. Logs de Auditoría
Cada vez que un incidente sufre una modificación (creación o edición), se registra un **Audit Log** en la base de datos indicando el usuario responsable, la acción y el payload de los cambios. Esta traza garantiza la transparencia de las operaciones, un requisito crucial en mesas de ayuda corporativas.

### 4. Exportación a CSV
Puedes probar la generación de reportes utilizando el endpoint `/api/incidents/export` (o el botón correspondiente en el frontend). El backend utiliza un `StreamedResponse`, lo que significa que puede descargar miles de registros sin agotar la memoria RAM del servidor.
