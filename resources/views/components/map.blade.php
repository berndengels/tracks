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
        getStyle = (feature) => {
            return {
                weight: 5,
                opacity: 1,
                color: 'red',
                dashArray: '3',
            };
        },
        tracks = {!! $tracks !!},
        points = {!! $points !!},
        openStreetMapLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }),
        openSeaMapLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>'
        });

        if(tracks.features.length > 0) {
            const bounds = L.latLngBounds(nordEast, southWest),
                pointLatlngs = points.features.map(c => [
                    c.geometry.coordinates[1],   // Latitude
                    c.geometry.coordinates[0]    // Longitude
                ]);
                center = bounds.getCenter();
        } else {
            const bounds = null,
                pointLatlngs = null,
                center = null;
        }

    console.info('data', tracks.features)

    $(document).ready(() => {


            if(tracks.features.length > 0) {
                const map = L.map('map', {
                    zoom: 12,
                    center: [center.lat, center.lng]
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
        } else {
            const map = L.map('map', {
                zoom: 10,
                center: [54.35, 13.51]
            });
            openStreetMapLayer.addTo(map);
            openSeaMapLayer.addTo(map);
        }
    });

</script>
