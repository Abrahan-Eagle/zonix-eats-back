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
// CSS CoreUI (Dashboard framework)
mix.styles('resources/assets/coreui/css/app.css', 'public/css/back-app.css')
// CSS Toastr (Notificaciones)
.styles([
    "node_modules/toastr/build/toastr.min.css"
], "public/css/toastr.min.css")
// CSS Laravel (SASS)
.sass('resources/sass/app.scss', 'public/css/app.css');

// Compilación de JavaScript
// JS CoreUI (Dashboard framework)
mix.scripts([
    "resources/assets/coreui/js/app.js",
    "resources/assets/coreui/js/app2.js"
], "public/js/back-app.js")
.scripts([
    "node_modules/toastr/build/toastr.min.js"
], "public/js/toastr.min.js")
.js('resources/js/app.js', 'public/js/app.js')
.extract(['jquery', 'bootstrap']);

// Copiar assets estáticos
// CoreUI: SVG icons
mix.copyDirectory("resources/assets/coreui/svg", "public/icons/svg/free")
// Dashboard: Copiar solo assets estáticos seguros, evitar JS/CSS compilados
.copyDirectory("resources/assets/dashboard", "public")
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
