# 🚀 Incident Management System (Technical Test)

Este repositorio contiene la prueba técnica para un **Sistema de Gestión de Incidentes**, desarrollado utilizando estándares Senior de la industria con **Laravel 11** en el Backend y **Vue 3** en el Frontend. 

El código respeta fielmente los principios SOLID, Clean Code y DRY, garantizando una alta escalabilidad, mantenibilidad y un rendimiento óptimo.

---

## 🏗️ Diagrama de Arquitectura (Backend & Frontend)

El siguiente diagrama ilustra cómo interactúa el Frontend (Vue) con el Backend (Laravel), y cómo los componentes internos están separados según sus responsabilidades (Service Pattern y Composables).

```mermaid
flowchart TD
    subgraph Frontend [📱 Frontend: Vue 3 (Vite)]
        UI[Vistas Vue\nIncidentList, Dashboard]
        Composables[Composables\nuseIncidents, useDashboard]
        Pinia[Pinia Store\nNotifications]
        Axios[API Client\nAxios + Interceptors]
        Echo[Laravel Echo\nWebSockets]
        
        UI -->|Usa| Composables
        UI -->|Dispara| Pinia
        Composables -->|Peticiones HTTP| Axios
        Composables -->|Escucha Eventos| Echo
    end

    subgraph Backend [⚙️ Backend: Laravel 11]
        Routes[API Routes\nSanctum Auth]
        Controllers[Skinny Controllers\nIncidentController]
        Services[Services Layer\nIncidentService]
        Eloquent[Eloquent ORM\nModelos y Relaciones]
        Reverb[Laravel Reverb\nWebSocket Server]
        
        Axios -->|Peticiones REST| Routes
        Routes --> Controllers
        Controllers -->|Delega lógica| Services
        Services -->|Consultas| Eloquent
        Services -->|Emite Eventos| Reverb
        Reverb -->|Broadcasting| Echo
    end

    subgraph Database [🗄️ Base de Datos]
        MySQL[(MySQL / SQLite)]
        Eloquent --> MySQL
    end
```

---

## 💻 Detalles del Frontend (Vue 3)

El frontend está construido como una *Single Page Application* (SPA) optimizada, separando la lógica de negocio de la interfaz de usuario:

- **Framework:** Vue 3 (Composition API) + Vite (para builds ultra-rápidos).
- **UI Library:** Vuetify 3 (para componentes estéticos y responsive).
- **State Management:** Pinia (para el manejo del estado global, como notificaciones).
- **Enrutamiento:** Vue Router (con soporte para vistas como Formulario, Detalle y Dashboard).
- **WebSockets:** Laravel Echo + Pusher JS, escuchando en tiempo real las actualizaciones del servidor Reverb.
- **Arquitectura Interna:** Uso intensivo de **Composables** (`useIncidents.js`, `useDashboard.js`). Esto evita componentes "gordos", permitiendo testear la lógica de manera aislada y reutilizar código.

---

## ⚙️ Detalles del Backend (Laravel 11)

El backend provee una API RESTful completamente autenticada y orientada a servicios:

- **Framework:** Laravel 11 (con PHP 8.3).
- **Autenticación:** Laravel Sanctum (Token-based Auth).
- **Arquitectura:** **Service Layer Pattern**. Los controladores son extremadamente "flacos" (Skinny Controllers). Toda la lógica compleja (filtros, creación, agregaciones) vive en clases `Service` dedicadas (`IncidentService`, `DashboardService`).
- **Tiempo Real:** Integración con Laravel Reverb para broadcasting nativo de WebSockets, avisando al Frontend en tiempo real cuando un incidente es creado, actualizado o eliminado.
- **Rendimiento:** Prevención de consultas N+1 mediante el uso de Eager Loading (`->with()`) desde la capa de servicios.

---

## 🛠️ Despliegue y Ejecución Local

A continuación, los pasos para ejecutar todo el ecosistema (Frontend, Backend y WebSockets) de manera local.

### Prerrequisitos
- PHP 8.3+ y Composer
- Node.js (v18+) y npm/yarn
- Base de datos local (o utilizar SQLite por defecto configurado en Laravel)

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/prueba-tecnica.git
cd prueba-tecnica
```

### 2. Configurar el Backend (Laravel)
Abre una terminal en la carpeta `/backend` y ejecuta:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Si usas SQLite (opción más rápida para desarrollo), crea el archivo:
```bash
touch database/database.sqlite
```

Luego ejecuta las migraciones (opcionalmente con `--seed` si tienes seeders configurados):
```bash
php artisan migrate --seed
```

### 3. Iniciar el Servidor Laravel y Reverb (WebSockets)
Puedes usar comandos separados o levantar todo junto si configuraste supervisor o comandos paralelos. En consolas separadas:

```bash
# Terminal 1 - Servidor HTTP
php artisan serve

# Terminal 2 - Servidor WebSockets (Reverb)
php artisan reverb:start
```

### 4. Configurar el Frontend (Vue 3)
Abre otra terminal en la carpeta `/frontend` y ejecuta:

```bash
cd frontend
npm install
```

Asegúrate de tener un archivo `.env` en el frontend con la URL de tu API y configuración de Reverb (Echo):
```env
VITE_API_URL=http://localhost:8000/api
VITE_REVERB_APP_KEY=tu_app_key_del_backend
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Inicia el servidor de desarrollo:
```bash
npm run dev
```

### 5. Acceder a la aplicación
Abre tu navegador en `http://localhost:5173` (o el puerto que te indique Vite) y ¡listo! Ya puedes acceder a la aplicación, interactuar con los incidentes y ver los cambios en tiempo real gracias a WebSockets.

> [!TIP]
> **Credenciales de Acceso por defecto (generadas por los Seeders):**
> 
> **Administrador (Todos los permisos):**
> - **Email:** `admin@incidentes.com`
> - **Contraseña:** `P4ssw0rd`
> 
> **Agente Normal (Solo ve sus incidentes):**
> - **Email:** `agente@incidentes.com`
> - **Contraseña:** `P4ssw0rd`
> 
> *(Se generan también otros agentes de forma aleatoria con la misma contraseña).*

---

## 🧪 Ejecución de Pruebas (Testing)

El proyecto viene con una suite de pruebas para el Backend. Para asegurarte de que todo funcione correctamente y de que las refactorizaciones cumplan con los estándares esperados, puedes ejecutar los tests.

Abre una terminal en la carpeta `/backend` y ejecuta:

```bash
php artisan test
```

Esto correrá todos los *Feature Tests* y *Unit Tests* asegurando que la API de incidentes y las lógicas del dashboard están funcionando al 100%.
