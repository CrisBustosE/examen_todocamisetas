# TodoCamisetas API

**Examen Transversal Final — Desarrollo Backend (IF201IINF)**
Instituto Profesional San Sebastián

| Campo | Detalle |
|---|---|
| Nombre del Grupo | CristobalBustos |
| Integrante | Cristóbal Bustos |
| Asignatura | Desarrollo Backend — IF201IINF |
| Evaluación | Examen Transversal Final |

---
## Link al Repositorio (GitHub)
>https://github.com/CrisBustosE/examen_todocamisetas
---

## Descripción del Proyecto

API RESTful construida con Laravel 11 para la empresa **TodoCamisetas**, proveedor mayorista de camisetas de fútbol con sede en Santiago, Chile. La API actúa como columna vertebral del sistema de gestión de inventario y relación con clientes B2B.

El sistema permite gestionar el catálogo de camisetas, la cartera de clientes y un motor de cálculo dinámico de precios que aplica reglas de negocio diferenciadas según la categoría del cliente (Regular o Preferencial).

---

## Stack Tecnológico

- PHP 8.2
- Laravel 11
- MySQL (producción)
- darkaonline/l5-swagger 11 — documentación OpenAPI 3.0
- Eloquent ORM con SoftDeletes
- Transacciones de base de datos (DB::beginTransaction / commit / rollBack)

---

## Arquitectura del Proyecto

El proyecto sigue la arquitectura MVC estándar de Laravel, separando responsabilidades de forma clara entre rutas, controladores, modelos y configuración.

```
todocamisetas-api/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Controller.php          # Clase base abstracta con anotaciones OpenAPI globales
│   │       ├── HealthController.php    # Endpoint de observabilidad (__invoke)
│   │       ├── ClientController.php    # CRUD completo de clientes B2B
│   │       ├── ShirtController.php     # CRUD de camisetas + lógica de precio final
│   │       ├── SizeController.php      # CRUD del catálogo de tallas
│   │       └── Schemas/
│   │           ├── ClientSchema.php    # Esquemas OpenAPI para clientes
│   │           ├── ShirtSchema.php     # Esquemas OpenAPI para camisetas
│   │           └── SizeSchema.php      # Esquemas OpenAPI para tallas
│   │
│   └── Models/
│       ├── Client.php                  # Modelo Eloquent con SoftDeletes y relación hasMany Shirts
│       ├── Shirt.php                   # Modelo con SoftDeletes, relaciones y método findWithFinalPrice()
│       └── Size.php                    # Modelo con SoftDeletes y relación belongsToMany Shirts
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_clients_table.php      # Tabla de clientes B2B
│   │   ├── ..._create_shirts_table.php       # Tabla de camisetas con FK a clients
│   │   ├── ..._create_sizes_table.php        # Catálogo de tallas
│   │   └── ..._create_shirt_size_table.php   # Tabla pivote many-to-many con FK y cascada
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── SizeSeeder.php                    # Siembra tallas estándar (XS, S, M, L, XL, XXL, Única)
│       ├── ClientSeeder.php                  # Siembra 2 clientes principales ("Tienda 90minutos" (Preferencial, ID 1) y "Tienda tdeportes" (Regular, ID 2)).
│       └── ShirtSeeder.php                   # Siembra 3 Camisetas (pre configuradas con y sin ofertas para probar la regla de negocio)
│
├── routes/
│   └── api.php                         # Todas las rutas RESTful versionadas bajo /api/v1
│
├── config/
│   └── l5-swagger.php                  # Configuración de Swagger UI
│
├── storage/
│   └── api-docs/
│       └── api-docs.json               # Especificación OpenAPI generada
│
└── .env.example                        # Plantilla de variables de entorno
```

### Rol de cada componente

**Controladores:** Reciben la solicitud HTTP, validan los datos de entrada con mensajes de error personalizados en español, orquestan la operación sobre el modelo y devuelven siempre una respuesta JSON con el header `Content-Type: application/json`. Toda operación de escritura está envuelta en una transacción (`beginTransaction / commit / rollBack`).

**Modelos:** Encapsulan la lógica de acceso a datos. Definen los campos `$fillable`, las relaciones Eloquent (`belongsTo`, `hasMany`, `belongsToMany`) y el trait `SoftDeletes`. El modelo `Shirt` contiene el método estático `findWithFinalPrice()` que ejecuta un JOIN manual con la tabla pivote para calcular el precio final según las reglas de negocio.

**Rutas:** Definidas en `routes/api.php` bajo el prefijo `/api/v1`. Se utiliza `Route::apiResource()` para generar automáticamente las cinco rutas RESTful estándar (index, store, show, update, destroy) para cada recurso.

**Schemas (Schemas/):** Clases PHP vacías cuyo único propósito es alojar las anotaciones de OpenAPI que definen los modelos de request y response para Swagger. Mantienen los controladores más limpios.

**Migraciones:** Definen la estructura de la base de datos de forma versionada. La tabla `shirt_size` (pivote) usa claves foráneas con `cascadeOnDelete()` y clave primaria compuesta para garantizar integridad referencial.

---

## Modelo de Datos

```
clients
├── id (PK)
├── nombre_comercial (string)
├── rut (string, unique)
├── direccion (string)
├── categoria (enum: 'Regular', 'Preferencial')
├── contacto_nombre (string)
├── contacto_correo (string)
├── porcentaje_oferta (integer, nullable)
├── created_at / updated_at
└── deleted_at (SoftDelete)

shirts
├── id (PK)
├── cliente_id (FK -> clients.id, RESTRICT ON DELETE)
├── titulo (string)
├── club (string)
├── pais (string)
├── tipo (string)
├── color (string)
├── precio (integer, en pesos CLP)
├── precio_oferta (integer, nullable)
├── detalles (text, nullable)
├── codigo_producto (string, unique)
├── created_at / updated_at
└── deleted_at (SoftDelete)

sizes
├── id (PK)
├── nombre (string)
├── created_at / updated_at
└── deleted_at (SoftDelete)

shirt_size  [Tabla pivote — relación muchos a muchos]
├── shirt_id (FK -> shirts.id, CASCADE ON DELETE)
└── size_id  (FK -> sizes.id,  CASCADE ON DELETE)
    PK: (shirt_id, size_id)
```

**Relaciones:**
- Un `Client` tiene muchas `Shirts` (1:N). No se puede eliminar un cliente que tenga camisetas asociadas (validado a nivel de controlador y a nivel de base de datos con `RESTRICT`).
- Una `Shirt` pertenece a un `Client` (N:1).
- Una `Shirt` tiene muchas `Sizes` y una `Size` pertenece a muchas `Shirts` (N:M), gestionada a través de `shirt_size`.

---

## Instalación y Ejecución

### Requisitos previos

- PHP 8.2 o superior
- Composer
- MySQL 8 (o SQLite para desarrollo rápido)

### Pasos de instalación

**1. Clonar el proyecto**

```bash
# 1. Clonar el repositorio
git clone https://github.com/CrisBustosE/examen_todocamisetas
cd examen_todocamisetas/backend

# 2. Levantar la infraestructura
docker compose up -d --build

# 3. Instalar dependencias
docker compose exec app composer install

# 4. Copiar el archivo de entorno (Trae las credenciales y DB listas para levantar el proyecto)
cp .env.example .env

# 5. Generar clave de aplicación
docker compose exec app php artisan key:generate

# 6. Otorgar permisos (Usuarios Linux/WSL):
docker compose exec app chmod -R 777 storage bootstrap/cache

# 7. Ejecutar migraciones y sembrar datos iniciales (:fresh para quitar residuos o proyectos previos)
docker compose exec app php artisan migrate:fresh --seed
```
* **El seeder crea automáticamente las tallas estándar, los 2 clientes principales y 3 camisetas pre configuradas.**
```bash
# 8. Generar documentación Swagger
docker compose exec app php artisan l5-swagger:generate

```



La API queda disponible en: `http://localhost:8080/api/v1`  
La documentación Swagger UI en: `http://localhost:8080/api/documentation`

Desde allí se puede explorar y probar cada endpoint directamente en el navegador.


---

## Endpoints de la API

Todos los endpoints están prefijados con `/api/v1` y devuelven `Content-Type: application/json`.

### Health Check

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/v1/health` | Verifica que la API está operativa |

Respuesta de ejemplo:
```json
{
  "status": "online",
  "service": "TodoCamisetas API",
  "version": "1.0.0",
  "timestamp": "2026-06-09T12:00:00+00:00"
}
```

---

### Clientes (`/api/v1/clients`)

| Método | Ruta | Acción | Descripcion |
|---|---|---|---|
| GET | `/api/v1/clients` | index | Listar todos los clientes |
| POST | `/api/v1/clients` | store | Crear un nuevo cliente |
| GET | `/api/v1/clients/{id}` | show | Obtener un cliente por ID |
| PUT/PATCH | `/api/v1/clients/{id}` | update | Actualizar un cliente |
| DELETE | `/api/v1/clients/{id}` | destroy | Eliminar un cliente (SoftDelete) |

**Campos del body (POST / PUT):**

```json
{
  "nombre_comercial": "90minutos",
  "rut": "76111222-3",
  "direccion": "Providencia, Santiago",
  "categoria": "Preferencial",
  "contacto_nombre": "Pedro Soto",
  "contacto_correo": "pedro@90minutos.cl",
  "porcentaje_oferta": 10
}
```

**Validaciones aplicadas:**
- `categoria` acepta únicamente `"Regular"` o `"Preferencial"`.
- `rut` debe ser único. Si el RUT ya existe con SoftDelete, el registro se restaura y actualiza en lugar de duplicarse.
- `porcentaje_oferta` debe estar entre 0 y 100.
- No se puede eliminar un cliente que tenga camisetas asociadas — retorna `409 Conflict`.

**Códigos HTTP posibles:**

| Código | Situación |
|---|---|
| 200 | Operación exitosa |
| 201 | Cliente creado |
| 404 | Cliente no encontrado |
| 409 | Cliente tiene camisetas asociadas, no se puede eliminar |
| 422 | Error de validación |
| 500 | Error de servidor |

---

### Camisetas (`/api/v1/shirts`)

| Método | Ruta | Acción | Descripcion |
|---|---|---|---|
| GET | `/api/v1/shirts` | index | Listar todas las camisetas (incluye cliente y tallas) |
| POST | `/api/v1/shirts` | store | Crear una nueva camiseta |
| GET | `/api/v1/shirts/{id}?client_id={id}` | show | Obtener camiseta con precio final calculado |
| PUT/PATCH | `/api/v1/shirts/{id}` | update | Actualizar una camiseta |
| DELETE | `/api/v1/shirts/{id}` | destroy | Eliminar una camiseta (SoftDelete) |
| GET | `/api/v1/clients/{id}/shirts` | byClient | Listar camisetas de un cliente específico |

**Campos del body (POST / PUT):**

```json
{
  "cliente_id": 1,
  "titulo": "Camiseta Local 2025 - Selección Chilena",
  "club": "Selección Chilena",
  "pais": "Chile",
  "tipo": "Local",
  "color": "Rojo y Azul",
  "precio": 45000,
  "precio_oferta": 38000,
  "detalles": "Edición aniversario 2025",
  "codigo_producto": "SCL2025L",
  "sizes_ids": [1, 2, 3, 4]
}
```

El campo `sizes_ids` recibe un array de IDs de tallas existentes. La relación se gestiona automáticamente sobre la tabla pivote `shirt_size` usando `sync()`.

**Código de producto único:** Si se intenta crear una camiseta con un `codigo_producto` de un registro previamente eliminado con SoftDelete, el sistema lo restaura y actualiza en vez de generar un error de duplicado.

**Códigos HTTP posibles:**

| Código | Situación |
|---|---|
| 200 | Operación exitosa |
| 201 | Camiseta creada |
| 400 | Falta el parámetro `client_id` en la consulta de precio |
| 404 | Camiseta o cliente no encontrado |
| 422 | Error de validación |
| 500 | Error de servidor |

---

### Tallas (`/api/v1/sizes`)

| Método | Ruta | Acción | Descripcion |
|---|---|---|---|
| GET | `/api/v1/sizes` | index | Listar todas las tallas |
| POST | `/api/v1/sizes` | store | Crear una nueva talla |
| GET | `/api/v1/sizes/{id}` | show | Obtener una talla por ID |
| PUT/PATCH | `/api/v1/sizes/{id}` | update | Actualizar una talla |
| DELETE | `/api/v1/sizes/{id}` | destroy | Eliminar una talla (SoftDelete) |

**Campos del body (POST / PUT):**

```json
{
  "nombre": "XL"
}
```

**Validaciones:**
- `nombre` debe ser único. Si ya existe como SoftDeleted, se restaura.
- No se puede eliminar una talla que esté asociada a alguna camiseta — retorna `409 Conflict`.

**Códigos HTTP posibles:**

| Código | Situación |
|---|---|
| 200 | Operación exitosa |
| 201 | Talla creada |
| 404 | Talla no encontrada |
| 409 | Talla en uso por una o más camisetas |
| 422 | Error de validación |
| 500 | Error de servidor |

---

## Reglas de Negocio — Precio Final (Tarea 6)

El endpoint `GET /api/v1/shirts/{id}?client_id={client_id}` implementa el cálculo dinámico de precio final según la categoría del cliente consultante.

**Lógica implementada:**

```
Si cliente.categoria == "Preferencial" Y shirt.precio_oferta no es nulo:
    precio_final = precio_oferta

En cualquier otro caso (categoria == "Regular" o precio_oferta es nulo):
    precio_final = precio (precio base)
```

Además del precio, el endpoint ejecuta un JOIN manual entre `shirts`, `shirt_size` y `sizes` usando `GROUP_CONCAT` para devolver las tallas disponibles como un campo adicional, sin necesidad de múltiples consultas.

**Ejemplo de respuesta:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "titulo": "Camiseta Local 2025 - Selección Chilena",
    "club": "Selección Chilena",
    "pais": "Chile",
    "tipo": "Local",
    "color": "Rojo y Azul",
    "detalles": "Edición aniversario 2025",
    "codigo_producto": "SCL2025L",
    "tallas_disponibles": "S,M,L,XL",
    "precio_final": 38000,
    "cliente_consultor": {
      "id": 1,
      "categoria": "Preferencial"
    }
  }
}
```

---

## Formato de Respuesta Estándar

Todos los endpoints siguen el mismo formato de respuesta JSON:

**Éxito:**
```json
{
  "success": true,
  "data": { },
  "message": "Descripción de la operación"
}
```

**Error:**
```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": { }
}
```

---

## Decisiones Técnicas Relevantes

**Por qué Laravel en lugar de PHP puro:** El profesor autorizó el uso de Laravel para este proyecto, en línea con el stack trabajado durante el trimestre. Laravel provee un ORM maduro (Eloquent), un sistema de validación robusto y herramientas de migración que permiten enfocarse en la lógica de negocio sin reinventar infraestructura.

**Por qué PDO a través de Eloquent:** Eloquent usa PDO internamente con consultas preparadas en todos los métodos estándar. Para el JOIN manual del precio final (`findWithFinalPrice`), se utilizó el Query Builder de Laravel, que también opera sobre PDO con binding de parámetros, eliminando el riesgo de inyección SQL.

**SoftDeletes en las tres entidades:** Permite recuperar registros eliminados accidentalmente y evita romper registros históricos relacionados. Si un `codigo_producto` o `rut` ya existió y fue eliminado, el sistema lo restaura en lugar de rechazar la creación con un error de unicidad.

**Transacciones en todos los métodos de escritura:** `store`, `update` y `destroy` en los tres controladores envuelven sus operaciones en `DB::beginTransaction()` con `commit()` en caso de éxito y `rollBack()` en caso de excepción. Esto garantiza consistencia en operaciones compuestas como crear una camiseta y asignar sus tallas simultáneamente.

**Tabla pivote sin timestamps:** `shirt_size` no tiene columnas `created_at` / `updated_at` porque la relación muchos a muchos no requiere auditoría temporal propia. El `cascadeOnDelete()` en ambas FK asegura que al eliminar una camiseta, sus registros en la pivote se limpian automáticamente.

**Validación de integridad en capa de aplicación:** El controlador verifica explícitamente si un cliente tiene camisetas antes de eliminarlo (retornando `409`), aunque la FK `RESTRICT` de la base de datos también lo bloquearía. Se optó por el control en la capa de aplicación para devolver un mensaje de error legible en JSON en lugar de una excepción de base de datos.

---

## Colección Postman

Se incluye el archivo `EXAMEN_BUSTOS.postman_collection.json` en la raíz del proyecto. Para importarla:

1. Abrir Postman.
2. Ir a **File > Import**.
3. Seleccionar el archivo `EXAMEN_BUSTOS.postman_collection.json`.
4. La colección incluye ejemplos de request y response para todos los endpoints, incluyendo casos de error.

La variable `{{base_url}}` de la colección debe apuntar a `http://localhost:8080/api/v1`.

*Desarrollado por Cristóbal Bustos — Instituto Profesional San Sebastián, 2026.*
