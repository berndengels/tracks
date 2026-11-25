<template>
    <div
         id="sailing-planner"
         ref="plannerBox"
         class="drag-box"
    >
        <div class="sailing-planner">
            <div class="stats-bar">
                <div class="stats-item cursor-move"><i class="fa-solid fa-up-down-left-right"></i></div>
                <div v-if="totalDistanceNM" class="stat-item">
                    <span class="label">Gesamtdistanz</span>
                    <strong>{{ Math.round(totalDistanceNM, 1)  }} NM</strong>
                </div>

                <div class="stat-item">
                    <span class="label">Geschätzte Fahrzeit (ETA)</span>
                    <strong>~ {{ estimatedDuration }}</strong>
                </div>

                <div class="stat-item speed-control">
                    <label for="speed-input">Ø Fahrt:</label>
                    <input
                        id="speed-input"
                        v-model.number="averageSpeedKnots"
                        type="number"
                        step="0.5"
                        min="0.5"
                        max="30"
                    />
                    <span>kn</span>
                </div>

                <div class="stat-item">
                    <span class="label">Etappen</span>
                    <strong>{{ segmentCount }}</strong>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, computed, watch, useTemplateRef} from 'vue'
//import { useDraggable } from '@vueuse/core'
import L from "leaflet";

let segments = [],
    waypointMarkers = [];

const props = defineProps([
        'geojson',
    ]),
    totalDistanceNM = ref('0.00'),
    currentGeoJson = ref(props.geojson),
    segmentCount = ref(0),
    averageSpeedKnots = ref(5.0),
    allWaypoints = ref([]),
    el = useTemplateRef('plannerBox'),
    geoJsonOutput = ref(null),
    getSegmentNM = (latlng1, latlng2) => {
        return latlng1.distanceTo(latlng2) / 1852;
    },
	updateData = () => {
        if(! currentGeoJson) return;

        console.info('currentGeoJson', currentGeoJson.value)

        let totalNM = 0;
        const allWaypointsLatLng = [],
            allWaypointsGeoJson = [],
            parent_id = currentGeoJson.value.properties.id;

        allWaypoints.value = []

        segments.forEach((seg, index) => {
            updateSegmentTooltip(seg, index);

            const pts = seg.getLatLngs();
            for (let i = 0; i < pts.length - 1; i++) {
                totalNM += getSegmentNM(pts[i], pts[i + 1]);
            }

            pts.forEach((pt, ptIdx) => {
                if (index > 0 && ptIdx === 0) return;
                allWaypointsLatLng.push(pt)
                allWaypoints.value.push({
                    route_planner_id: parent_id,
                    lat: pt.lat.toFixed(6),
                    lng: pt.lng.toFixed(6),
                })
                allWaypointsGeoJson.push([
                    Number(pt.lng.toFixed(6)),
                    Number(pt.lat.toFixed(6))
                ])

            })
        })
        totalDistanceNM.value = Number(totalNM.toFixed(2));
        segmentCount.value = segments.length;
    },
    estimatedDuration = computed(() => {
        if (!averageSpeedKnots.value || averageSpeedKnots.value <= 0 || totalDistanceNM.value <= 0) {
            return '0h 00m';
        }
        const totalHours = totalDistanceNM.value / averageSpeedKnots.value;
        const hours = Math.floor(totalHours);
        const minutes = Math.round((totalHours - hours) * 60);
        return `${hours}h ${String(minutes).padStart(2, '0')}m`;
    });

watch(averageSpeedKnots, () => {
    segments.forEach((seg, index) => updateSegmentTooltip(seg, index));
})
</script>

<style scoped lang="scss">
#sailing-planner {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 300px;
    height: 140px;
    max-height: 400px;
    cursor: grab;
    user-select: none;
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
    /* Hover-Tooltip Styling */
    :deep(.nautical-hover-tooltip) {
        background: rgba(15, 23, 42, 0.92);
        color: #f8fafc;
        border: 1px solid #38bdf8;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
    /* Nummerierte Wegpunkt-Badges (WP 1, WP 2...) */
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
</style>
