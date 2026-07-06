<div id="map"></div>
<script>
    $(document).ready(() => {
        const bounds = L.bounds(L.point({{ $bounds[0][0] }}, {{ $bounds[0][1] }}), L.point({{ $bounds[1][0] }}, {{ $bounds[1][1] }})),
            tracks = {!! $tracks !!},
            center = bounds.getCenter(),
            map = L.map('map', {
                center: [center.x, center.y],
                zoom: 8
            }),
            tileLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

        $(tracks).each((t, v) => {
            let lastPoint = v.points[v.points.length - 1].point;
            console.info('lastPoint', lastPoint)

            let marker = L.marker(lastPoint).addTo(map);
            marker.bindTooltip(v.name);

            let latlngs = v.points.map(p => p.point)
            let polyline = L.polyline(latlngs, {color: 'red'}).addTo(map);
/*
            $(v.points).each((i,v) => {
                L.tooltip()
                    .setLatLng(v.point)
                    .setContent(v.time + ": Speed: " + v.speed)
                    .addTo(map);
            });
*/
        });
/*
        map.fitBounds(polyline.getBounds());
*/
    })
</script>
