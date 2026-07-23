@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div id="map"></div>
<div id="image" class="d-none">
    <img src="" width="800" alt="" />
</div>
<div id="video" class="d-none">
    <video width="800" name="" controls autoplay>
        <source src="" />
        Ihr Browser unterstützt dieses Videoformat nicht.
    </video>
</div>
<script>
    const fallbackLatLng = L.latLng([54.35, 13.51]),
        nordEast = L.latLng({
            lat: {{ $bounds[0][0] }},
            lng: {{ $bounds[0][1] }}
        }),
        southWest = L.latLng({
            lat: {{ $bounds[1][0] }},
            lng: {{ $bounds[1][1] }}
        }),
        getStyle = () => {
            return {
                weight: 5,
                opacity: 1,
                color: 'red',
                dashArray: '3',
            };
        },
        getSpeed = (meters, seconds) => {
            return (meters / seconds * 1.944).toFixed(1);
        },
        tracks = {!! $tracks !!},
        points = {!! $points !!},
        media = {!! $media !!},
        duration = {!! $duration !!},
        hasData = tracks.features.length > 0,
        openStreetMapLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }),
        openSeaMapLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>'
        }),
        bounds = hasData ? L.latLngBounds(nordEast, southWest) : null,
        pointLatlngs = hasData ? points.features.map(c => [
            c.geometry.coordinates[1],   // Latitude
            c.geometry.coordinates[0]    // Longitude
        ]) : null,
        center = hasData ? bounds.getCenter() : fallbackLatLng;

    $(document).ready(() => {
        if(tracks.features.length > 0) {
                const map = L.map('map', {
                    zoom: 12,
                    center: [center.lat, center.lng]
                }),
                lineStringLayer = tracks.features.length > 0 ? L.geoJSON(tracks, {
                    style: getStyle
                }) : null,
                pointLayer = L.polyline(pointLatlngs, {
                    weight: 6,
                    opacity: 0,
                    fillOpacity: 0
                }),
                attributeControl = L.control.attribution({
                    position: 'topright'
                }).addAttribution(`Points: ${points.features.length}, Time: ${duration} sec`);

            if(media.features.length > 0) {
                var src,content;
                media.features.forEach(m => {
//                    console.info('latLng', m.geometry.coordinates)
                    var marker = L.marker(m.geometry.coordinates).addTo(map);
                    switch (m.properties.type) {
                        case 'video':
                            src = '/storage/media/videos/' + m.properties.filename;
                            $el = $('#video').clone()
                            $('video', $el).attr({name: m.properties.name})
                            $('video source', $el).attr({src: src})
                            content = $el.html()
                            break
                        case 'image':
                        default:
                            src = '/storage/media/images/' + m.properties.filename;
                            $el = $('#image').clone()
                            $('img', $el).attr({alt: m.properties.name, src: src})
                            content = $el.html()
                            break;
                    }
                    var popup = L.popup({minWidth: 800})
                        .setLatLng(m.geometry.coordinates)
                        .setContent(content);

                    marker.on('click', () => {
                        popup.openOn(map)
                    })
                });
            }

            pointLayer.on("click", (e) => {
                var nearest = null,
                    minDistance = Infinity,
                    prevDateTime = null,
                    prevLatLng = null,
                    speed = 0;

                points.features.forEach((feature, idx) => {
                    if(idx > 0) {
                        const prev = points.features[idx - 1];
                        prevDateTime = moment(prev.properties.datetime);
                        prevLatLng = L.latLng(
                            prev.geometry.coordinates[1],
                            prev.geometry.coordinates[0]
                        );
                    }

                    const latlng = L.latLng(
                            feature.geometry.coordinates[1],
                            feature.geometry.coordinates[0]
                        ),
                        deateTime = moment(feature.properties.datetime),
                        distance = e.latlng.distanceTo(latlng);

                    if(prevDateTime && prevLatLng) {
                        const myDistance = prevLatLng.distanceTo(latlng),
                            duration = moment.duration(deateTime.diff(prevDateTime)),
                            seconds = duration.asSeconds();

                        speed = getSpeed(myDistance, seconds);
                    }

                    if (distance < minDistance) {
                        minDistance = distance;
                        feature.properties.speed = speed;
                        nearest = feature;
                    }
                });

                const p = nearest.properties;

//                console.info('p', p)
                L.popup()
                    .setLatLng(e.latlng)
                    .setContent(`<b>${moment(p.datetime).format("dd DD.MM.YYYY HH:mm")}</b><br>${p.track.name}<br>Start ${p.track.start} Ende ${p.track.end}<br>Speed: ${p.speed} kn`)
                    .openOn(map);
            });
            openStreetMapLayer.addTo(map);
            openSeaMapLayer.addTo(map);
            lineStringLayer.addTo(map);
            pointLayer.addTo(map);
            attributeControl.addTo(map);
            map.fitBounds(lineStringLayer.getBounds());
        } else {
            const map = L.map('map', {
                zoom: 10,
                center: fallbackLatLng
            });
            openStreetMapLayer.addTo(map);
            openSeaMapLayer.addTo(map);
        }
    });
</script>
