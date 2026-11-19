<template>
    <div id="sailing-app" class="bg-dark text-light">
        <div v-if="featureCollectionName" class="collection-header">
            <strong>{{ featureCollectionName }}</strong>
            <button id="trackSelector"
                class="btn btn-outline-light"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasTracks"
                aria-controls="offcanvasTracks">Select Track</button>
        </div>

        <div v-if="errorMessage" class="error-banner m-3">
            {{ errorMessage }}
        </div>

        <div class="sailing-dashboard">
            <div v-if="isLoading" id="loader" class="d-flex justify-content-center align-items-center min-vh-100">
                <div class="text-danger">Lade Daten <VueSpinnerHourglass color="red" size="50" margin="auto"/></div>
            </div>
            <aside v-else id="offcanvasTracks" class="sidebar offcanvas offcanvas-start text-bg-dark" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" aria-labelledby="offcanvasScrollingLabel">
                <div class="offcanvas-header">
                    <button type="button" class="btn-close btn-close-white me-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div offcanvas-body>
                    <div class="filter-bar">
                        <label for="year-select"><strong>Segeljahr:</strong></label>
                        <select id="year-select" v-model="selectedYear">
                            <option v-for="year in yearOptions" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <h3>Törns {{ selectedYear }} ({{ yearlyTracks.length }})</h3>
                        <ul class="track-list">
                            <li
                                v-for="{ feature, stats } in yearlyTracks"
                                :key="feature.properties.id"
                                :class="{
                                    active: feature.properties.id === selectedTrackId,
                                    hovered: feature.properties.id === hoveredTrackId
                                }"
                                @click="selectTrack(feature.properties.id)"
                                @mouseenter="hoveredTrackId = feature.properties.id"
                                @mouseleave="hoveredTrackId = null"
                            >
                                <div class="track-title">{{ feature.properties.name }}</div>
                                <div class="track-meta">
                                    {{ stats.nm.toFixed(1) }} sm ({{ stats.km.toFixed(1) }} km) • Ø
                                    {{ stats.avgSpeedKn.toFixed(1) }} kn
                                </div>
                                <div class="track-date">Start {{ (stats.start) }} Uhr</div>
                                <div v-if="selectedTrackId && selectedTrackId.value === feature.properties.id">
                                    <button class="close-btn" @click="unSelectTrack">✕ Schließen</button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
            <div id="map" ref="mapContainer"></div>
        </div>
    </div>
</template>

<script setup>
const $ = require('jquery')
import {ref, computed, watch, onMounted, onUnmounted} from 'vue'
import axios from 'axios'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import "leaflet.markercluster/dist/MarkerCluster.css"
import "leaflet.markercluster/dist/MarkerCluster.Default.css"
import { MarkerClusterGroup } from 'leaflet.markercluster';
import "leaflet.nauticscale/dist/leaflet.nauticscale";
import { VueSpinnerHourglass } from 'vue3-spinners';
import { isMobile } from '@basitcodeenv/vue3-device-detect'

import moment from "moment";

moment.locale('de');
moment.locale('de_DE');

const props = defineProps([
        'initNorthEast',
        'initSouthWest',
        'initCenter',
        'initYearOptions',
        'zoom',
        'maxZoom',
        'km',
        'nm',
    ]),
// API-Konfiguration (passe URL und Payload an dein Backend an)
    API_URL = '/api/tracks',
    zoom = props.zoom,
    maxZoom = props.maxZoom,
    km = props.km,
    nm = props.nm,
    northEast = JSON.parse(props.initNorthEast),
    southWest = JSON.parse(props.initSouthWest),
    center      = JSON.parse(props.initCenter),
    yearOptions = JSON.parse(props.initYearOptions),
    // Reaktive Zustände
    featureCollectionName = ref(''),
    allTrackFeatures = ref([]),
    allMediaFeatures = ref([]),
    isLoading = ref(true),
    errorMessage = ref(null),
    selectedYear = ref(new Date().getFullYear()),
    hoveredTrackId = ref(null),
    mapContainer = ref(null),
    year = ref(moment().year()),
    selectedTrackId = ref(null),
    markers = new MarkerClusterGroup({zIndex: 10}),
    mediaPaneName = 'mediaPane',
    trackPaneName = 'trackPane';


let map = null,
    geoJsonTrackLayer = null,
    geoJsonMediaLayer = null;

// --- Hilfsfunktionen für deutsches Datumsformat ("10.07.2026 14:04") ---

function parseGermanDateTime(dateStr) {
    if (!dateStr) return null
    const [datePart, timePart = '00:00'] = dateStr.trim().split(' ')
    const [day, month, year] = datePart.split('.').map(Number)
    const [hours, minutes] = timePart.split(':').map(Number)

    if (!day || !month || !year) return null
    return new Date(year, month - 1, day, hours || 0, minutes || 0)
}

function getYearFromFeature(feature) {
    const startDate = parseGermanDateTime(feature.properties?.start)
    return startDate ? startDate.getFullYear() : null
}

// Track-Kennzahlen aus deinen properties + berechneter Ø-Geschwindigkeit
function getTrackStats(feature) {
    const props = feature.properties || {},
        km = parseFloat(props.km) || 0,
        nm = parseFloat(props.nm) || 0,
        startDate = parseGermanDateTime(props.start),
        endDate = parseGermanDateTime(props.end);

    let avgSpeedKn = 0,
        durationFormatted = '–';

    if (startDate && endDate && endDate > startDate) {
        const diffMs = endDate.getTime() - startDate.getTime(),
            diffHours = diffMs / (1000 * 60 * 60);

        if (diffHours > 0) {
            avgSpeedKn = nm / diffHours
        }

        const totalMinutes = Math.round(diffMs / (1000 * 60)),
            hrs = Math.floor(totalMinutes / 60),
            mins = totalMinutes % 60;

        durationFormatted = `${hrs} Std. ${mins} Min.`
    }

    return {
        km,
        nm,
        start: props.start || '–',
        end: props.end || '–',
        avgSpeedKn,
        durationFormatted
    }
}

// --- API-Request per Axios-POST ---
async function fetchApi() {
    errorMessage.value = null

    try {
        // Optional: Jahr im Payload mitsenden, falls das Backend danach filtert
        const resp = await axios.post(API_URL, {
            year: selectedYear.value,
            southWest: {lat: southWest[0], lng: southWest[1]},
            northEast: {lat: northEast[0], lng: northEast[1]},
            zoom: map.getZoom(),
        })

        if(resp && resp.data && !resp.error) {
            const tracks = resp.data.tracks,
                media = resp.data.media;

            featureCollectionName.value = tracks.name || ''
            allTrackFeatures.value = Array.isArray(tracks.features) ? tracks.features : []
            allMediaFeatures.value = Array.isArray(media.features) ? media.features : []

            renderGeoJsonTrackLayer(tracks.features)
            renderGeoJsonMediaLayer(media.features)
            isLoading.value = false
        } else {
            errorMessage.value = `Fehler: ${resp.error}`
            console.error(err)
        }
    } catch (err) {
        errorMessage.value = 'Tracks konnten nicht geladen werden.'
        console.error('Axios POST Fehler:', err)
    } finally {
        isLoading.value = false
    }
}

// --- Computed Properties ---

const availableYears = computed(() => {
    return yearOptions
})

const filteredFeatures = computed(() => {
    return allTrackFeatures.value.filter((f) => {
        const y = getYearFromFeature(f)
        // Features ohne Startdatum (z. B. reine Hafen-Points) trotzdem anzeigen
        return y === null || y === selectedYear.value
    })
})

const yearlyTracks = computed(() => {
    return filteredFeatures.value
        .filter((f) => f.geometry?.type === 'LineString')
        .map((feature) => ({
            feature,
            stats: getTrackStats(feature)
        }))
})

var selectedTrackDetails = computed(() => {
    console.info('selectedTrackId', selectedTrackId.value)
    if (selectedTrackId.value === null) return null
    return yearlyTracks.value.find((t) => t.feature.properties.id === selectedTrackId.value) || null
})

// --- Leaflet Karten-Logik ---
function getTrackStyle(feature) {
    const id = feature.properties.id,
        isSelected = id === selectedTrackId.value,
        isHovered = id === hoveredTrackId.value;

    if (isSelected) {
        return { color: '#f00', weight: 6, opacity: 1 }
    }
    if (isHovered) {
        return { color: '#f80', weight: 5, opacity: 0.9 }
    }

    return { color: '#1e3a8a', weight: 5, opacity: 0.9, zIndex: 1000 }
}

function getMediaStyle() {
    return { opacity: 1 }
}

function handleEachTrackFaeture(feature, layer) {
    feature.properties.points.forEach(p => {
        const
//            latlng = p.latlng,
            name = p.name,
            nmSpeed = p.nmSpeed,
            kmSpeed = p.kmSpeed,
            time = p.time;

        const popupHtml = `
        <div style="min-width: 195px; font-family: system-ui, sans-serif;">
            <strong>${name}</strong><br>
            <strong>Zeit: </strong>${time} Uhr<br>
            <strong>Speed: </strong>${kmSpeed} km/h, ${nmSpeed} kn<br>
        </div>`;

        layer.bindPopup(popupHtml)
    })

    layer.on({
        mouseover: () => {
            hoveredTrackId.value = props.id
            layer.bringToFront()
        },
        mouseout: () => {
            if (hoveredTrackId.value === props.id) {
                hoveredTrackId.value = null
            }
        },
    })
}

function handleEachMediaFeature(feature, layer) {
    const props = feature.properties,
        prefix = '/storage/media',
        bounds = isMobile ? map.getBounds() : map.getBounds().pad(-0.25),
        marker = L.marker(feature.geometry.coordinates, { zIndex: 10 });

    var src;

    markers.addLayer(marker)
    marker.on('click', (e) => {
        switch (props.type) {
            case 'image':
                src = prefix + '/images/' + props.filename
                const image = L.imageOverlay(src,
                    bounds,
                    {
                        alt: props.name,
                        className: 'image',
                        zIndex: 1000,
                        interactive: true,
                        pane: mediaPaneName,
                    });
                image.bringToFront();
                image.addTo(map)
                image.on('click', (e) => {
                    image.remove();
                })
                break
            case 'video':
                src = prefix + '/videos/' + props.filename
                const video = L.videoOverlay(src,
                    bounds,
                    {
                        alt: props.name,
                        className: 'video',
                        zIndex: 1000,
                        interactive: true,
                        pane: mediaPaneName,
                        controls: true,
                        loop: false,
                        playsInline: true
                    });
                video.bringToFront();
                video.addTo(map)
                const elem = video.getElement();

                video.on('load', () => {
                    elem.controls = true;
                });

                elem.onended = (event) => {
                    video.remove();
                }

                video.on('click', (e) => {
                    video.remove();
                })
                break
        }
    })
}
function renderGeoJsonTrackLayer(data) {
    if (!map) return
    if (geoJsonTrackLayer) geoJsonTrackLayer.remove()

    const featureCollection = {
        type: 'FeatureCollection',
        features: data
    }

    geoJsonTrackLayer = L.geoJSON(featureCollection, {
        style: getTrackStyle,
        pane: trackPaneName,
        onEachFeature: handleEachTrackFaeture
    }).addTo(map)

    if (filteredFeatures.value.length > 0 && geoJsonTrackLayer.getBounds().isValid()) {
        map.fitBounds(geoJsonTrackLayer.getBounds())
    }
}

/*
function renderGeoJsonPointLayer(data) {
    if (!map) return
    if (geoJsonTrackLayer) geoJsonTrackLayer.remove()

    const featureCollection = {
        type: 'FeatureCollection',
        features: data
    }
    geoJsonPointLayer = L.geoJSON(featureCollection, {
        style: getPointStyle,
        pane: pointPaneName,
        pointToLayer: (feature, latlng) => L.circleMarker(latlng, getPointStyle()),
        onEachFeature: handleEachPointFeature
    }).addTo(map)
}
*/

function renderGeoJsonMediaLayer(data) {
    if (!map) return
    if (geoJsonMediaLayer) geoJsonMediaLayer.remove()

    const featureCollection = {
            type: 'FeatureCollection',
            features: data
        },

    geoJsonMediaLayer = L.geoJSON(featureCollection, {
        style: getMediaStyle,
        onEachFeature: handleEachMediaFeature
    }).addTo(map)
    map.addLayer(markers);
}

function updateLayerStyles() {
    if (!geoJsonTrackLayer) return
    geoJsonTrackLayer.eachLayer((layer) => {
        const feature = layer.feature
        if (feature && feature.geometry.type === 'LineString') {
            layer.setStyle(getTrackStyle(feature))
            if (feature.properties.id === selectedTrackId.value) {
                layer.bringToFront()
            }
        }
    })
}

function selectTrack(id, zoomToTrack = false) {
    selectedTrackId.value = selectedTrackId.value === id ? null : id

    if (zoomToTrack && selectedTrackId.value !== null && geoJsonTrackLayer) {
        const myOffcanvas = document.getElementById('offcanvasTracks');

        if(myOffcanvas) {
            myOffcanvas.addEventListener('hidden.bs.offcanvas', () => {
                unSelectTrack()
            })
        }
        geoJsonTrackLayer.eachLayer((layer) => {
            if (layer.feature?.properties?.id === id) {
                map.fitBounds(layer.getBounds(), { maxZoom: 13 })
                layer.openPopup()
            }
        })
    }
}

function unSelectTrack() {
    selectedTrackId.value = null
    updateLayerStyles()
}

watch(selectedYear, () => {
    selectedTrackId.value = null
    // Wenn dein API-Endpoint pro Jahr abfragt, hier fetchApi() aufrufen;
    // wenn die API immer alle Jahre liefert, reicht renderGeoJsonTrackLayer():
    fetchApi()
    renderGeoJsonTrackLayer()
})

watch([selectedTrackId, hoveredTrackId], () => {
    updateLayerStyles()
})

onMounted(() => {
    map = L.map(mapContainer.value, {
        zoom: zoom,
        maxZoom: maxZoom,
        center: center,
    });

    const bounds = L.latLngBounds(northEast, southWest);
    map.fitBounds(bounds);

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        zIndex: 1
    }).addTo(map)

    const openSeaMapLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>',
        zIndex: 100
    }).addTo(map)

    map.createPane(trackPaneName);
    // Z-Index höher als markerPane (600) setzen, z.B. 650 (aber unter Popups, die bei 700 liegen)
    map.getPane(trackPaneName).style.zIndex = 600;
    map.getPane(trackPaneName).style.pointerEvents = 'auto';

    map.createPane(mediaPaneName);
    // Z-Index höher als markerPane (600) setzen, z.B. 650 (aber unter Popups, die bei 700 liegen)
    map.getPane(mediaPaneName).style.zIndex = 1000;

    L.control.layers(
        {'OpenStreetMap': osmLayer},
        {'Seezeichen (OpenSeaMap)': openSeaMapLayer},
        { position: 'topright' }
    ).addTo(map)

    console.info('yearOptions', yearOptions)

    fetchApi()
})

onUnmounted(() => {
    map?.remove()
})
</script>

<style lang="scss">
#tracks {
    width: 100%;
    height: 100%;
}
#loader {
    position: absolute;
    width: 100%;
    height: 100vh;
    margin: auto;
    z-index: 10000;
    background-color: transparent;
    div {
        font-size: 1.6rem;
        font-weight: bold;
        color: red;
    }
}
#sailing-app {
    display: block;
    width: 100%;
    height: 100%;
    padding: 0;
    font-family: system-ui, sans-serif;
    //    display: flex;
    //    gap: 16px;
//    flex-direction: column;
    .collection-header {
        padding: 10px 10px;
        strong {
            margin-left: 10px;
            font-size: 1.35rem;
            color: white;
        }
        #trackSelector {
            margin-top: -5px;
            margin-left: 20px;
        }
    }
}
#map {
    border: 1px solid green;
    width: 100%;
    height: 90%;
}
.error-banner {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px 14px;
    border-radius: 6px;
    border: 1px solid #f87171;
}
.sailing-dashboard {
    display: flex;
    height: 100vh;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    overflow: hidden;
}
.sidebar {
    width: 320px;
    background: #f8fafc;
    border-right: 1px solid #cbd5e1;
    padding: 16px;
    overflow-y: auto;
}
.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.filter-bar select {
    padding: 6px 10px;
    border-radius: 4px;
    border: 1px solid #94a3b8;
}
.track-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.track-list li {
    padding: 10px 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.track-list li:hover,
.track-list li.hovered {
    border-color: #0284c7;
    background: #f0f9ff;
}
.track-list li.active {
    border-color: #7c3aed;
    background: #f5f3ff;
    box-shadow: 0 0 0 1px #7c3aed;
}
.track-title {
    font-weight: 600;
    font-size: 0.95rem;
    color: #0f172a;
}
.track-meta {
    font-size: 0.82rem;
    color: #334155;
    margin-top: 4px;
}
.track-date {
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 2px;
}
/*
.map-wrapper {
    flex: 1;
}
.map-container {
    height: 100%;
    width: 100%;
}
*/
.track-details-panel {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 16px;
}
.details-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.details-header h4 {
    margin: 0;
    font-size: 1.1rem;
    color: #0f172a;
}
.close-btn {
    background: transparent;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 0.85rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 12px;
}
.stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
}
.stat-label {
    font-size: 0.78rem;
    color: #64748b;
}
.stat-value {
    font-size: 1.05rem;
    font-weight: 600;
    color: #0f172a;
    margin-top: 2px;
}
</style>
