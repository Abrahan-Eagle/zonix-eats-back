const mix = require('laravel-mix');
const path = require('path');

// Configuración principal
mix.options({
    processCssUrls: false,
    terser: {
        extractComments: false,
    }
});

// Configuración de alias para paths comunes
mix.webpackConfig({
    resolve: {
        alias: {
            '@': path.resolve('resources/js'),
            '~': path.resolve('resources')
        }
    }
});

// Compilación de CSS
// CSS Frontend Legacy (Template antiguo)
mix.styles([
    "resources/assets/frontend/legacy/css/bootstrap.min.css",
    "resources/assets/frontend/legacy/css/font-awesome.min.css",
    "resources/assets/frontend/legacy/css/elegant-icons.css",
    "resources/assets/frontend/legacy/css/owl.carousel.min.css",
    "resources/assets/frontend/legacy/css/magnific-popup.css",
    "resources/assets/frontend/legacy/css/slicknav.min.css",
    "resources/assets/frontend/legacy/css/style.css"
], "public/css/vendor.css")
// CSS CoreUI (Dashboard framework)
.styles('resources/assets/coreui/css/app.css', 'public/css/back-app.css')
// CSS Toastr (Notificaciones)
.styles([
    "node_modules/toastr/build/toastr.min.css"
], "public/css/toastr.min.css")
// CSS Laravel (SASS)
.sass('resources/sass/app.scss', 'public/css/app.css')
// CSS Frontend Corral X (Template nuevo One-Page)
.styles('resources/assets/frontend/corralx/css/styles.css', 'public/css/front.css');

// Compilación de JavaScript
// JS Frontend Legacy (Template antiguo)
mix.scripts([
    "resources/assets/frontend/legacy/js/jquery-3.3.1.min.js",
    "resources/assets/frontend/legacy/js/bootstrap.min.js",
    "resources/assets/frontend/legacy/js/jquery.magnific-popup.min.js",
    "resources/assets/frontend/legacy/js/mixitup.min.js",
    "resources/assets/frontend/legacy/js/masonry.pkgd.min.js",
    "resources/assets/frontend/legacy/js/jquery.slicknav.js",
    "resources/assets/frontend/legacy/js/owl.carousel.min.js",
    "resources/assets/frontend/legacy/js/main.js"
], "public/js/frontend-vendor.js") // Was vendor.js - Renamed to avoid conflict with .extract()
// JS CoreUI (Dashboard framework)
.scripts([
    "resources/assets/coreui/js/app.js",
    "resources/assets/coreui/js/app2.js"
], "public/js/back-app.js")
.scripts([
    "node_modules/toastr/build/toastr.min.js"
], "public/js/toastr.min.js")
.js('resources/js/app.js', 'public/js/app.js')
.extract(['jquery', 'bootstrap']);

// Copiar assets estáticos
// Frontend Legacy: Fonts e imágenes del template antiguo
mix.copyDirectory("resources/assets/frontend/legacy/fonts", "public/fonts")
// .copyDirectory("resources/assets/frontend/legacy/images/img", "public/img")
// Frontend Corral X: Imágenes del template nuevo
.copyDirectory("resources/assets/frontend/corralx/images", "public/assets/front/images")
// Frontend Legacy: Imágenes de usuario (avatars por defecto)
// .copyDirectory("resources/assets/frontend/legacy/images/images/user", "public/images/user")
// CoreUI: SVG icons
.copyDirectory("resources/assets/coreui/svg", "public/icons/svg/free")
// Dashboard: Copiar solo assets estáticos seguros, evitar JS/CSS compilados
.copyDirectory("resources/assets/dashboard/fonts", "public/fonts")
.copyDirectory("resources/assets/dashboard/images", "public/images")
.copyDirectory("resources/assets/dashboard/icons", "public/icons")
.copyDirectory("resources/assets/dashboard/svg", "public/svg")
// .copyDirectory("resources/assets/dashboard", "public") // COMENTADO: Evitar sobrescribir public/js/back-app.js
// CoreUI Icons desde node_modules
.copyDirectory("node_modules/@coreui/icons/fonts", "public/fonts")
.copyDirectory("node_modules/@coreui/icons/svg/flag", "public/svg/flag")
.copyDirectory("node_modules/@coreui/icons/sprites/", "public/icons/sprites")
.copyDirectory("node_modules/@coreui/icons/svg/free/", "public/icons/svg/free");

// Configuración de jQuery
mix.autoload({
    jquery: ['$', 'window.jQuery', 'jQuery']
});

// Configuración para entornos
if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps()
       .browserSync({
           proxy: 'localhost:8000',
           open: false,
           files: [
               'resources/views/**/*.php',
               'public/js/**/*.js',
               'public/css/**/*.css'
           ]
       });
}
