# ALQOMICHI — API REST MVC (PHP + SQL Server)

## Estructura del proyecto
`app/Controllers`, `app/Middleware`, `app/Models`, `app/Services`, `app/Views`, `config`, `core`, `public`, `routes`, `vendor` (generado por Composer), `composer.json`, `composer.lock` (generado tras instalar).

## Requisitos
- Windows con XAMPP (Apache + PHP 8.1+)
- Composer
- SQL Server y SQL Server Management Studio (SSMS)
- Microsoft ODBC Driver 18 y extensión PHP `pdo_sqlsrv` compatible con la versión, arquitectura y Thread Safety de PHP
- Postman para pruebas de endpoints

## Instalación
1. Copia esta carpeta a `C:\xampp\htdocs\ALQOMICHI_API_MVC`.
2. Abre una terminal en la carpeta del proyecto y ejecuta `composer install` para generar `vendor/` y `composer.lock` si no existen. Verifica la ruta de PHP con `where.exe php` si es necesario.
3. Copia `.env.example` como `.env` y configura servidor, usuario, contraseña y base de datos. Nunca subas `.env` al repositorio.
4. En SSMS, verifica `SELECT name FROM sys.databases WHERE name = 'AlqomichiDB'`. Revisa el script SQL antes de ejecutarlo (por ejemplo, si contiene `DROP DATABASE` o reinicia datos) y haz respaldo si corresponde.
5. Comprueba el driver con `php -m | findstr /i "pdo_sqlsrv"`. Si no aparece, instala el ODBC/DLL correspondiente, habilítalo en `php.ini` y reinicia Apache.
6. En XAMPP, inicia **Apache** y verifica que el servicio de SQL Server esté activo.
7. Verifica los endpoints base (como `/api/salud` o `/api/productos`) y comprueba la conexión a la base de datos.

## Recursos CRUD (6 tablas)
Cada recurso admite `GET /api/{recurso}`, `GET /api/{recurso}/{id}`, `POST /api/{recurso}`, `PUT /api/{recurso}/{id}`, `PATCH /api/{recurso}/{id}` y `DELETE /api/{recurso}/{id}`:

- `/api/categorias`
- `/api/marcas`
- `/api/productos`
- `/api/clientes`
- `/api/pedidos`
- `/api/detalle-pedidos`

En peticiones `POST`, `PUT` o `PATCH`, envía el cuerpo en formato `raw` (`JSON`) y el encabezado `Content-Type: application/json`.

## Códigos HTTP esperados
- `200`: Consulta, actualización o eliminación correcta.
- `201`: Recurso creado correctamente.
- `400` / `422`: Parámetros inválidos o errores de validación/reglas de negocio.
- `404`: Recurso o referencia inexistente.
- `409`: Conflicto por restricción única o clave foránea.
- `500`: Error interno del servidor.

## Pruebas y entrega
- Pruebas CRUD realizadas en Postman sobre las seis tablas de `AlqomichiDB`, verificando respuestas y cambios en la base de datos desde SSMS.
- Se adjunta el script SQL con estructura y datos de prueba respetando claves primarias, foráneas y restricciones (`CHECK` / `UNIQUE`).
- No se incluyen `vendor/`, `.env` ni credenciales sensibles.
