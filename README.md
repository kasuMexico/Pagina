# KASU - Página Web Oficial

Bienvenido al repositorio de la página web oficial de **KASU** ([kasu.com.mx](https://kasu.com.mx)).  
Este proyecto representa la plataforma en línea de **KASU**, proporcionando información sobre nuestros servicios, opiniones de clientes y opciones de contacto.

## 🚀 Características

- 🌐 **Diseño Responsivo**: Adaptable a dispositivos móviles y de escritorio.
- 🔍 **SEO Optimizado**: Mejores prácticas para indexación en motores de búsqueda.
- 📝 **Sección de Testimonios**: Opiniones verificadas de clientes.
- 📊 **Integración con Google Tag Manager & Facebook Pixel**: Seguimiento y análisis de tráfico.
- 🔒 **Seguridad Mejorada**: Protección contra inyecciones SQL y validaciones de datos.

---

## 📂 Estructura del Proyecto

├── assets/ # Archivos estáticos (CSS, JS, imágenes) 
├── eia/ # Librerías y archivos PHP auxiliares 
├── hmtl/ # (Posible error, debería ser html/) Archivos de la interfaz HTML 
├── login/ # Módulo de autenticación de usuarios 
├── opiniones/ # Gestión de testimonios y comentarios 
├── patro/ # (Pendiente revisar su propósito) 
├── php/ # Scripts backend y controladores 
├── vendor/ # Dependencias gestionadas con Composer 
├── index.php # Página de inicio 
├── testimonios.php # Página de testimonios de clientes 
├── composer.json # Archivo de dependencias PHP 
├── README.md # Documentación del proyecto


---

## 🛠️ **Requisitos del Proyecto**

Antes de ejecutar este proyecto, asegúrate de tener instalado:

- **PHP** (Versión 7.4 o superior)
- **MySQL / MariaDB** (Para la gestión de base de datos)
- **Apache / Nginx** (Servidor web)
- **Composer** (Para manejar dependencias en PHP)

---
## 🔧 **Instalación y Configuración**

### 1️⃣ Clonar el Repositorio

```bash
git clone https://github.com/kasuMexico/Pagina.git
cd Pagina
```

### 2️⃣ Instalar Dependencias
```bash
composer install
```

### 3️⃣ Configurar el Archivo .env
Crea un archivo .env en la raíz del proyecto y configura la base de datos:
```ini
DB_HOST=localhost
DB_NAME=kasu_db
DB_USER=root
DB_PASS=tu_contraseña
```

### 4️⃣ Importar la Base de Datos
Ejecuta el siguiente comando en MySQL para importar la base de datos:
```sql
SOURCE database/kasu.sql;
```
### 5️⃣ Iniciar el Servidor
Si estás utilizando PHP nativo, puedes iniciar un servidor local con:
```bash
php -S localhost:8000
```
Si utilizas Apache, asegúrate de configurar el VirtualHost en httpd.conf.

📞 Contacto
Si tienes alguna pregunta o sugerencia sobre este proyecto, no dudes en ponerte en contacto con el equipo de KASU.

- 🌍 Sitio Web: [kasu.com.mx](https://kasu.com.mx/)
- ✉️ Correo Electrónico: contacto@kasu.com.mx
- 📌 GitHub: @kasuMexico
- 🎯 Licencia
Este proyecto es de código privado y propiedad de KASU. No está permitido su uso sin autorización.
```yaml
© 2025 KASU - Todos los derechos reservados.
```
