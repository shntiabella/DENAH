const fs = require("fs");
let src = fs.readFileSync("storage/full_script.js", "utf8");
eval(src.slice(src.indexOf("// ---------- matematika geo"), src.indexOf("// ---------- peta Leaflet")));
const fc = JSON.parse(fs.readFileSync("public/data/data_2021.geojson", "utf8"));
const kab = JSON.parse(fs.readFileSync("storage/kabupaten.json", "utf8"));
const bu = kab.features.find(f => f.properties.name === "Bengkulu Utara");
console.log("Bengkulu Utara geom type:", bu.geometry.type, "area=", geomAreaKm2(bu.geometry).toFixed(2), "km2");
const bbBU = geomBbox(bu.geometry);
console.log("BU bbox:", JSON.stringify(bbBU));
// area tiap kabupaten (validasi data batas)
for (const kf of kab.features) console.log(kf.properties.name.padEnd(20), "geomArea=", geomAreaKm2(kf.geometry).toFixed(1), "km2");
// cari kontributor terbesar utk BU
const feats = fc.features.map(f => ({ g: f.geometry, c: f.properties.count || 0 }));
let rows = [];
for (const f of feats) {
  let a = 0;
  eachSubPolygon(f.g, function (poly) {
    const c = ringCentroid(poly[0]);
    if (geomContains(bu.geometry, c[0], c[1])) a += polyAreaKm2(poly);
  });
  if (a > 0) rows.push({ area: a, count: f.c, bbox: geomBbox(f.g) });
}
rows.sort((x, y) => y.area - x.area);
for (const r of rows.slice(0, 5)) console.log("kontributor BU: area=" + r.area.toFixed(2) + " count=" + r.count + " bbox=" + JSON.stringify(r.bbox));