const fs = require('fs');
const p = 'resources/views/livewire/public/peta.blade.php';
let s = fs.readFileSync(p, 'utf8');

// 1) buang baris return duplikat (baris mati setelah return pertama)
const dup = /([ \t]*return cls === state\.classificationId;\r?\n)\1/;
if (dup.test(s)) {
    s = s.replace(dup, '$1');
    console.log('dup return: fixed');
} else {
    console.log('dup return: not found');
}

// 2) perbaiki mojibake "kmÂ²" (double-encoded UTF-8) menjadi "km²"
const mojibake = 'km\u00c2\u00b2';
if (s.includes(mojibake)) {
    s = s.split(mojibake).join('km\u00b2');
    console.log('mojibake: fixed');
} else {
    console.log('mojibake: not found');
}

fs.writeFileSync(p, s);
console.log('done, length=' + s.length);
