const fs = require('fs');
const s = fs.readFileSync('resources/views/livewire/public/peta.blade.php', 'utf8');
const start = s.indexOf('const CFG');
const a = s.lastIndexOf('<script>', start);
const b = s.indexOf('</script>', start);
let js = s.substring(a + '<script>'.length, b);
js = js.replace('@js($jsConfig)', '{}');
fs.writeFileSync('storage/_peta_check.js', js);
console.log('extracted', js.length, 'chars');
