<template>
    <div>
        <div id="loader" v-if="loading" class="text-center p-5 d-flex justify-content-between">
            <h3>Lade Daten <VueSpinnerHourglass color="red" size="50" margin="auto" /></h3>
        </div>
        <div id="info"></div>
        <div class="row p-3">
            <div class="col-3 control">
                <div v-if="initYearOptions" class="years">
                    <label for="year" class="form-label">Jahr</label>
                    <select id="year" class="form-control" v-model="year" @change="selectYear">
                        <option value="">-- Jahr wählen--</option>
                        <option v-for="item in initYearOptions" :key="item" :value="item" v-html="item"></option>
                    </select>
                </div>
                <div v-if="tracks" class="tracks mt-3">
                    <h5>Tracks</h5>
                    <div class="form-check" v-for="(item, idx) in trackFeatures" :key="idx">
                        <input type="checkbox" class="form-check-input" v-model="trackItems" :value="item.properties.id" :id="item.properties.name" @change="selectTrack" />
                        <label class="form-check-label" :for="item.properties.name" v-html="item.properties.name"></label>
                    </div>
                </div>
            </div>
            <div class="col" id="map">
                <l-map ref="map" :zoom="zoom" :center="initCenter">
                    <!--l-geo-json :geojson="geojsonTracks" :options="geojsonTracksOptions" /-->
                    <l-tile-layer
                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                        layer-type="base"
                        name="OpenStreetMap"
                        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    ></l-tile-layer>
                    <l-tile-layer
                        url="https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png"
                        layer-type="base"
                        name="OpenSeaMap"
                        attribution='&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>'
                    ></l-tile-layer>
                    <l-geo-json
                        :geojson="trackFeatures"
                        :options="trackOptions"
                        :options-style="geojsonTrackStyler"
                    />
                    <!--l-geo-json
                        :geojson="points"
                        :options="pointOptions"
                        :options-style="geojsonPointStyler"
                        v-model="points"
                    /-->
                    <!--l-geo-json
                        :geojson="mediaFeatures"
                        :options="mediaOptions"
                        :options-style="geojsonMediaStyler"
                        v-model="mediaFeatures"
                    /-->
                    <l-control-scale position="bottomleft" :metric="true" :imperial="true" />
                </l-map>
            </div>
        </div>
    </div>
</template>

<script>
const $ = require('jquery')
import { ref } from 'vue';
import * as L from 'leaflet';
import { MarkerClusterGroup } from 'leaflet.markercluster';
import "leaflet/dist/leaflet.css"
import "leaflet.markercluster/dist/MarkerCluster.css"
import "leaflet.markercluster/dist/MarkerCluster.Default.css"
import { LMap, LGeoJson, LMarker, LFeatureGroup, LPopup, LPolyline, LTileLayer, LControlScale} from "@vue-leaflet/vue-leaflet";
import { VueSpinnerHourglass } from 'vue3-spinners';
import { isMobile } from '@basitcodeenv/vue3-device-detect'

var apiURL = '/api/tracks',
    mediaWidth = '600';

export default {
    name: "Tracks",
    components: {
        VueSpinnerHourglass,
        MarkerClusterGroup,
        isMobile,
        LMap,
        LGeoJson,
        LMarker,
        LFeatureGroup,
        LPopup,
        LPolyline,
        LTileLayer,
        LControlScale
    },
    props: ['northEast','southWest','km','nm','duration','maxZoom','yearOptions','center'],
    data() {
        return {
            loading: true,
            zoom: 8,
            bounds: null,
            curBounds: null,
            curSouthWest: null,
            curNorthEast: null,
            tracks: ref(null),
            points: ref(null),
            media: ref(null),
            trackFeatures: ref([]),
            trackItems: ref([]),
//            pointFeatures: ref([]),
//            mediaFeatures: ref([]),
            hasData: false,
            wrapper: {},
            countPoints: 0,
            attributeControl: new L.control.attribution({ position: 'topright', prefix: false }),
            mediaPopup: L.popup({minWidth: isMobile ? '100%' : 640, keepInView: true}),
            markers: new MarkerClusterGroup(),
            rectangle: null,
            year: null,
            lineStringLayer: ref(null),
            pointLatlngs: ref(null),
            pointLayer: ref(null),
            info: {
                points: 0,
                km: 0,
                nm: 0,
                zoom: 0,
                time: 0,
            },
            trackOptions: {
                color: "red",
            },
            pointOptions: {
                weight: 6,
                opacity: 0,
                fillOpacity: 0,
                visible: false,
            },
            geojsonPointStyler: (feature) => ({
                weight: 6,
                opacity: 0,
                fillOpacity: 0,
                visible: false,
            }),
            geojsonTrackStyler: (feature) => ({
                weight: 2,
            }),
            geojsonMediaStyler: (feature) => ({
            }),
            mediaOptions: {
            }
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
        this.init()
    },
    watch: {
        trackFeatures: (old,newVal) => newVal,
        trackItems: (old,newVal) => newVal
//        points: (old,newVal) => newVal,
//        media: (old,newVal) => newVal,
    },
    methods: {
        async fetch(params) {
            const resp = await axios.post(apiURL, params).catch(err => console.error(err));

            if(resp.data && !resp.data.error) {
                if(resp.data && !resp.data.error) {
                    this.tracks = resp.data.tracks;
                    this.points = resp.data.points;
                    this.media = resp.data.media;

                    this.trackFeatures = this.tracks.features;
                    this.trackItems = this.trackFeatures.map( t => t.properties.id );
                    console.info('trackFeatures', this.trackFeatures.length)
                    console.info('trackItems', this.trackItems)

//                    this.pointFeatures = this.points.features;
//                    this.mediaFeatures = this.media.features;

                    this.hasData = this.tracks.features.length > 0;
                    this.loading = false;
                    this.countPoints = this.points.features.length;
                    this.curSouthWest = resp.data.bounds[0];
                    this.curNorthEast = resp.data.bounds[1];
                    this.curBounds = resp.data.bounds;

//                    console.info('pointFeatures', this.points.features.length)
//                    console.info('mediaFeatures', this.media.features.length)

//                    this.bounds = L.latLngBounds(this.curNorthEast, this.curSouthWest);

                    this.info = {
                        points: this.countPoints,
                        km: this.km + ' km',
                        nm: this.nm + ' nm',
                        zoom: this.zoom,
                        time: this.duration + ' sec',
                    }
                    this.setInfo(this.info)

//                    this.map.fitBounds(this.bounds);
//                    this.handleMedia()
//                    this.handleTracks()
                }
            }
        },
        init() {
            this.info = $('#info');
            let params = {
                southWest: {lat: this.initSouthWest[0], lng: this.initSouthWest[1]},
                northEast: {lat: this.initNorthEast[0], lng: this.initNorthEast[1]},
                zoom: this.zoom,
            };
            this.fetch(params);
        },
        handleMedia() {
            if(this.media && this.media.features.length > 0) {

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
                    if('video' === e.popup.options.className) {
                        const video = document.getElementById('map').querySelector('.video video')
                        if(video) {
                            video.pause();
                        }
                    }
                    this.wrapper = null;
                });

                this.map.addLayer(this.markers);
            }
        },
        handleTracks() {
            if(this.tracks && this.points) {
                this.lineStringLayer = L.geoJSON(this.tracks, {
                    style: this.getStyle
                });
                this.pointLatlngs = this.hasData ? this.pointFeatures.map(c => [
                    c.geometry.coordinates[1],   // Latitude
                    c.geometry.coordinates[0]    // Longitude
                ]) : null;
                this.pointLayer = L.polyline(this.pointLatlngs, {
                    weight: 6,
                    opacity: 0,
                    fillOpacity: 0
                });

                this.pointLayer.on("click", (e) => {
                    var nearest = null,
                        minDistance = Infinity,
                        prevDateTime = null,
                        prevLatLng = null,
                        speed = 0;

                    this.pointFeatures.forEach((feature, idx) => {
                        if(idx > 0) {
                            const prev = this.pointFeatures[idx - 1];
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
                            dateTime = moment(feature.properties.datetime),
                            distance = e.latlng.distanceTo(latlng);

                        if(prevDateTime && prevLatLng) {
                            const myDistance = prevLatLng.distanceTo(latlng),
                                duration = moment.duration(dateTime.diff(prevDateTime)),
                                seconds = duration.asSeconds();

                            speed = this.getSpeed(myDistance, seconds);
                        }

                        if (distance < minDistance) {
                            minDistance = distance;
                            feature.properties.speed = speed;
                            nearest = feature;
                        }
                    });

                    const p = nearest.properties;

                    console.info('p', p)

                    L.popup()
                        .setLatLng(e.latlng)
                        .setContent(`<b>${moment(p.datetime).format("dddd DD.MM.YYYY HH:mm")}</b><br>
                            ${p.track.name}<br>
                            Start ${p.track.start} Ende ${p.track.end}<br>
                            Speed: ${p.speed} kn<br>
                            Turnlänge insgesamt:<br>
                            ${p.track.km} km, ${p.track.nm} nm`)
                        .openOn(this.map);
                });

                this.lineStringLayer.addTo(this.map);
                this.pointLayer.addTo(this.map);
            }
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
            $span.text(pos[0] +" N "+ pos[1] + " E").appendTo($h)

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
        getStyle() {
            return {
                weight: 5,
                opacity: 1,
                color: 'red',
                dashArray: '3',
            };
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
            console.info('year', this.year)
        },
        selectTrack() {
/*
            if(this.trackItems.length > 0) {
                this.trackFeatures = this.trackFeatures.filter(f => this.trackItems.includes(f.properties.id))
//                this.points.features = this.points.features.filter(f => this.trackItems.includes(f.properties.track.track_id))
                this.handleTracks();
                console.info('trackItems', this.trackItems.length)
                console.info('trackFeatures', this.trackFeatures.length)
            }
*/
        },
    }
}
</script>

<style scoped lang="scss">
#map {
    width: 100%;
    height: 100vh;
    padding: 0;
    margin: 0;
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
    opacity: 0.5;
}
.control {
    height: 100vh;
    overflow-y: auto;
    .tracks {
        overflow-y: auto;
        .form-check {
            .form-check-label {
                font-size: 0.8rem;
            }
        }
    }
}
</style>
