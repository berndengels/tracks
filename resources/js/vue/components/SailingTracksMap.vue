<template>
	<div>
		<div id="sailing-app" class="bg-dark text-light w-full">
			<div v-if="featureCollectionName" class="collection-header row w-full">
				<div class="col-9 float-start ms-0">
					<div class="d-inline-block">
						<select id="modus" v-model="selectedModus" class="form-select form-floating ms-3">
							<option v-for="item in modusOptions" :key="item.val" v-html="item.label" :value="item.val"/>
						</select>
					</div>
					<div class="d-inline-block">
						<strong>{{ featureCollectionName }}</strong>
						<button id="trackSelector"
						        class="btn btn-outline-light"
						        type="button"
						        data-bs-toggle="offcanvas"
						        data-bs-target="#offcanvasTracks"
						        aria-controls="offcanvasTracks">Wähle {{ selectedModus }}
						</button>
					</div>
				</div>
				<div class="col-3 float-end">
					<div class="stats-bar">
					</div>
					<div v-if="duration" class="text-end me-3">
                        <span class="text-light align-middle fa-align-right ms-3 fs-6">Duration: {{
		                        duration
	                        }} sec</span>
					</div>
				</div>
			</div>

			<div v-if="errorMsg" class="error-banner m-3 p-3">
				<span v-html="errorMsg"></span>
			</div>

			<div v-if="errorResponse" class="error-banner justify-content-center m-3">
				<table class="table table-sm align-self-center">
					<tr v-for="(val, key) in errorResponse" :key="key">
						<th v-html="key"/>
						<td v-html="val"/>
					</tr>
				</table>
			</div>

			<div class="sailing-dashboard">
				<div v-if="isLoading" id="loader" class="d-flex justify-content-center align-items-center min-vh-100">
					<div class="text-danger">Lade Daten
						<VueSpinnerHourglass color="red" size="50" margin="auto"/>
					</div>
				</div>
				<aside v-else id="offcanvasTracks" ref="offcanvas" class="sidebar offcanvas offcanvas-start show text-bg-dark"
				       data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1"
				       aria-labelledby="offcanvasScrollingLabel">
					<div class="offcanvas-header">
						<button type="button" class="btn-close btn-close-white me-0" data-bs-dismiss="offcanvas"
						        aria-label="Close"></button>
					</div>
					<div offcanvas-body>
						<div class="filter-bar">
							<label for="year-select"><strong>Segeljahr:</strong></label>
							<select id="year-select" v-model="selectedYear">
								<option v-for="option in yearOptions" :key="option.value" :value="option.value" v-html="option.label"/>
							</select>
						</div>

						<div v-if="'Track' === selectedModus">
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
									<div class="track-title">{{ feature.properties.name }}
										<span>({{ feature.properties.id }})</span></div>
									<div class="track-meta">
										{{ stats.nm.toFixed(1) }} sm ({{ stats.km.toFixed(1) }} km) • Ø
										{{ stats.avgSpeedKn.toFixed(1) }} kn
									</div>
									<div class="track-date">Start: {{ (stats.start) }} Ende: {{ (stats.end) }}</div>
								</li>
							</ul>
						</div>

						<div v-if="'Planung' === selectedModus">
							<h3>Planung {{ selectedYear }} ({{ allPlannerFeatures.length }})</h3>
							<ul class="track-list">
								<li
									v-for="feature in yearlyPlanners"
									:key="feature.properties.id"
									:class="{
                                        active: feature.properties.id === selectedPlannerId,
                                        hovered: feature.properties.id === selectedPlannerId
                                    }"
									@click="toggleGeoJsonPlannerLayer(feature)"
									@mouseenter="hoveredPlannerId = feature.properties.id"
									@mouseleave="hoveredPlannerId = null"
								>
									<div v-if="feature.properties.points" class="track-title">{{ feature.properties.name }}
										<span>(Points: {{ feature.properties.points.length }})</span></div>
									<div class="track-meta" v-html="feature.properties.desc"></div>
								</li>
							</ul>
						</div>
					</div>
				</aside>
				<TourCalculationPanel v-if="currentTourGeoJson" :geojson="currentTourGeoJson"/>
				<div id="map" ref="mapContainer"></div>
			</div>
		</div>
	</div>
</template>

<script setup>
alert('OK')

const $ = require('jquery')
import {ref, computed, watch, onMounted, onUnmounted, useTemplateRef, toRaw} from 'vue'
import axios from 'axios'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import "leaflet.markercluster/dist/MarkerCluster.css"
import "leaflet.markercluster/dist/MarkerCluster.Default.css"
import {MarkerClusterGroup} from 'leaflet.markercluster';
import "leaflet.nauticscale/dist/leaflet.nauticscale";
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';
import "leaflet.measurecontrol";
import {VueSpinnerHourglass} from 'vue3-spinners';
import {isMobile} from '@basitcodeenv/vue3-device-detect'
import moment from "moment";
import TourCalculationPanel from "c@/TourCalculationPanel.vue";

moment.locale('de');
moment.locale('de_DE');

const props = defineProps([
		'northEast',
		'southWest',
		'center',
		'yearOptions',
		'zoom',
		'maxZoom',
		'km',
		'nm',
	]),
// API-Konfiguration (passe URL und Payload an dein Backend an)
	API_URL = '/api/tracks',
	startTime = moment(),
	modusOptions = [
		{val: null, label: '-- Modus --'},
		{val: 'Track', label: 'Touren'},
		{val: 'Planung', label: 'Planung'}
	],
	// Reaktive Zustände
	duration = ref(null),
	featureCollectionName = ref(''),
	allTrackFeatures = ref([]),
	allMediaFeatures = ref([]),
	allPlannerFeatures = ref([]),
	isLoading = ref(true),
	totalDistanceNM = ref('0.00'),
	modus = ref(null),
	offcanvas = ref(),
	currentTourGeoJson = ref(null),
	mapData = ref(null),
	errorMsg = ref(null),
	errorResponse = ref(null),
	selectedYear = ref(new Date().getFullYear()),
	selectedModus = ref('Planung'),
	hoveredTrackId = ref(null),
	hoveredPlannerId = ref(null),
	mapContainer = ref(null),
	geoJsonOutput = ref(null),
	segmentCount = ref(0),
	averageSpeedKnots = ref(5.0),
	year = ref(moment().year()),
	selectedTrackId = ref(null),
	selectedPlannerId = ref(null),
	allWaypoints = ref([]),
	routeData = ref(null),
	mediaPaneName = 'mediaPane',
	trackPointPaneName = 'trackPointPane',
	trackPaneName = 'trackPane',
	markers = new MarkerClusterGroup({
		optios: {
			zIndex: 1000,
			clusterPane: mediaPaneName,
		}
	});

let map = null,
	geoJsonTrackLayer = null,
	geoJsonPlannerLayer = null,
	geoJsonMediaLayer = null,
	segments = [],
	waypointMarkers = [],
	isAutoHealing = false,
	layerGroup = null;

// --- API-Request per Axios-POST ---
const fetchApi = async () => {
		errorMsg.value = null

		if (!selectedYear.value) {
			errorMsg.value = 'Bitte ein gültiges Jahr auswählen!';
			return false
		}

		try {
			const resp = await axios.post(API_URL, {
				year: selectedYear.value,
				southWest: {lat: props.southWest[0], lng: props.southWest[1]},
				northEast: {lat: props.northEast[0], lng: props.northEast[1]},
				zoom: map.getZoom(),
			})

			if (resp && resp.data) {
				if (resp.data.error) {
					errorResponse.value = resp.data.error
					console.error(resp.data.error)
				} else {
					mapData.value = {
						media: resp.data.media,
						tracks: resp.data.tracks,
						planners: resp.data.planners
					}

					setDataByModus()
					isLoading.value = false
				}
			} else {
				errorMsg.value = 'Fehler: keine Daten erhalten!'
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
	yearlyTracks = computed(() => allTrackFeatures.value
		.filter((f) => f.geometry?.type === 'LineString')
		.map((feature) => ({
			feature,
			stats: getTrackStats(feature)
		}))),
	yearlyPlanners = computed(() => allPlannerFeatures.value.filter((f) => f.geometry?.type === 'LineString')),
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
			return {color: '#f00', weight: 6, opacity: 1}
		}
		if (isHovered) {
			return {color: '#f80', weight: 5, opacity: 0.9}
		}

		return {color: '#1e3a8a', weight: 5, opacity: 0.9, zIndex: 1000}
	},
	getPlannerStyle = (feature) => {
		const id = feature.properties.id,
			isSelected = id === selectedPlannerId.value,
			isHovered = id === hoveredPlannerId.value;

		if (isSelected) {
			return {color: '#f00', weight: 6, opacity: 1}
		}
		if (isHovered) {
			return {color: '#f80', weight: 5, opacity: 0.9}
		}

		return {color: '#1e3a8a', weight: 5, opacity: 0.9, zIndex: 1000}
	},
	bindLayerEvents = (layer) => {
		layer.on('click', (e) => console.info('click', e.latlng));
	},
	createSegmentsFromGeoJson = (geoJson) => {
		// Koordinaten extrahieren (unterstützt LineString & MultiLineString)
		let coords = [];
		if (geoJson.geometry.type === 'LineString') {
			coords = geoJson.geometry.coordinates;
		} else if (geoJson.geometry.type === 'MultiLineString') {
			coords = geoJson.geometry.coordinates.flat();
		}

		// Für je zwei aufeinanderfolgende Punkte ein eigenes Segment erstellen
		for (let i = 0; i < coords.length - 1; i++) {
			const start = [coords[i][1], coords[i][0]];     // [Lat, Lng]
			const end = [coords[i + 1][1], coords[i + 1][0]]; // [Lat, Lng]
			addSegmentLayer([start, end]);
		}
	},
	addSegmentLayer = (latlngs) => {
		const segment = L.polyline(latlngs, {color: '#0284c7', weight: 4}).addTo(map);
		segments.push(segment);
	},
	getBearing = (latlng1, latlng2) => {
		const toRad = (deg) => (deg * Math.PI) / 180;
		const toDeg = (rad) => (rad * 180) / Math.PI;

		const lat1 = toRad(latlng1.lat);
		const lat2 = toRad(latlng2.lat);
		const dLon = toRad(latlng2.lng - latlng1.lng);

		const y = Math.sin(dLon) * Math.cos(lat2);
		const x =
			Math.cos(lat1) * Math.sin(lat2) -
			Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);

		const brng = toDeg(Math.atan2(y, x));
		return Math.round((brng + 360) % 360);
	},
	setDataByModus = () => {
		const data = mapData.value

		switch (selectedModus.value) {
			case 'Track':
				if (geoJsonPlannerLayer) {
					geoJsonPlannerLayer.remove()
				}
				featureCollectionName.value = mapData.value.tracks.name || ''

				allTrackFeatures.value = Array.isArray(data.tracks.features) ? data.tracks.features : []
				allMediaFeatures.value = Array.isArray(data.media.features) ? data.media.features : []

				renderGeoJsonTrackLayer(allTrackFeatures.value)
				renderGeoJsonMediaLayer(allMediaFeatures.value)
				break
			case 'Planung':
				/*
								if (geoJsonMediaLayer) {
									geoJsonMediaLayer.remove()
								}

								if (geoJsonTrackLayer) {
									geoJsonTrackLayer.remove()
								}
				*/
				markers.remove()

				featureCollectionName.value = mapData.value.planners.name || ''
				allPlannerFeatures.value = Array.isArray(data.planners.features) ? data.planners.features : []
				break
		}
	},
	calculateNauticalMiles = (polylineLayer) => {
		const latlngs = polylineLayer.getLatLngs();
		let totalMeters = 0;

		for (let i = 0; i < latlngs.length - 1; i++) {
			totalMeters += latlngs[i].distanceTo(latlngs[i + 1]);
		}

		// 1 Seemeile = 1852 Meter
		return totalMeters / 1852;
	},
	getSegmentNM = (latlng1, latlng2) => {
		return latlng1.distanceTo(latlng2) / 1852;
	},
	rebuildWaypointMarkers = (waypoints) => {
		if (!map) return;

		// Alte Marker entfernen
		waypointMarkers.forEach((m) => map.removeLayer(m));
		waypointMarkers = [];

		waypoints.forEach((latlng, idx) => {
			const wpNumber = idx + 1;

			// Benutzerdefiniertes Badge-Icon für den Wegpunkt
			const icon = L.divIcon({
				className: 'wp-badge-wrapper',
				html: `<div class="wp-badge">WP ${wpNumber}</div>`,
				iconSize: 10,
				iconAnchor: [22, -8] // Leicht unterhalb des Stützpunktes positioniert, damit der Drag-Punkt frei bleibt!
			});

			const marker = L.marker(latlng, {
				icon,
				interactive: true, // Erlaubt Hover für Koordinaten-Anzeige
				pmIgnore: true     // Wichtig: Geoman soll diesen Info-Marker nicht als editierbares Objekt behandeln
			}).addTo(map);

			// Dezimal-Koordinaten beim Hovern über den WP-Marker anzeigen
			const latStr = latlng.lat.toFixed(5);
			const lngStr = latlng.lng.toFixed(5);
			marker.bindTooltip(`<strong>WP ${wpNumber}</strong><br/>Lat: ${latStr}°<br/>Lng: ${lngStr}°`, {
				direction: 'bottom',
				offset: 0,
				className: 'nautical-hover-tooltip'
			});

			waypointMarkers.push(marker);
		});
	},
	formatLegTime = (nm) => {
		if (!averageSpeedKnots.value || averageSpeedKnots.value <= 0) return '-';
		const hrs = nm / averageSpeedKnots.value;
		const h = Math.floor(hrs);
		const m = Math.round((hrs - h) * 60);
		return `${h}h ${String(m).padStart(2, '0')}m`;
	},
	getMediaStyle = () => {
		return {opacity: 1}
	},
	clearPlannerGeoJson = () => {
		if (layerGroup && geoJsonPlannerLayer) {
			layerGroup.clearLayers();
			map.removeLayer(layerGroup)
			geoJsonPlannerLayer = null
			layerGroup = null
		}
	},
	removePlannerLayer = () => {
		if (layerGroup && map) {
			layerGroup.clearLayers();
			map.removeLayer(layerGroup);
			layerGroup = null;
		}
		selectedPlannerId.value = null
//		currentTourGeoJson.value = null
	},
	updateSegmentTooltip = (segment, index) => {
		const pts = segment.getLatLngs();
		if (pts.length < 2) return;

		let segMeters = 0;
		for (let i = 0; i < pts.length - 1; i++) {
			segMeters += pts[i].distanceTo(pts[i + 1]);
		}
		const nm = segMeters / 1852;
		const course = String(getBearing(pts[0], pts[pts.length - 1])).padStart(3, '0');
		const legTime = formatLegTime(nm);

		const tooltipContent = `<div style="text-align: left; line-height: 1.4;">
				<strong>Etappe ${index + 1}</strong> (WP ${index + 1} → WP ${index + 2})<br/>
				Distanz: <strong>${nm.toFixed(2)} NM</strong><br/>
				Kurs: <strong>${course}°</strong> | Zeit: <strong>~${legTime}</strong>
			</div>`;

		if (!segment.getTooltip()) {
			segment.bindTooltip(tooltipContent, {
				permanent: false, // Erscheint nur bei MouseOver!
				sticky: true,     // Folgt dem Mauszeiger entlang der Linie
				className: 'nautical-hover-tooltip'
			});
		} else {
			segment.setTooltipContent(tooltipContent);
		}
	},
	toggleGeoJsonPlannerLayer = (data) => {
		if (!map || !data) return;

		const rawData = toRaw(data),
			// ID ggf. anpassen, z. B. rawData.id oder rawData.properties.id
			clickedId = rawData.id ?? rawData.properties?.id;

		// 1. TOGGLE: Bei erneutem Klick auf denselben Link -> ausblenden
		if (selectedPlannerId.value === clickedId && layerGroup) {
			removePlannerLayer();
			selectedPlannerId.value = null
			currentTourGeoJson.value = null
			return;
		}

		// 2. WECHSEL: Alten Layer + alte Marker restlos löschen
		removePlannerLayer();

		// 3. Neue Gruppe erstellen und direkt zur Karte hinzufügen
		layerGroup = L.layerGroup().addTo(map);

		const featureCollection = {
			type: 'FeatureCollection',
			features: Array.isArray(rawData) ? rawData : [rawData]
		};

		const geoJsonPlannerLayer = L.geoJSON(featureCollection, {
			style: {
				color: 'blue',
				weight: 5, // 30 ist extrem breit, 5 ist eine gute Linienbreite
			},
			onEachFeature: handleEachPlannerFeature
		});

		// MultiLine zur Gruppe hinzufügen
		layerGroup.addLayer(geoJsonPlannerLayer);
		selectedPlannerId.value = clickedId
		currentTourGeoJson.value = clickedId === selectedPlannerId.value ? rawData : null

		if (geoJsonPlannerLayer.getBounds().isValid()) {
			map.fitBounds(geoJsonPlannerLayer.getBounds());
		}
	},
	handleEachPlannerFeature = (feature) => {
		if (!feature?.properties?.points) return;

		feature.properties.points.forEach(p => {
			const desc = p.desc ? p.desc + '<br>' : '';
			const popupHtml = `
			<div style="min-width: 195px; font-family: system-ui, sans-serif;">
				<strong>${p.name} ID ${p.id}</strong><br>
				${desc}
				Lat: ${p.latlng.lat}<br>
				Lng: ${p.latlng.lng}
			</div>`;

			const marker = L.circleMarker(p.latlng, {
				color: '#c00',
				radius: 6,
				opacity: 1,
				fillOpacity: 1,
				className: 'planner-point',
				zIndex: 1000,
				bubblingPointerEvents: true,
				pane: trackPointPaneName,
			});

			marker.bindPopup(popupHtml);

			// WICHTIG: Nicht an map, sondern an layerGroup anhängen!
			marker.addTo(layerGroup);
		})
	},
	handleEachTrackFaeture = (feature, layer) => {
		feature.properties.points.forEach(p => {
			let popupHtml = `
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
//				pane: trackPointPaneName,
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
			marker = L.marker(feature.geometry.coordinates, {zIndex: 10});

		markers.addLayer(marker)
		marker.on('click', () => {
			showMedia(props)
		})
	},
	renderGeoJsonPlannerLayer = (data) => {
		if (!map) return

		removePlannerLayer()

		map.getPane(trackPointPaneName).style.zIndex = 600;
		map.getPane(trackPointPaneName).style.pointerEvents = 'auto';

		const featureCollection = {
			type: 'FeatureCollection',
			features: Array.isArray(data) ? toRaw(data) : [toRaw(data)]
		}
		layerGroup = L.layerGroup()
		geoJsonPlannerLayer = L.geoJSON(toRaw(featureCollection), {
			style: {
				zIndex: 600,
				color: 'blue',
				weight: 30,
			},
			pane: trackPointPaneName,
			onEachFeature: handleEachPlannerFeature
		})
		layerGroup.addLayer(geoJsonPlannerLayer);

		if (geoJsonPlannerLayer.getBounds().isValid()) {
			map.fitBounds(geoJsonPlannerLayer.getBounds())
		}
	},
	renderGeoJsonTrackLayer = (data) => {
		if (!map) return
		clearPlannerGeoJson()

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
	// planner
	createSegmentLayer = (latlngs, insertIndex = null) => {
		const segment = L.polyline(latlngs, {
			color: '#0284c7',
			weight: 5,       // Etwas breiter für leichteres MouseOver
			opacity: 0.85
		}).addTo(map);

		if (insertIndex !== null) {
			segments.splice(insertIndex, 0, segment);
		} else {
			segments.push(segment);
		}

		// Visuelles Feedback beim Hovern über die Linie
		segment.on('mouseover', () => segment.setStyle({color: '#0369a1', weight: 7}));
		segment.on('mouseout', () => segment.setStyle({color: '#0284c7', weight: 5}));

		return segment;
	},
	loadFromGeoJson = (geoJson) => {
		let coords = [];
		if (geoJson.geometry.type === 'LineString') {
			coords = geoJson.geometry.coordinates;
		} else if (geoJson.geometry.type === 'MultiLineString') {
			coords = geoJson.geometry.coordinates.flat();
		}

		for (let i = 0; i < coords.length - 1; i++) {
			const start = L.latLng(coords[i][1], coords[i][0]);
			const end = L.latLng(coords[i + 1][1], coords[i + 1][0]);
			createSegmentLayer([start, end]);
		}
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
	updatePlannerLayerStyles = () => {
		if (!geoJsonPlannerLayer) return
		geoJsonPlannerLayer.eachLayer((layer) => {
			const feature = layer.feature
			if (feature && feature.geometry.type === 'LineString') {
				layer.setStyle(getPlannerStyle(feature))
				if (feature.properties.id === selectedPlannerId.value) {
					layer.bringToFront()
				}
			}
		})
	},
	selectTour = (id, zoomToTrack = true, moveToCenter = false) => {
		selectedPlannerId.value = selectedPlannerId.value === id ? null : id
//		selectedPlannerId.value = id

		const myOffcanvas = document.getElementById('offcanvasTracks'),
			geojson = allPlannerFeatures.value.find(p => p.properties.id === id);

		if (myOffcanvas) {
			myOffcanvas.addEventListener('hidden.bs.offcanvas', () => {
				if (!isMobile) {
					unSelectPlanner()
				}
			})
		}

		if (geojson) {
			renderGeoJsonPlannerLayer(geojson)
			loadFromGeoJson(geojson)
			currentTourGeoJson.value = geojson.properties.id === selectedPlannerId.value

			if (moveToCenter) {
				const bounds = tour.getBounds();
//				const bounds = L.latLngBounds(props.northEast, props.southWest);
				map.fitBounds(bounds);
			}

			if (zoomToTrack) {
				geoJsonPlannerLayer.eachLayer(layer => {
					if (layer.feature?.properties?.id === id) {
						map.fitBounds(layer.getBounds(), {maxZoom: props.maxZoom})
						layer.openPopup()
					}
				})
			}
		}
	},
	selectTrack = (id, zoomToTrack = false, moveToCenter = true) => {
		selectedTrackId.value = selectedTrackId.value === id ? null : id

		const myOffcanvas = document.getElementById('offcanvasTracks');

		if (myOffcanvas) {
			myOffcanvas.addEventListener('hidden.bs.offcanvas', () => {
				if (!isMobile) {
					unSelectTrack()
				}
				const bounds = L.latLngBounds(props.northEast, props.southWest);
				map.fitBounds(bounds);
			})
		}

		if (selectedTrackId.value !== null) {
			if (moveToCenter) {
				const bounds = L.latLngBounds(props.northEast, props.southWest);
				map.fitBounds(bounds);
			}

			if (zoomToTrack && geoJsonTrackLayer) {
				geoJsonTrackLayer.eachLayer((layer) => {
					if (layer.feature?.properties?.id === id) {
						map.fitBounds(layer.getBounds(), {maxZoom: props.maxZoom})
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
	unSelectPlanner = () => {
		selectedPlannerId.value = null
		clearPlannerGeoJson()
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

		let media = {};

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
			if ('video' === props.type) {
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

// Wenn der Nutzer die Geschwindigkeit in Knoten ändert -> Tooltips aktualisieren
watch(selectedYear, () => {
	selectedTrackId.value = null
	fetchApi()
})
watch(selectedModus, () => {
	selectedTrackId.value = null
	selectedPlannerId.value = null
	currentTourGeoJson.value = null
	setDataByModus()
})
watch([selectedTrackId, hoveredTrackId], () => {
	updateLayerStyles()
})
/*
watch(selectedPlannerId, (id) => {
	updatePlannerLayerStyles()
})
*/
onMounted(() => {
	map = L.map(mapContainer.value, {
		zoom: props.zoom,
		maxZoom: props.maxZoom,
		center: props.center,
	});

	const bounds = L.latLngBounds(props.northEast, props.southWest);
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
		{'OpenSeaMap': openSeaMapLayer},
		{position: 'topright'}
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

	fetchApi()
})
onUnmounted(() => {
	map?.remove()
})
</script>

<style lang="scss">
#offcanvasTracks {
	margin-top: 55px;
}

#modus {
	width: 130px;
}

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

	table {
		tr {
			td, th {
				color: blue;
				text-align: left;
			}
		}
	}
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

/*
#sailing-planner {
    position: absolute;
    top: 50px;
    right: 10px;
    border: 1px solid gray;
    z-index: 10000;
    border-radius: 8px;
    background-color: transparent;

    .sailing-planner {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .stats-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: #0f172a;
        color: #f8fafc;
        padding: 0.85rem 1.25rem;
        border-radius: 8px;
    }
    .stat-item {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .stat-item .label {
        font-size: 0.75rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .stat-item strong {
        font-size: 1.15rem;
        color: #38bdf8;
    }
    .speed-control {
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
        background: #1e293b;
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
    }
    .speed-control input {
        width: 60px;
        padding: 0.25rem 0.4rem;
        border-radius: 4px;
        border: 1px solid #334155;
        background: #0f172a;
        color: #f8fafc;
        font-weight: bold;
        text-align: center;
    }
    .output-panel {
        background: #f4f4f5;
        padding: 1rem;
        border-radius: 8px;
        max-height: 200px;
        overflow-y: auto;
        font-size: 0.85rem;
    }
    :deep(.nautical-hover-tooltip) {
        background: rgba(15, 23, 42, 0.92);
        color: #f8fafc;
        border: 1px solid #38bdf8;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
    :deep(.wp-badge) {
        background: #0284c7;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        text-align: center;
        padding: 1px 4px;
        border-radius: 4px;
        border: 1px solid #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        white-space: nowrap;
    }
}
.drag-box {
    padding: 0;
    background: #42b883;
    color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.drag-box:active {
    cursor: grabbing;
}
*/
</style>
