<div id="map"></div>
<script>
    $(document).ready(() => {
        const bounds = L.bounds(L.point({{ $bounds[0][0] }}, {{ $bounds[0][1] }}), L.point({{ $bounds[1][0] }}, {{ $bounds[1][1] }})),
            tracks = {!! $tracks !!},
            center = bounds.getCenter(),
            map = L.map('map', {
                zoom: 8,
                center: [center.x, center.y]
            }),
            baseLayer = L.tileLayer('https://t2.openseamap.org/tile/{z}/{x}/{y}.png', {
                maxZoom: 19,
//                errorTileUrl: 'path-to-error-tile.png',
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
            }).addTo(map);

        $(tracks).each((t, v) => {
            let lastPoint = v.points[v.points.length - 1].point,
//            console.info('lastPoint', lastPoint)
                marker = L.marker(lastPoint).addTo(map),
                latlngs = v.points.map(p => p.point),
                polyline = L.polyline(latlngs, {color: 'red'}).addTo(map);

            marker.bindTooltip(v.name);
/*
            $(v.points).each((i,v) => {
                L.tooltip()
                    .setLatLng(v.point)
                    .setContent(v.time + ": Speed: " + v.speed)
                    .addTo(map);
            });
*/
        });
    })
</script>
