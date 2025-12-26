# Guía de Instalación - Sistema de Gestión de Impresoras

## Requisitos
- XAMPP (PHP 8.0+, MySQL/MariaDB)
- Navegador web moderno

## Pasos de Instalación

### 1. Copiar Archivos
Descomprime el archivo en:
```
C:\xampp\htdocs\impresoras
```

### 2. Importar Base de Datos

1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Crea una nueva base de datos llamada: `sistema_impresoras`
3. Selecciona la base de datos creada
4. Ve a la pestaña "Importar"
5. Selecciona el archivo `.sql` exportado
6. Haz click en "Continuar"

### 3. Configurar Conexión a Base de Datos

Edita el archivo: `config/database.php`

Ajusta estos valores según tu configuración:

```php
private $host = "localhost";      // Servidor MySQL (normalmente localhost)
private $db_name = "sistema_impresoras";  // Nombre de la base de datos
private $username = "root";        // Usuario MySQL (por defecto "root" en XAMPP)
private $password = "";            // Contraseña MySQL (vacía por defecto en XAMPP)
```

### 4. Configurar URL Base

Edita el archivo: `config/config.php`

**IMPORTANTE**: Ajusta la constante `BASE_URL` según tu configuración:

#### Si instalas en la raíz de htdocs:
```php
define('BASE_URL', 'http://localhost/impresoras');
```

#### Si usas otro nombre de carpeta:
```php
define('BASE_URL', 'http://localhost/NOMBRE_DE_TU_CARPETA');
```

#### Si usas otro puerto (ejemplo: 8080):
```php
define('BASE_URL', 'http://localhost:8080/impresoras');
```

#### Si accedes desde otra computadora en la red local:
```php
define('BASE_URL', 'http://192.168.1.XXX/impresoras');
```
*Reemplaza XXX con la IP de la máquina servidor*

### 5. Iniciar XAMPP

1. Abre el Panel de Control de XAMPP
2. Inicia el servicio **Apache**
3. Inicia el servicio **MySQL**

### 6. Acceder al Sistema

Abre tu navegador y ve a:
```
http://localhost/impresoras
```

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

⚠️ **IMPORTANTE**: Cambia la contraseña del administrador después del primer inicio de sesión.

## Verificación de Instalación

### Checklist:
- [ ] XAMPP Apache ejecutándose (verde en panel de control)
- [ ] XAMPP MySQL ejecutándose (verde en panel de control)
- [ ] Base de datos `sistema_impresoras` creada e importada
- [ ] Archivos descomprimidos en `C:\xampp\htdocs\impresoras`
- [ ] `config/database.php` configurado correctamente
- [ ] `config/config.php` con BASE_URL correcta
- [ ] Puedes acceder a `http://localhost/impresoras` sin errores
- [ ] Login funciona con credenciales por defecto

## Problemas Comunes

### Error: "No se puede conectar a la base de datos"
- Verifica que MySQL esté corriendo en XAMPP
- Revisa las credenciales en `config/database.php`
- Confirma que la base de datos `sistema_impresoras` existe

### Error: "404 Not Found" o recursos no cargan
- Verifica que `BASE_URL` en `config/config.php` sea correcta
- Asegúrate que la carpeta esté en `htdocs/impresoras`
- Si usas otro nombre de carpeta, actualiza BASE_URL

### Página en blanco o error 500
- Activa la visualización de errores PHP:
  - Edita `C:\xampp\php\php.ini`
  - Busca `display_errors = Off`
  - Cámbialo a `display_errors = On`
  - Reinicia Apache en XAMPP
- Revisa los logs en: `C:\xampp\apache\logs\error.log`

### No puedo hacer login
- Verifica que la tabla `usuarios` tenga datos
- Si no hay usuarios, ejecuta en phpMyAdmin:
```sql
INSERT INTO usuarios (username, password, nombre_completo, email, id_rol, activo) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'Administrador', 'admin@sistema.com', 1, 1);
```
*Contraseña: admin123*

## Acceso desde Otra Computadora en Red Local

### En la máquina SERVIDOR (donde está XAMPP):

1. Obtén tu IP local:
   - Abre CMD
   - Ejecuta: `ipconfig`
   - Busca "Dirección IPv4": ejemplo `192.168.1.100`

2. Configura el firewall:
   - Permite conexiones entrantes al puerto 80
   - O desactiva temporalmente el firewall para pruebas

3. Actualiza `config/config.php`:
```php
define('BASE_URL', 'http://192.168.1.100/impresoras');
```

### En las máquinas CLIENTE:

Abre el navegador y ve a:
```
http://192.168.1.100/impresoras
```
*Usa la IP del servidor*

## Estructura de Archivos Importante

```
impresoras/
├── config/
│   ├── config.php        ← Configurar BASE_URL aquí
│   └── database.php      ← Configurar credenciales BD aquí
├── controllers/          (No modificar)
├── models/              (No modificar)
├── views/               (No modificar)
├── assets/              (No modificar)
└── uploads/             (Debe tener permisos de escritura)
```

## Mantenimiento

### Respaldo de Base de Datos
1. Abre phpMyAdmin
2. Selecciona `sistema_impresoras`
3. Click en "Exportar"
4. Selecciona "Rápido" y "SQL"
5. Click en "Continuar"
6. Guarda el archivo `.sql` con fecha

### Respaldo de Archivos
Comprime la carpeta:
```
C:\xampp\htdocs\impresoras
```

## Soporte

Si encuentras problemas:
1. Revisa los logs de Apache: `C:\xampp\apache\logs\error.log`
2. Revisa los logs de MySQL: `C:\xampp\mysql\data\mysql_error.log`
3. Activa errores PHP en `php.ini`
4. Verifica las configuraciones en `config/config.php` y `config/database.php`
