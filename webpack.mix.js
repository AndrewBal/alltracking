const mix = require('laravel-mix');

// mix.js('resources/js/app.js', 'public/js')
//     .vue()
//     .sass('resources/sass/app.scss', 'public/css');

//mix.sass('resources/sass/app.scss', 'public/dashboard/css/uikit.min.js');


mix.js([
    'resources/vue/app.js',
], 'public/js/vue.js').vue();
