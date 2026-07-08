<div id="map"></div>
<script>
    const nordEast = L.latLng({
            lat: {{ $bounds[0][0] }},
            lng: {{ $bounds[0][1] }}
        }),
        southWest = L.latLng({
            lat: {{ $bounds[1][0] }},
            lng: {{ $bounds[1][1] }}
        }),
        tracks = {!! $tracks !!},
        points = {!! $points !!},
        bounds = L.latLngBounds(nordEast, southWest),
        center = bounds.getCenter(),
        pointLatlngs = points.features.map(c => [
            c.geometry.coordinates[1],   // Latitude
            c.geometry.coordinates[0]    // Longitude
        ]);

    $(document).ready(() => {
        map = L.map('map', {
                zoom: 8,
                center: [center.lat, center.lng]
            }),
            getStyle = (feature) => {
                return {
                    weight: 5,
                    opacity: 1,
                    color: 'red',
                    dashArray: '3',
                };
            },
            openStreetMapLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }),
            openSeaMapLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>'
            }),
            lineStringLayer = L.geoJSON(tracks, {
                style: getStyle
            }),
            pointLayer = L.polyline(pointLatlngs, {
                weight: 6,
                opacity: 0,
                fillOpacity: 0
            }),
            attributeControl = L.control.attribution({
                position: 'topright'
            }).addAttribution(`Points: ${points.features.length}`)
        ;
        pointLayer.on("click", (e) => {
            let nearest = null,
                minDistance = Infinity;

            points.features.forEach(feature => {
                const latlng = L.latLng(
                        feature.geometry.coordinates[1],
                        feature.geometry.coordinates[0]
                    ),
                    distance = e.latlng.distanceTo(latlng);

                if (distance < minDistance) {
                    minDistance = distance;
                    nearest = feature;
                }
            });

            const p = nearest.properties;

            L.popup()
                .setLatLng(e.latlng)
                .setContent(`<b>${p.datetime}</b><br>${p.track.name}<br>Start ${p.track.start} Ende ${p.track.end}<br>Speed: ${p.speed} kn`)
                .openOn(map);
        });
        openStreetMapLayer.addTo(map);
        openSeaMapLayer.addTo(map);
        lineStringLayer.addTo(map);
        pointLayer.addTo(map)
        attributeControl.addTo(map)
        map.fitBounds(lineStringLayer.getBounds());
    });

</script>
