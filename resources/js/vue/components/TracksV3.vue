<template>
    <div>
        <div id="loader" v-if="loading" class="text-center p-5 d-flex justify-content-between">
            <h3>Lade Daten
                <VueSpinnerHourglass color="red" size="50" margin="auto"/>
            </h3>
        </div>
        <div id="info"></div>
        <div id="year">
            <div v-if="initYearOptions" class="years">
                <select class="form-control" v-model="year" @change="selectYear">
                    <option value="">-- Jahr wählen--</option>
                    <option v-for="item in initYearOptions" :key="item" :value="item" v-html="item"></option>
                </select>
            </div>
        </div>
        <!--div class="row p-3">
            <div class="col-3">
                <div v-if="initYearOptions" class="years">
                    <label for="year" class="form-label">Jahr</label>
                    <select id="year" class="form-control" v-model="year" @change="selectYear">
                        <option value="">-- Jahr wählen--</option>
                        <option v-for="item in initYearOptions" :key="item" :value="item" v-html="item"></option>
                    </select>
                </div>
                <div v-if="trackFeatures.length > 0" class="tracks mt-3">
                    <h5>Tracks ({{ trackFeatures.length }})</h5>
                    <div class="form-check" v-for="(item, idx) in trackFeatures" :key="idx">
                        <input type="checkbox" class="form-check-input" v-model="trackItems" :value="item.properties.id"
                               :id="item.properties.name" @change="selectTrack"/>
                        <label class="form-check-label" :for="item.properties.name"
                               v-html="item.properties.name"></label>
                    </div>
                </div>
                <div v-else class="mt-3"><h6>Lade Tracks ...</h6></div>
            </div>
            <div class="col-9">
                <div ref="map" id="map"></div>
            </div>
        </div-->
        <div ref="map" id="map"></div>
    </div>
</template>

<script>
const $ = require('jquery')
import {ref} from 'vue';
import * as L from 'leaflet';
import { MarkerClusterGroup } from 'leaflet.markercluster';
import "leaflet.nauticscale/dist/leaflet.nauticscale";
import "leaflet/dist/leaflet.css"
import "leaflet.markercluster/dist/MarkerCluster.css"
import "leaflet.markercluster/dist/MarkerCluster.Default.css"
import {VueSpinnerHourglass} from 'vue3-spinners';
import {isMobile} from '@basitcodeenv/vue3-device-detect'
import moment from 'moment';

moment.locale('de');
moment.locale('de_DE');

const apiURL = '/api/tracks',
    mediaWidth = '600',
    startTime = moment();
;

export default {
    name: "TracksOrig",
    components: {VueSpinnerHourglass, MarkerClusterGroup, isMobile},
    props: ['northEast', 'southWest', 'km', 'nm', 'duration', 'maxZoom', 'yearOptions', 'center','initZoom'],
    data() {
        return {
            loading: true,
            zoom: this.initZoom,
            curBounds: null,
            curSouthWest: null,
            curNorthEast: null,
            hasData: false,
            wrapper: {},
            trackFeatures: ref([]),
            pointFeatures: ref([]),
            mediaFeatures: ref([]),
            attributeControl: new L.control.attribution({position: 'topright', prefix: false}),
            mediaPopup: L.popup({minWidth: isMobile ? '100%' : 640, keepInView: true}),
            markers: new MarkerClusterGroup(),
            year: moment().year(),
            trackItems: ref([]),
/*
            trackStyle: {
                weight: 5,
                opacity: 1,
                color: 'red',
                dashArray: '3',
            },
*/
            mediaStyle: {},
            highlightId: ref(null),
        }
    },
    computed: {
        initSouthWest() {
            return JSON.parse(this.southWest)
        },
        initNorthEast() {
            return JSON.parse(this.northEast)
        },
        initYearOptions() {
            return JSON.parse(this.yearOptions) ?? null
        },
        initCenter() {
            return JSON.parse(this.center)
        }
    },
    mounted() {
        this.initMap()
    },
    beforeUnmount() {
        // Speicherlecks vermeiden beim Zerstören der Komponente
        if (this.map) {
            this.map.remove();
            this.map = null;
            this.tracks = null;
            this.points = null;
            this.media = null;
            this.trackItems = null;
            this.trackFeatures = null;
            this.pointFeatures = null;
            this.mediaFeatures = null;
        }
    },
    watch: {
        trackFeatures: (old, newVal) => newVal,
        highlightId: {
            handler(newVal) {
                this.updateTracks(newVal);
            },
            deep: true // Erkennt auch Änderungen tief im GeoJSON-Objekt
        },
        tracks: {
            handler(newVal) {
                this.updateTracks(newVal);
            },
            deep: true // Erkennt auch Änderungen tief im GeoJSON-Objekt
        },
        points: {
            handler(newVal) {
                this.updatePoints(newVal);
            },
            deep: true // Erkennt auch Änderungen tief im GeoJSON-Objekt
        },
        media: {
            handler(newVal) {
                this.updateMedia(newVal);
            },
            deep: true // Erkennt auch Änderungen tief im GeoJSON-Objekt
        },
    },
    methods: {
        initMap() {
            // Direkt an 'this' hängen -> bleibt ein reines JS-Objekt ohne Vue-Proxy
            this.map = L.map('map', {
                zoom: this.zoom,
                center: this.initCenter,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);
            L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
                maxZoom: this.maxZoom,
                attribution: '&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>'
            }).addTo(this.map)

            const bounds = L.latLngBounds(this.initNorthEast, this.initSouthWest);
            this.map.fitBounds(bounds);

            L.control.scalenautic({
                imperial: false,
                nautic: true
            }).addTo(this.map);
/*
            // Leeren Layer initialisieren
            this.tracks = L.geoJSON(null, {
                style: this.trackStyle
            }).addTo(this.map);

            this.points = L.geoJSON(null, {
                style: this.getPointFeatureStyle,
                pointToLayer: this.createPointLayer,
                onEachFeature: this.handleEachPointFeature
            }).addTo(this.map);

            this.media = L.geoJSON(null, {
                style: this.mediaStyle
            }).addTo(this.map);
*/
            this.setInfo(info)

            let params = {
                southWest: {lat: this.initSouthWest[0], lng: this.initSouthWest[1]},
                northEast: {lat: this.initNorthEast[0], lng: this.initNorthEast[1]},
                zoom: this.map.getZoom(),
                year: this.year,
            };

            this.fetch(params);
        },
        updateTracks(data) {
            if (!this.tracks || !data) return;
            this.tracks = L.geoJSON(null, {
                style: this.getFeatureStyle,
                onEachFeature: this.handleEachTrackFeature
            }).addTo(this.map);

            // Alte Elemente entfernen
            this.tracks.clearLayers();

            // Neue Daten hinzufügen
            if (data && Object.keys(data).length > 0) {
                this.tracks.addData(data);

                // Optional: Kartenausschnitt automatisch auf die neuen Daten zoomen
                const bounds = this.tracks.getBounds();

                if (bounds.isValid()) {
                    this.map.fitBounds(bounds);
                }
            }
        },
        updatePoints(data) {
            if (!this.points || !data) return;
            this.points = L.geoJSON(null, {
                style: this.getPointFeatureStyle,
                pointToLayer: this.createPointLayer,
//                onEachFeature: this.handleEachPointFeature
            }).addTo(this.map);

            // Alte Elemente entfernen
            this.points.clearLayers();

            // Neue Daten hinzufügen
            if (data && Object.keys(data).length > 0) {
                this.points.addData(data);
            }
        },
        updateMedia(data) {
            if (!this.media || !data) return;

            // Alte Elemente entfernen
            this.media.clearLayers();

            // Neue Daten hinzufügen
            if (data && Object.keys(data).length > 0) {
                this.media.addData(data);
            }
        },
        async fetch(params) {
            const resp = await axios.post(apiURL, params).catch(err => console.error(err));

            if (resp.data && !resp.data.error) {
                this.loading = false;
                this.hasData = resp.data.tracks.features.length > 0;
                this.countPoints = resp.data.points.features.length;
                this.curZoom = resp.data.zoom;
                this.curSouthWest = resp.data.bounds[0];
                this.curNorthEast = resp.data.bounds[1];
                this.curBounds = resp.data.bounds;
                this.trackFeatures = resp.data.tracks.features;
                this.pointFeatures = resp.data.points.features;
                this.mediaFeatures = resp.data.media.features;

                // Leeren Layer initialisieren
                this.tracks = L.geoJSON(null, {
                    style: this.trackStyle,
                    onEachFeature: this.handleEachTrackFeature
                }).addTo(this.map);
/*
                this.points = L.geoJSON(null, {
                    style: this.getPointFeatureStyle,
                    pointToLayer: this.createPointLayer, // Ersetzt Standard-Marker
//                    onEachFeature: this.handleEachPointFeature
                }).addTo(this.map);
*/
                this.media = L.geoJSON(null, {
                    style: this.mediaStyle
                }).addTo(this.map);

                const tracks = resp.data.tracks,
                    points = resp.data.points,
                    media = resp.data.media;

                // Falls beim Laden schon Daten übergeben wurden, direkt einfügen
                if (tracks) {
                    this.updateTracks(tracks);
                    this.trackItems = tracks.features.map(t => t.properties.id);
                }

                if (points) {
//                    this.updatePoints(points);
                }

                if (media) {
                    this.updateMedia(media);
                }

                const bounds = L.latLngBounds(this.curNorthEast, this.curSouthWest);

                this.map.fitBounds(bounds);
                this.handleMedia()

                const time = (moment().diff(startTime) / 1000).toFixed(1);
                const info = {
                    points: this.countPoints,
                    km: this.km + ' km',
                    nm: this.nm + ' nm',
                    zoom: this.map.getZoom(),
                    time: time + ' sec',
                }
                this.setInfo(info)
            }
        },
        handleMedia() {
            if (this.media && this.mediaFeatures.length > 0) {

                this.mediaFeatures.forEach(m => {
                    const marker = L.marker(m.geometry.coordinates);
                    this.markers.addLayer(marker);

                    marker.on('click', () => {
                        const content = this.contentDiv(m);
                        this.mediaPopup
                            .setLatLng(m.geometry.coordinates)
                            .setContent(content);

                        this.mediaPopup.options.className = m.properties.type;
                        this.mediaPopup.openOn(this.map)
                    })
                });

                this.map.on('popupclose', (e) => {
                    if ('video' === e.popup.options.className) {
                        const video = document.getElementById('map').querySelector('.video video')
                        if (video) {
                            video.pause();
                        }
                    }
                    this.wrapper = null;
                });

                this.map.addLayer(this.markers);
            }
        },
        handleEachTrackFeature(feature, layer) {
/*
            layer.on('click', (e) => {
                this.highlightId = feature.properties.id;
//                this.tracks.setStyle(this.getTrackFeatureStyle(feature))
                console.info('feature', feature)
            });
*/
        },
        handleEachPointFeature(feature, layer) {
            // 1. Popup aus den GeoJSON-Properties bauen
            const name = feature.properties.track.name,
                dateTime = moment(feature.properties.datetime).format("dddd DD.MM.YYYY HH:mm"),
                start = feature.properties.track.start,
                end = feature.properties.track.end,
                km = feature.properties.track.km,
                nm = feature.properties.track.nm,
                speed = this.handleDistance(feature.geometry.coordinates);

            layer.bindPopup(`
                    <strong>${dateTime}</strong><br>
                    ${name}<br>
                    Start ${start} Ende ${end}<br>
                    Speed: ${speed} kn<br>
                    Turnlänge insgesamt:<br>
                    ${km} km, ${nm} nm
                `);

            // 2. Klick-Event abfangen und an Vue weiterleiten
            layer.on('click', (e) => {
                // Verhindert, dass das Klick-Event zusätzlich auf die Karte darunter durchschlägt
                L.DomEvent.stopPropagation(e);
                // Sendet das angeklickte Feature an die Eltern-Komponente
                this.$emit('point-click', feature);
            });
        },
        handleDistance(position) {
            var nearest = null,
                minDistance = Infinity,
                prevDateTime = null,
                prevLatLng = null,
                speed = 0;

            this.pointFeatures.forEach((ftr, idx) => {
                if (idx > 0) {
                    const prev = this.pointFeatures[idx - 1];
                    prevDateTime = moment(prev.properties.datetime);
                    prevLatLng = L.latLng(
                        prev.geometry.coordinates[1],
                        prev.geometry.coordinates[0]
                    );
                }

                const latlng = L.latLng(
                        ftr.geometry.coordinates[1],
                        ftr.geometry.coordinates[0]
                    ),
                    dateTime = moment(ftr.properties.datetime),
                    distance = L.latLng(position).distanceTo(latlng);

                if (prevDateTime && prevLatLng) {
                    const myDistance = prevLatLng.distanceTo(latlng),
                        duration = moment.duration(dateTime.diff(prevDateTime)),
                        seconds = duration.asSeconds();

                    speed = this.getSpeed(myDistance, seconds);
                }

                if (distance < minDistance) {
                    minDistance = distance;
                    nearest = ftr;
                }
            });

            return speed;
        },
        getSpeed(meters, seconds) {
            return (meters / seconds * 1.944).toFixed(1);
        },
        contentDiv(media) {
            var filename = media.properties.filename,
                title = media.properties.name,
                type = media.properties.type,
                pos = media.geometry.coordinates,
                id = 'v' + media.properties.id,
                src,
                $media,
                $h = $('<h6>'),
                $span = $('<span class="float-end pos">');

            this.wrapper = $('<div>');

            this.wrapper.css({
                width: mediaWidth + 'px',
                margin: 0,
                padding: 0,
            });
            $h.text(title)
            $span.text(pos[0] + " N " + pos[1] + " E").appendTo($h)

            switch (type) {
                case 'image':
                    src = '/storage/media/images/' + filename;
                    $media = $('<img>'),
                        $media.attr({
                            src: src,
                            alt: title,
                            width: mediaWidth,
                        });
                    break;
                case 'video':
                    src = '/storage/media/videos/' + filename;
                    var $media = $('<video>'), $source = $('<source>');
                    $media.attr({
                        id: id,
                        class: 'video',
                        with: mediaWidth,
                        controls: true,
                        autoplay: true
                    });
                    $source.attr({
                        type: 'video/mp4',
                        src: src,
                    })
                    $source.appendTo($media)
//                    this.currentVideo = $media;
                    break;
            }

            $h.appendTo(this.wrapper);
            $media.appendTo(this.wrapper);

            return this.wrapper.html();
        },
        setInfo(info) {
            var $divInfo = $('#info'),
                $body = $('body'),
                label, val;

            $divInfo.empty();

            Object.keys(info).forEach(key => {
                var row = $('<div class="row">'),
                    col = $('<div class="col-6 text-nowrap text-start align-content-start">');

                label = key[0].toUpperCase() + key.slice(1);
                val = info[key];
                col = col.clone()
                col.text(label + ':')
                col.appendTo(row)
                col = col.clone()
                col.text(val)
                col.appendTo(row)
                row = row.clone()
                row.appendTo($divInfo)
            });

            $divInfo.appendTo($body).show()
        },
        selectYear() {
            if(this.initYearOptions.length > 1) {
                console.info('year', this.year)
                let params = {
                    southWest: {lat: this.initSouthWest[0], lng: this.initSouthWest[1]},
                    northEast: {lat: this.initNorthEast[0], lng: this.initNorthEast[1]},
                    zoom: this.map.getZoom(),
                    year: this.year,
                };
                this.fetch(params);

            }
        },
        selectTrack() {
            if (this.trackItems.length > 0) {
                console.info('tracks', this.tracks)
                const tracks = this.trackFeatures.filter(f => this.trackItems.includes(f.properties.id)),
                    points = this.pointFeatures.filter(f => this.trackItems.includes(f.properties.track_id));

                this.updateTracks(tracks)
                this.updatePoints(points)
            }
        },
        createPointLayer(feature, latlng) {
            return L.circleMarker(latlng, {
                radius: 3,         // 3px Radius = 6px Durchmesser
                opacity: 0,        // Rahmen unsichtbar
                fillOpacity: 0,    // Füllung unsichtbar
                interactive: true  // Stellt sicher, dass Klicks & Popups trotzdem funktionieren
            });
        },
        getTrackFeatureStyle(feature) {
            // Styling für deine Polylines
            const isHighlighted = this.highlightId && feature.properties.id === this.highlightId;
            console.info('isHighlighted', isHighlighted)
            return {
                color: isHighlighted ? 'red' : 'blue',
//                weight: isHighlighted ? 5 : 5,
                weight: 5,
//                fillOpacity: 1,
                opacity: 1,
                dashArray: '3',
            };
        },
        getPointFeatureStyle(feature) {
            // Wichtig: Punkte unsichtbar lassen, falls setStyle() aufgerufen wird
            if (feature.geometry && feature.geometry.type === 'Point') {
                return {
                    opacity: 0,
                    fillOpacity: 0
                };
            }

            // Styling für deine Polylines
            const isHighlighted = feature.id === this.highlightId;
            return {
                color: isHighlighted ? '#ff0000' : '#3388ff',
                weight: isHighlighted ? 4 : 2,
                fillOpacity: 0.3
            };
        }
    }
}
</script>

<style scoped lang="scss">
#map {
    width: 100%;
    min-width: 1000px;
    height: 100vh;
    z-index: 1;
}

#loader {
    position: absolute;
    width: 100%;
    height: 100vh;
    z-index: 1000;

    h3 {
        margin: auto;
        color: red;
        font-size: 25px;
    }
}

marker-cluster-small {
    background-color: rgba(255, 226, 140, 0.6);
}

.marker-cluster-small div {
    background-color: rgba(255, 0, 0, 0.8);
    color: #fff;
}

#info {
    position: absolute;
    top: 10px;
    right: 10px;
    width: auto;
    height: auto;
    min-width: 150px;
    max-width: 200px;
    max-height: 200px;
    border: 1px solid #bbb;
    background-color: #fff;
    color: #666;
    z-index: 1000;
    padding: 5px;
    font-size: 0.9rem;
    display: none;
    opacity: 0.7;
}
#year {
    position: absolute;
    top: 3px;
    left: 40px;
    width: auto;
    height: 30px;
    padding: 10px;
    background-color: transparent;
    z-index: 1000;
    select {
        background-color: white;
    }
}
.tracks {
    overflow-y: auto;

    .form-check {
        .form-check-label {
            font-size: 0.8rem;
        }
    }
}
</style>
