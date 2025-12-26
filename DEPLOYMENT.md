# Sistema de Control de Impresoras
## Guía de Despliegue en XAMPP (PHP 8.2 + MySQL)

---

## ✅ **Estado del Proyecto**
El proyecto ha sido desplegado exitosamente en XAMPP.

---

## 📋 **Requisitos**
- ✅ XAMPP con PHP 8.2 y MySQL/MariaDB
- ✅ Navegador web moderno

---

## 🗄️ **Base de Datos**
### Estado: ✅ CONFIGURADA

La base de datos `sistema_impresoras` ha sido creada e inicializada con:
- 13 tablas principales
- 2 vistas (vista_equipos_completa, vista_mantenimientos)
- Triggers para auditoría automática
- Datos iniciales:
  - 3 Roles (Administrador, Encargado, Usuario)
  - 5 Estados de equipo
  - 6 Tipos de demanda
  - 1 Usuario administrador

---

## 👤 **Credenciales de Acceso**

### Usuario Administrador
- **Usuario:** `admin`
- **Contraseña:** `admin123`

---

## 🚀 **Acceso al Sistema**

### URL Principal
```
http://localhost/impresoras
```

### URL del Login
```
http://localhost/impresoras/controllers/auth.php
```

---

## 📁 **Estructura del Proyecto**

```
impresoras/
├── assets/
│   ├── css/
│   ├── img/
│   ├── imagenes/          ← 📸 Coloca aquí la imagen 2.png para el fondo del login
│   └── js/
├── config/
│   ├── config.php         ← Configuración general
│   └── database.php       ← Conexión a BD
├── controllers/
│   ├── auth.php          ← Controlador de autenticación
│   └── equipos.php       ← Controlador de equipos
├── database/
│   └── schema.sql        ← Esquema de BD (YA IMPORTADO)
├── includes/
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── models/
│   ├── Equipo.php
│   ├── Mantenimiento.php
│   └── Usuario.php
├── views/
│   ├── layout.php        ← Template base ✅
│   ├── login.php         ← Nueva interfaz de login ✅
│   ├── dashboard.php
│   └── equipos/
├── uploads/
├── index.php
└── README.md
```

---

## 🎨 **Nueva Interfaz de Login**

### Características Implementadas ✅
- ✨ Diseño moderno y elegante
- 🎭 Animación de entrada desde la izquierda
- 🖼️ Fondo con imagen personalizable
- 📱 Responsive design
- ✔️ Checkbox "Recuérdame"
- 🎨 Colores azules corporativos
- 🔒 Validación de campos

### 📸 **Imagen de Fondo**
Por favor, coloca tu imagen de fondo en:
```
c:\xampp\htdocs\impresoras\assets\imagenes\2.png
```

**Si no tienes la imagen**, el login funcionará con el gradiente de fondo.

---

## ⚙️ **Configuración del Sistema**

### 1. Configuración de Base de Datos
Archivo: `config/database.php`
```php
private $host = "localhost";
private $db_name = "sistema_impresoras";
private $username = "root";
private $password = "";  // Sin contraseña por defecto en XAMPP
```

### 2. Configuración General
Archivo: `config/config.php`
```php
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/impresoras');
```

---

## 🔧 **Solución de Problemas**

### ❌ Error: "No se puede conectar a la base de datos"
**Solución:**
1. Verifica que MySQL esté corriendo en XAMPP Control Panel
2. Verifica las credenciales en `config/database.php`

### ❌ Error: "Headers already sent"
**Solución:**
1. Verifica que no haya espacios antes de `<?php` en los archivos
2. Asegúrate de que los archivos estén guardados en UTF-8 sin BOM

### ❌ La imagen de fondo no se muestra
**Solución:**
1. Coloca la imagen en `assets/imagenes/2.png`
2. Verifica que el formato sea PNG, JPG o similar
3. Verifica los permisos de la carpeta

### ❌ Error 404 al acceder
**Solución:**
1. Verifica que Apache esté corriendo
2. Confirma la URL: `http://localhost/impresoras`
3. Verifica que el proyecto esté en `c:\xampp\htdocs\impresoras`

---

## 📊 **Próximos Pasos**

1. **Agregar imagen de fondo del login:**
   - Coloca `2.png` en `assets/imagenes/`

2. **Completar el dashboard:**
   - El archivo `views/dashboard.php` necesita ser desarrollado

3. **Módulo de Equipos:**
   - Listar equipos
   - Crear/Editar equipos
   - Gestionar mantenimientos

4. **Módulo de Reportes:**
   - Reportes de mantenimiento
   - Estadísticas de equipos

---

## 🛡️ **Seguridad**

✅ Implementado:
- Sesiones seguras con `session_regenerate_id()`
- Sanitización de entradas
- Contraseñas hasheadas con `password_hash()`
- Validación de usuario activo
- Auditoría de login

---

## 📝 **Notas Técnicas**

- **PHP:** 8.2+
- **Base de Datos:** MySQL 8.0+ / MariaDB 10.4+
- **Charset:** UTF-8 (utf8mb4)
- **Framework CSS:** Bootstrap 5.3
- **Iconos:** Font Awesome 6.4
- **Fuentes:** Google Fonts (Inter)

---

## 📞 **Soporte**

Para cualquier problema o consulta, revisa:
1. Los logs de Apache: `c:\xampp\apache\logs\error.log`
2. Los logs de PHP: Revisar `display_errors` en `config/config.php`
3. La consola del navegador (F12) para errores JavaScript

---

## 🎉 **¡Proyecto Listo para Desarrollo!**

El sistema base está configurado y funcionando. Ahora puedes:
- ✅ Iniciar sesión con admin/admin123
- ✅ Comenzar a desarrollar los módulos restantes
- ✅ Personalizar la interfaz según tus necesidades

---

**Última actualización:** 20 de Diciembre, 2025
**Versión:** 1.0.0
