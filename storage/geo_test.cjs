const fs = require("fs");
let src = fs.readFileSync("storage/full_script.js", "utf8");
const start = src.indexOf("// ---------- matematika geo");
const end = src.indexOf("// ---------- peta Leaflet");
if (start < 0 || end < 0) { console.error("marker tidak ketemu"); process.exit(1); }
eval(src.slice(start, end));

const RASTER_MAX_SPAN_DEG = 0.5;
const fc = JSON.parse(fs.readFileSync("public/data/data_2021.geojson", "utf8"));
const kab = JSON.parse(fs.readFileSync("storage/kabupaten.json", "utf8"));

// replika prepareRaster
const features = [];
let totalCount = 0, totalArea = 0;
for (const f of fc.features) {
    const g = f.geometry;
    if (!g || (g.type !== "Polygon" && g.type !== "MultiPolygon")) continue;
    const bbox = geomBbox(g);
    if ((bbox.maxLng - bbox.minLng) > RASTER_MAX_SPAN_DEG || (bbox.maxLat - bbox.minLat) > RASTER_MAX_SPAN_DEG) continue;
    const area = geomAreaKm2(g);
    features.push({ geom: g, bbox, area, count: Number(f.properties.count || 0) });
    totalCount += f.properties.count || 0; totalArea += area;
}
console.log("raster 2021: sel=" + features.length + " totalCount=" + totalCount + " totalArea=" + totalArea.toFixed(2) + " km2");

let sumSel = 0;
for (const kf of kab.features) {
    const bg = kf.geometry;
    const bb = geomBbox(bg);
    let sel = 0, count = 0, area = 0;
    for (const f of features) {
        if (f.bbox.maxLng < bb.minLng || f.bbox.minLng > bb.maxLng || f.bbox.maxLat < bb.minLat || f.bbox.minLat > bb.maxLat) continue;
        eachSubPolygon(f.geom, function (poly) {
            const c = ringCentroid(poly[0]);
            if (!geomContains(bg, c[0], c[1])) return;
            const a = polyAreaKm2(poly);
            area += a; sel++; count += f.count * (f.area > 0 ? a / f.area : 1);
        });
    }
    sumSel += sel;
    console.log(kf.properties.name.padEnd(20) + " sel=" + String(sel).padStart(5) + " count=" + Math.round(count).toString().padStart(9) + " area=" + area.toFixed(2) + " km2");
}
console.log("SUM kabupaten sel=" + sumSel + " (" + (100 * sumSel / features.length).toFixed(1) + "% dari total file)");