let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/welcome.js', 'public/js')
   .js('resources/js/keyboard.js', 'public/js')
   .js('resources/js/slides.js', 'public/js')
   .styles([
       'node_modules/reveal.js/css/reveal.css',
       'node_modules/reveal.js/css/theme/serif.css',
   ], 'public/css/reveal.css')
   .scripts([
       'node_modules/midi/inc/shim/Base64.js',
       'node_modules/midi/inc/shim/Base64binary.js',
       'node_modules/midi/inc/shim/WebAudioAPI.js',

       'node_modules/midi/js/midi/audioDetect.js',
       'node_modules/midi/js/midi/gm.js',
       'node_modules/midi/js/midi/loader.js',
       'node_modules/midi/js/midi/player.js',
       'node_modules/midi/js/midi/plugin.audiotag.js',
       'node_modules/midi/js/midi/plugin.webaudio.js',
       'node_modules/midi/js/midi/plugin.webmidi.js',

       'node_modules/midi/js/util/dom_request_xhr.js',
       'node_modules/midi/js/util/dom_request_script.js',
   ], 'public/js/MIDI.js')
   .copyDirectory('node_modules/midi/examples/soundfont', 'public/soundfont')
   .sass('resources/sass/app.scss', 'public/css')
   .less('resources/less/keyboard.less', 'public/css')
   .sourceMaps();

