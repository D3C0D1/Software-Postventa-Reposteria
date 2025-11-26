# 🍰 Software Postventa Repostería

Sistema de gestión integral para reposterías, desarrollado en **PHP** y **MySQL**. Diseñado para ser completamente **responsivo**, permitiendo su uso fluido tanto en dispositivos móviles como en escritorio.

## 🚀 Características Principales

*   **📱 Diseño Responsivo:** Interfaz adaptable a móviles, tablets y escritorio.
*   **🔐 Login y Seguridad:** Sistema de autenticación seguro para usuarios.
*   **📊 Dashboard:** Panel de control con resumen de actividades.
*   **📦 Control de Inventario:** Gestión detallada de insumos y existencias.
*   **🔔 Notificaciones de Stock:** Alertas automáticas cuando el inventario de productos es bajo.
*   **🧁 Gestión de Productos:** Administración de productos de venta, incluyendo imágenes y precios.
*   **📂 Categorías y Subcategorías:** Organización jerárquica de productos.
*   **⚙️ Configuración de Empresa:** Ajustes para facturación y tickets (datos de la empresa, logo, etc.).
*   **👥 Gestión de Usuarios:** Administración de empleados y permisos.
*   **🛒 Punto de Venta (POS):** Interfaz para realizar ventas rápidas.
*   **📄 Facturación y Tickets:** Generación de comprobantes de venta (PDF).

## 📂 Estructura del Proyecto

```
Software-Postventa-Reposteria/
├── 📁 ajax/                     # Scripts para peticiones asíncronas (Stock, Notificaciones)
├── 📁 config/                   # Archivos de configuración (Base de datos)
├── 📁 includes/                 # Fragmentos de código reutilizables (Header, Footer, Auth)
├── 📁 instaladores/             # Scripts de instalación
├── 📁 lib/                      # Librerías externas (FPDF, etc.)
├── 📁 logs/                     # Archivos de registro de errores
├── 📁 public/                   # Recursos públicos (CSS, JS, Imágenes, Uploads)
├── 📁 views/                    # Vistas y plantillas HTML/PHP
├── 📄 ajax_filter_sales.php     # Filtrado de ventas
├── 📄 ajax_handler.php          # Manejador principal de AJAX
├── 📄 base de datos SQL.sql     # Script de importación de la Base de Datos
├── 📄 carrito.php               # Lógica del carrito de compras
├── 📄 checkout.php              # Proceso de finalización de compra
├── 📄 configuracion.php         # Panel de configuración del sistema
├── 📄 configure_xampp_autostart.bat # Script para autoinicio en XAMPP
├── 📄 dashboard.php             # Panel principal
├── 📄 generar_factura.php       # Generador de facturas
├── 📄 index.php                 # Página de inicio / Login
├── 📄 inventario.php            # Gestión de inventario
├── 📄 productos.php             # Gestión de productos
├── 📄 quick_start_xampp.bat     # 🚀 Script de inicialización rápida
├── 📄 usuarios.php              # Gestión de usuarios
└── 📄 ventas.php                # Historial y gestión de ventas
```

## 🛠️ Instalación y Configuración

1.  **Base de Datos:** Importa el archivo `base de datos SQL.sql` en tu servidor MySQL.
2.  **Configuración:** Edita `config/database.php` con tus credenciales de base de datos.
3.  **Inicialización:**
    *   Ejecuta el archivo `quick_start_xampp.bat` para inicializar el software automáticamente después de la instalación (especialmente útil en entornos XAMPP).

## 💻 Requisitos

*   PHP 7.4 o superior
*   MySQL / MariaDB
*   Servidor Web (Apache recomendado)
