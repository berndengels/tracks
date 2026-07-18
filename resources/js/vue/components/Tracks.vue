<template>
    <div>
        <div id="loader" v-if="loading" class="text-center p-5 d-flex justify-content-between">
            <PulseLoader :loading="loading" color="grey" size="50">Bitte warten</PulseLoader>
        </div>
        <div v-else id="map"></div>
        <div id="msg"></div>
    </div>
</template>

<script>
import L from "leaflet"
import PulseLoader from "vue-spinner/src/PulseLoader.vue";

const apiURL = '/api/tracks/data';

export default {
    name: "Tracks",
    components: { PulseLoader },
    props: ['bounds','northEast','southWest'],
    data() {
        return {
            curZoom: null,
            curBounds: null,
            curSouthWest: null,
            curNorthEast: null,
            tracks: null,
            points: null,
            hasData: false,
            loading: false,
        }
    },
    computed: {
        initBounds() {
            return JSON.parse(this.bounds)
        },
        initSouthWest() {
            return JSON.parse(this.southWest)
        },
        initNorthEast() {
            return JSON.parse(this.northEast)
        },
    },
    mounted() {
        this.init()
    },
    methods: {
        init()
        {
            const
                openStreetMapLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
//                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }),
                openSeaMapLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>'
                }),
                bounds = L.latLngBounds(this.initNorthEast, this.initSouthWest),
                center = bounds.getCenter(),
                map = L.map('map', {
                    minZoom: 8,
                    center: [center.lat, center.lng]
                });

            openStreetMapLayer.addTo(map);
            openSeaMapLayer.addTo(map);
            map.fitBounds(this.initBounds);

            let params = {
                southWest: this.initSouthWest,
                northEast: this.initNorthEast,
                zoom: map.getZoom(),
            };

            axios.post(apiURL, params)
                .then(resp => {
                    console.info(resp.data);
                    if(resp.data) {
                        this.tracks = resp.data.tracks;
                        this.points = resp.data.points;
                        this.hasData = this.tracks.features.length > 0;
                        this.loading = false;

                        console.info('tracks', this.tracks.features.length)
                        console.info('points', this.points.features.length)

                        if(this.tracks && this.tracks.length) {
                            const lineStringLayer = L.geoJSON(this.tracks, {
                                    style: this.getStyle
                                }),
                                pointLatlngs = this.hasData ? this.points.features.map(c => [
                                    c.geometry.coordinates[1],   // Latitude
                                    c.geometry.coordinates[0]    // Longitude
                                ]) : null,
                                pointLayer = L.polyline(pointLatlngs, {
                                    weight: 6,
                                    opacity: 0,
                                    fillOpacity: 0
                                }),
                                attributeControl = L.control.attribution({
                                    position: 'topright'
                                }).addAttribution(`Points: ${this.points.features.length}`);

                            pointLayer.on("click", (e) => {
                                let nearest = null,
                                    minDistance = Infinity;

                                this.points.features.forEach(feature => {
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
                                    .setContent(`<b>${p.datetime}</b><br>${p.track.name}<br>Start ${p.track.start} Ende ${p.track.end}<br>Speed: ${p.speed.toString()} kn<br>ID: ${p.id}`)
                                    .openOn(map);
                            });

                            lineStringLayer.addTo(map);
                            pointLayer.addTo(map);
                            attributeControl.addTo(map);
                        }
                    }
                }).catch(err => console.error(err));

            map.on('zoomend', () => {
                this.loading = true;
                this.curZoom = map.getZoom();
                this.curBounds = map.getBounds();
                this.currentSouthWest = map.getBounds().getSouthWest();
                this.currentNorthEast = map.getBounds().getNorthEast();

                let params = {
                    southWest: this.currentSouthWest,
                    northEast: this.currentNorthEast,
                    zoom: this.curZoom,
                };

                axios.post(apiURL, params)
                    .then(resp => {
                        console.info(resp.data);
                        this.loading = false;
                    }).catch(err => console.error(err));
            })
        },
        getStyle(feature) {
            return {
                weight: 5,
                opacity: 1,
                color: 'red',
                dashArray: '3',
            };
        },

    }
}
</script>

<style scoped>
</style>
