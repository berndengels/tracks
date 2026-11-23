<template>
    <div>
        <div id="sailing-app" class="bg-dark text-light w-full">
            <div v-if="featureCollectionName" class="collection-header row w-full">
                <div class="col-10 float-start">
                    <strong>{{ featureCollectionName }}</strong>
                    <button id="trackSelector"
                            class="btn btn-outline-light"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasTracks"
                            aria-controls="offcanvasTracks">Select Track</button>
                </div>
                <div class="col-2 float-end">
                    <div v-if="duration" class="align-content-end">
                        <span class="text-light align-content-end">Duration: {{ duration }} sec</span>
                    </div>
                </div>
            </div>

            <div v-if="errorMsg" class="error-banner m-3 p-3">
                <span v-html="errorMsg"></span>
            </div>

            <div v-if="errorResponse" class="error-banner m-3">
                <table class="table table-sm">
                    <tr v-for="(val, key) in errorResponse" :key="key">
                        <th v-html="key" />
                        <td v-html="val"/>
                    </tr>
                </table>
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
                                <option v-for="option in yearOptions" :key="option.value" :value="option.value" v-html="option.label" />
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
                                    <div class="track-title">{{ feature.properties.name }} <span>({{ feature.properties.id }})</span></div>
                                    <div class="track-meta">
                                        {{ stats.nm.toFixed(1) }} sm ({{ stats.km.toFixed(1) }} km) • Ø
                                        {{ stats.avgSpeedKn.toFixed(1) }} kn
                                    </div>
                                    <div class="track-date">Start: {{ (stats.start) }} Ende: {{ (stats.end) }}</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </aside>
                <div id="map" ref="mapContainer"></div>
            </div>
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
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';
import "leaflet.measurecontrol";
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
    startTime = moment(),
    zoom = props.zoom,
    maxZoom = props.maxZoom,
    km = props.km,
    nm = props.nm,
    northEast = JSON.parse(props.initNorthEast),
    southWest = JSON.parse(props.initSouthWest),
    center      = JSON.parse(props.initCenter),
    yearOptions = JSON.parse(props.initYearOptions),
    // Reaktive Zustände
    duration = ref(null),
    featureCollectionName = ref(''),
    allTrackFeatures = ref([]),
    allMediaFeatures = ref([]),
    isLoading = ref(true),
    errorMsg = ref(null),
    errorResponse = ref(null),
    selectedYear = ref(new Date().getFullYear()),
    hoveredTrackId = ref(null),
    mapContainer = ref(null),
    year = ref(moment().year()),
    selectedTrackId = ref(null),
    mediaPaneName = 'mediaPane',
    trackPointPaneName = 'trackPointPane',
    trackPaneName = 'trackPane',
    markers = new MarkerClusterGroup({optios: {
        zIndex: 1000,
        clusterPane: mediaPaneName,
    }});

let map = null,
    geoJsonTrackLayer = null,
    geoJsonMediaLayer = null;

// --- API-Request per Axios-POST ---
const fetchApi = async () => {
        errorMsg.value = null

        if(! selectedYear.value) {
            errorMsg.value = 'Bitte ein gültiges Jahr auswählen!';
            return false
        }

        try {
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
                errorMsg.value = `Fehler: ${resp.error}`
                console.error(err)
            }
            const endTime = moment()
            duration.value = endTime.diff(startTime, 'seconds').toLocaleString()
        } catch (err) {
            errorMsg.value = err
            console.error(err)
        } finally {
            isLoading.value = false
        }
    },
    yearlyTracks = computed(() => {
    return allTrackFeatures.value
        .filter((f) => f.geometry?.type === 'LineString')
        .map((feature) => ({
            feature,
            stats: getTrackStats(feature)
        }));
    }),
    getYearFromFeature = (feature) => moment(feature.properties?.start).year(),
    getTrackStats = (feature) => {
        const props = feature.properties || {},
            km = parseFloat(props.km) || 0,
            nm = parseFloat(props.nm) || 0,
            startDate = new Date(props.start),
            endDate = new Date(props.end);

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
            id: props.id,
            km,
            nm,
            start: props.start || '–',
            end: props.end || '–',
            avgSpeedKn,
            durationFormatted
        }
    },
    getTrackStyle = (feature) => {
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
    },
    getMediaStyle = () => {
        return { opacity: 1 }
    },
    handleEachTrackFaeture = (feature, layer) => {
        feature.properties.points.forEach(p => {
            var popupHtml = `
            <div style="min-width: 195px; font-family: system-ui, sans-serif;">
                <strong>${p.name}</strong><br>
                <strong>Zeit: </strong>${p.time} Uhr<br>
                <strong>Speed: </strong>${p.kmSpeed} km/h, ${p.nmSpeed} kn<br>
            </div>`;
            const marker = L.circleMarker(p.latlng, {
                stroke: false,
                radius: 6,
                opacity: 0,
                fillOpacity: 0,
                className: 'track-point',
                bubblingPointerEvents: true,
                pane: trackPointPaneName,
            })
            marker.bindPopup(popupHtml)
            marker.addTo(map)
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
    },
    handleEachMediaFeature = (feature, layer) => {
        const props = feature.properties,
            marker = L.marker(feature.geometry.coordinates, { zIndex: 10 });

        markers.addLayer(marker)
        marker.on('click', () => {
            showMedia(props)
        })
    },
    renderGeoJsonTrackLayer = (data) => {
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

        if (data.length > 0 && geoJsonTrackLayer.getBounds().isValid()) {
            map.fitBounds(geoJsonTrackLayer.getBounds())
        }
    },
    renderGeoJsonMediaLayer = (data) => {
        if (!map) return
        if (geoJsonMediaLayer) geoJsonMediaLayer.remove()

        const featureCollection = {
            type: 'FeatureCollection',
            features: data
        }

        geoJsonMediaLayer = L.geoJSON(featureCollection, {
            style: getMediaStyle,
            onEachFeature: handleEachMediaFeature
        })

        map.addLayer(markers);
    },
    updateLayerStyles = () => {
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
    },
    selectTrack = (id, zoomToTrack = false, moveToCenter = true) => {
        selectedTrackId.value = selectedTrackId.value === id ? null : id

        const myOffcanvas = document.getElementById('offcanvasTracks');

        if(myOffcanvas) {
            myOffcanvas.addEventListener('hidden.bs.offcanvas', () => {
                if(!isMobile) {
                    unSelectTrack()
                }
                const bounds = L.latLngBounds(northEast, southWest);
                map.fitBounds(bounds);
            })
        }

        if(selectedTrackId.value !== null && geoJsonTrackLayer) {
            if (moveToCenter) {
                const bounds = L.latLngBounds(northEast, southWest);
                map.fitBounds(bounds);
            }

            if (zoomToTrack) {
                geoJsonTrackLayer.eachLayer((layer) => {
                    if (layer.feature?.properties?.id === id) {
                        map.fitBounds(layer.getBounds(), { maxZoom: maxZoom })
                        layer.openPopup()
                    }
                })
            }
        }
    },
    unSelectTrack = () => {
        selectedTrackId.value = null
        updateLayerStyles()
    },
    showMedia = (props) => {
        // Falls bereits ein Modal offen ist, dieses zuerst entfernen
        const existingBackdrop = document.querySelector('.media-modal-backdrop');
        if (existingBackdrop) {
            existingBackdrop.remove();
        }

        // Abgedunkelten Hintergrund über der Karte erstellen
        const backdrop = L.DomUtil.create('div', 'media-modal-backdrop', map.getContainer());

        // Karten-Interaktionen (Zoomen, Ziehen, Klicken) komplett blockieren
        L.DomEvent.disableClickPropagation(backdrop);
        L.DomEvent.disableScrollPropagation(backdrop);

        // Äußeren Rahmen im Backdrop erstellen
        const container = L.DomUtil.create('div', 'media-modal-container', backdrop);

        // Kopfzeile erstellen
        const header = L.DomUtil.create('div', 'media-modal-header', container);

        // Titel als h5 einfügen
        const heading = L.DomUtil.create('h5', '', header);
        heading.textContent = `${props.name} ${moment(props.created).format('DD.MM.YYYY HH:mm')} Uhr` || '';

        // Close-Button in die Kopfzeile einfügen
        const closeBtn = L.DomUtil.create('span', 'media-close-btn', header);
        closeBtn.innerHTML = '<i class="fa-regular fa-circle-xmark"></i>';
    //    closeBtn.innerHTML = '&times;';
        closeBtn.title = 'Schließen (ESC)';

        // Video-Element unterhalb der Kopfzeile erstellen

        var media = {};

        switch (props.type) {
            case 'image':
                media = L.DomUtil.create('img', '', container);
                closeBtn.onclick = () => {
                    container.remove();
                };
                break
            case 'video':
                media = L.DomUtil.create('video', '', container);
                media.controls = true;
                media.autoplay = true;
                media.muted = false; // Oft nötig für Autoplay in modernen Browsern

                closeBtn.onclick = () => {
                    media.pause();
                    container.remove();
                    backdrop.remove();
                };
                media.onended = () => {
                    container.remove();
                    backdrop.remove();
                }
                break
        }
        media.src = props.file;

        // Zentrale Schließen-Funktion inkl. Aufräumen des Keydown-Listeners
        const closeModal = () => {
            if('video' === props.type) {
                media.pause();
            }
            backdrop.remove();
            document.removeEventListener('keydown', onKeyDown);
        };

        // Handler für die Escape-Taste
        const onKeyDown = (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        };

        // Event-Listener registrieren
        closeBtn.onclick = closeModal;
        document.addEventListener('keydown', onKeyDown);

        // Optional: Schließen bei Klick auf den dunklen Hintergrund außerhalb des Videos
        backdrop.onclick = (e) => {
            if (e.target === backdrop) {
                closeModal();
            }
        };
    };

watch(selectedYear, () => {
    selectedTrackId.value = null
    fetchApi()
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

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        zIndex: 1
    }).addTo(map)

    const openSeaMapLayer = L.tileLayer('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.openseamap.org">OpenSeaMap</a>',
        zIndex: 100
    }).addTo(map)

    // Z-Index höher als markerPane (600) setzen, z.B. 650 (aber unter Popups, die bei 700 liegen)
    map.createPane(trackPaneName);
    map.createPane(trackPointPaneName);
    map.createPane(mediaPaneName);
    map.getPane(mediaPaneName).style.zIndex = 10000;
    map.getPane(mediaPaneName).style.pointerEvents = 'auto';

    L.control.layers(
        {'OpenStreetMap': osmLayer},
        {'Seezeichen (OpenSeaMap)': openSeaMapLayer},
        { position: 'topright' }
    ).addTo(map)

    L.Control.measureControl({
        handler: {
            multiDistance: true,
            metric: false,
            imperial: false,
            nauticalMiles: true
        }
    }).addTo(map);

    L.control.scalenautic({
        imperial: false,
        nautic: true
    }).addTo(map);

    map.on('click', (e) => console.info('latlng', e.latlng))

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
#map {
    border: 1px solid green;
    width: 100%;
    height: 95%;
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
.leaflet-control-draw-measure {
    background-image: url("/images/measure-control.svg");
}
#sailing-app {
    display: block;
    width: 100%;
    height: 100%;
    padding: 0;
    font-family: system-ui, sans-serif;
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
.error-banner {
    background: #fee2e2;
    padding: 10px 14px;
    border-radius: 6px;
    border: 1px solid #f87171;
    text-align: center;
    span {
        color: #991b1b;
    }
    z-index: 1000;
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
    span {
        font-weight: normal;
    }
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

/* Der äußere Rahmen des Video-Overlays */
.media-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6); /* Halbtransparentes Schwarz */
    z-index: 1000; /* Über allen Karten-Layern und Controls */
    display: flex;
    align-items: center;
    justify-content: center;
}
.media-modal-container {
    width: 800px; /* Feste Breite */
    max-width: 90%; /* Fallback für sehr kleine Bildschirme */
    background-color: #1e1e1e;
    border: 3px solid #ffffff;
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.7);
    box-sizing: border-box;
    display: flex !important;
    flex-direction: column !important; /* Zwingt Header und Video untereinander */
    overflow: hidden;
    video, img {
        width: 100%;
        height: 100%;
        border-radius: 5px;
        object-fit: cover;
        clear: both;
    }
}
/* Kopfzeile für Titel und Close-Button */
.media-modal-header {
    width: 100%;
    box-sizing: border-box;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background-color: #2c2c2c;
    border-bottom: 1px solid #444;
    h5 {
        margin: 0;
        padding: 0;
        color: #ffffff;
        font-family: sans-serif;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 12px;
    }
}

/* Der Close-Button oben rechts */
.media-close-btn {
    display: inline-block;
    float: right;
    width: 26px;
    height: 26px;
    background-color: transparent;
    color: #fff;
    border: none;
    border-radius: 50%;
    font-size: 18px;
    font-weight: bold;
    line-height: 24px;
    text-align: center;
    cursor: pointer;
    flex-shrink: 0;
    padding: 0;
}
.media-close-btn:hover {
    color: #cc0000;
}
</style>
