// Diagnostics for served Peta & Statistik pages + compiled views + GeoJSON props
const fs = require('fs');
const http = require('http');

function get(url) {
  return new Promise((resolve) => {
    const u = new URL(url);
    const req = http.get({ hostname: u.hostname, port: u.port || 80, path: u.pathname + u.search }, (res) => {
      let d = '';
      res.on('data', (c) => d += c);
      res.on('end', () => resolve({ code: res.statusCode, body: d }));
    });
    req.on('error', (e) => resolve({ code: 0, body: 'ERR:' + e.message }));
    req.setTimeout(15000, () => { req.destroy(); resolve({ code: 0, body: 'TIMEOUT' }); });
  });
}

function extract(str, marker, len) {
  const i = str.indexOf(marker);
  if (i === -1) return '(NOT FOUND)';
  return str.slice(i, i + len);
}

(async () => {
  // 1) Served statistik buildUrl + all /api/ refs
  const st = await get('http://127.0.0.1:8000/statistik');
  console.log('=== SERVEDED /statistik (len=' + st.body.length + ', code=' + st.code + ') ===');
  // find all /api/ occurrences
  {
    let i = 0, cnt = 0;
    while ((i = st.body.indexOf('/api/', i)) !== -1 && cnt < 20) {
      console.log('  /api/ @' + i + ': ...' + st.body.slice(i, i + 60).replace(/\n/g, '\\n') + '...');
      i++; cnt++;
    }
  }
  console.log('  buildUrl region:');
  console.log('    ' + extract(st.body, 'function buildUrl', 260).replace(/\n/g, '\\n'));
  console.log('  load region:');
  console.log('    ' + extract(st.body, 'function load()', 220).replace(/\n/g, '\\n'));

  // 2) Served peta statsParams
  const pt = await get('http://127.0.0.1:8000/peta');
  console.log('\n=== SERVED /peta (len=' + pt.body.length + ', code=' + pt.code + ') ===');
  console.log('  statsParams region:');
  console.log('    ' + extract(pt.body, 'function statsParams', 420).replace(/\n/g, '\\n'));
  console.log('  endpoints region (jsConfig):');
  console.log('    ' + extract(pt.body, "endpoints\",", 260).replace(/\n/g, '\\n'));

  // 3) Compiled views
  console.log('\n=== compiled views ===');
  const cvdir = 'D:/project_kp/storage/framework/views';
  if (fs.existsSync(cvdir)) {
    for (const f of fs.readdirSync(cvdir)) {
      if (!f.endsWith('.php')) continue;
      const fp = cvdir + '/' + f;
      let c = '';
      try { c = fs.readFileSync(fp, 'utf8', ); } catch { continue; }
      if (c.includes('buildUrl') || c.includes('statsParams') || c.includes('filter-regency')) {
        const st2 = fs.statSync(fp);
        console.log('  ' + f + ' mtime=' + st2.mtime.toISOString() + ' has buildUrl=' + c.includes('buildUrl') + ' statsParams=' + c.includes('statsParams'));
        // does this compiled view reference settlement-stats or get-statistics?
        console.log('    settlement-stats? ' + c.includes('settlement-stats') + ' | get-statistics? ' + c.includes('get-statistics'));
      }
    }
  } else {
    console.log('  no compiled views dir');
  }

  // 4) GeoJSON property key scan
  for (const yf of ['data_2021.geojson']) {
    const fp = 'D:/project_kp/public/data/' + yf;
    console.log('\n=== ' + yf + ' ===');
    // count region-property key occurrences
    const buf = fs.readFileSync(fp, 'utf8');
    for (const key of ['WADMKK', 'WADMKC', 'WADMD', 'NAMOBJ', 'DESA', 'KABUPATEN', '"regency"', '"district"', '"village"', '"name"']) {
      let cnt = 0, i = 0;
      while ((i = buf.indexOf(key, i)) !== -1) { cnt++; i += key.length; }
      console.log('  ' + key + ' count: ' + cnt);
    }
  }
})();
