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
        bounds = L.latLngBounds(nordEast, southWest),
        center = bounds.getCenter();

    $(document).ready(() => {
                const map = L.map('map', {
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
                    onEachFeature = (feature,layer) => {
                        layer.bindPopup('');
                        layer.on('popupopen', (e) => {
                            let found = null,
                                popup = e.popup,
                                latLng = popup.getLatLng(),
                                lat = latLng.lat.toFixed(6),
                                lng = latLng.lng.toFixed(6),
                                id = feature.properties.id,
                                speed = tracks.features.find((track) => {
                                    if(id === track.properties.id) {
                                        console.info('track_id', track.properties.id)
                                        track.geometry.coordinates.forEach((v, i) => {
                                            if(lng == v[0] && lat == v[1]) {
                                                found = v;
                                                return;
                                            }
                                        });
//                                        let found = track.geometry.coordinates.find((c) => (lng == c[0] && lat == c[1])) ?? null;
//                                        console.info('idx', idx)
                                        console.info('found', found)

                                        if(found) {
                                            return found;
                                        }

                                        return null;
                                    }
                                }) ?? null;

                            console.info('lat', lat)
                            console.info('lng', lng)
                            console.info('speed', speed)
                            if(speed) {
                                popup.setContent(speed.toString());
                            }
                        });
                    },
                    // 13.860626
                    openStreetMapLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }),
                    openSeaMapLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
                        attribution: 'Map data: &copy; <a href="http://www.openseamap.org">OpenSeaMap</a> contributors'
                    }),
                    geoJsonLayer = L.geoJSON(tracks, {
                            style: getStyle,
                            onEachFeature: onEachFeature
                        })
                        .addTo(map);

                openStreetMapLayer.addTo(map);
                openSeaMapLayer.addTo(map);
//                geoJsonLayer.addTo(map);

                map.fitBounds(bounds);

//                console.info('tracks', tracks);
    })
</script>
