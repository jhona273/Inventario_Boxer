# Inventario Boxer Matheus

Sistema de inventario web (PHP + MySQL) para llevar el control de
existencias por código, descripción y talla, separado entre estantería
y bodega, con historial de movimientos.

## Requisitos

- XAMPP (Apache + MySQL + PHP) — https://www.apachefriends.org/
- PHP 8+

## Instalación (primera vez)

1. Clona este repositorio dentro de tu carpeta `htdocs`:
   ```
   git clone <URL-de-tu-repo> inventario_boxers
   ```
2. Abre phpMyAdmin (`http://localhost/phpmyadmin`), crea una base de datos
   llamada `inventario_boxers`, y corre en orden (pestaña SQL):
   - `schema_v2.sql`
   - `migracion_v3.sql`
   - `datos_iniciales.sql` (opcional, solo si quieres las 18 referencias de ejemplo)
3. Copia `config.example.php` y renómbralo a `config.php`. Ajusta usuario
   y clave si no son las de XAMPP por defecto.
4. Abre `http://localhost/inventario_boxers/index.php`

## Estructura

- `index.php` — vista principal del inventario
- `movimiento.php` — procesa entradas/salidas de estantería o bodega
- `config.php` — conexión a la base de datos (NO se sube a git, ver `.gitignore`)
- `config.example.php` — plantilla de configuración (sí se sube a git)
- `estilo.css` — estilos de la página
- `*.sql` — scripts de base de datos, en el orden que se deben correr
