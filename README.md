# Requisitos

Se recomienda usar Laragon

-   ✅ PHP 8.2 o mayor (php -v)
-   ✅ Composer (composer -v)
-   ✅ Node JS (node -v)

# Características

Ejemplo proyecto Laravel 10 + vue3 PAra DAW

-   ✅ Laravel 10
-   ✅ Vue 3
-   ✅ VueRouter + Pinia
-   ✅ PrimeVue
-   ✅ Vue i18n Multi Idioma
-   ✅ Iniciar sesión
-   ✅ Restablecimiento de contraseña
-   ✅ Login
-   ✅ Panel de administración
-   ✅ Gestión de perfiles
-   ✅ Gestión de usuarios
-   ✅ Gestión de roles
-   ✅ Gestión de permisos (Spatie)
-   ✅ Cambio de contraseña
-   ✅ Verificación de correo electrónico
-   ✅ Gestión de Posts
-   ✅ Blog de Frontend
-   ✅ Boostrap 5

## Como usar

### Clonar Repositorio

```bash
git clone ....
```

### Instalar vía Composer

Entrar a la carpeta del repositorio

```bash
composer install
```

### Copiar el fichero .env.example a .env edita las credenciales de la base de datos y la url

### Generar Application Key

```bash
php artisan key:generate
```

### Migrar base de datos

```bash
php artisan migrate
```

### Lanzar Seeders

```bash
php artisan db:seed
```

### Instalar las dependencias de Node

```bash
npm install

npm run dev
```

### Lanzar servidor

```bash
php artisan serve
```

### Lanzar a producción

```bash
npm run build or yarn build
```

=======

# Proyecto2

Hundir la flota.

> > > > > > > 68f96d9c4acf28fd9e79e5425482f3b77c61d1bf

Integrar cambios realizados por el compañero:

```bash
git pull origin main
```

-   (main o rama deseada)

Crear link para poder acceder a las imágenes

```bash
php artisan storage:link
```

Comando para sembrar la base de datos

```bash
php artisan db:seed
```

Comando para refrescar la base de datos (eliminar todas las tablas, volver a migrarlas y luego ejecutar los seeders)

```bash
php artisan migrate:fresh --seed

```
DEV TEAM USERS:
```bash
EMAIL                    PASSWORD    ROLE
admin@demo.com           12345678    Admin
danielloberafp@ibf.cat   Asdqwe!23   User
enricruizfp@ibf.cat      Asdqwe!23   User
```
