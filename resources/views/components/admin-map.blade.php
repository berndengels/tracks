@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div id="adminMap"></div>

<script>
    var $media, $wrapper, marker = {};
    const fallbackLatLng = L.latLng([54.35, 13.51]),
        isMobile = WURFL.is_mobile,
        $h = $('<h6>'),
        $span = $('<span class="float-end pos">'),
        mediaWidth = '600',
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
        contentDiv = (filename, title, pos, type = 'image') => {
            var src;
            $wrapper = $('<div>');
            $wrapper.css({
                width: mediaWidth + 'px',
                margin: 0,
                padding: 0,
            });
            $h.text(title)
            $span.text(pos[0] +" N "+ pos[1] + " E").appendTo($h)
            if('image' === type) {
                src = '/storage/media/images/' + filename;
                $media = $('<img>');
                $media.attr({
                    src: src,
                    alt: title,
                    width: mediaWidth,
                });
            } else {
                src = '/storage/media/videos/' + filename;
                $media = $('<video>'), $source = $('<source>');
                $media.attr({
                    id: 'video',
                    with: mediaWidth,
                    controls: true,
                    autoplay: true
                });
                $source.attr({
                    type: 'video/mp4',
                    src: src,
                })
                $source.appendTo($media)
            }

            $h.appendTo($wrapper);
            $media.appendTo($wrapper);

            return $wrapper.html();
        },
        track = {!! $track !!},
        points = {!! $points !!},
        hasData = true,
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
        if(track) {
                const map = L.map('adminMap', {
                    zoom: 12,
                    maxZoom: 19,
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
                }).addAttribution(`Points: ${points.features.length}`);

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
                    .setContent(`<b>${moment(p.datetime).format("dddd DD.MM.YYYY HH:mm")}</b><br>
                        ${p.track.name}<br>
                        Start ${p.track.start} Ende ${p.track.end}<br>
                        Speed: ${p.speed} kn<br>
                        Turnlänge insgesamt:<br>
                        ${p.track.km} km, ${p.track.nm} nm`)
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
