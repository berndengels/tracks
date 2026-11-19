<template>
    <div id="map">
        <l-map ref="map" v-model:zoom="zoom" :bounds="bounds" :center="center">
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
            <l-control-scale position="bottomleft" :metric="true" :imperial="true" />
        </l-map>
    </div>
</template>

<script>
import "leaflet/dist/leaflet.css"
import { LMap, LGeoJson, LMarker, LFeatureGroup, LPopup, LPolyline, LTileLayer, LControlScale} from "@vue-leaflet/vue-leaflet";
import { MarkerClusterGroup } from 'leaflet.markercluster';
import "leaflet.markercluster/dist/MarkerCluster.css"
import "leaflet.markercluster/dist/MarkerCluster.Default.css"

export default {
    name: "Map",
    props: ['tracks','points','media','initZomm','southWest','northEast','bounds','center'],
    components: { MarkerClusterGroup, LMap, LGeoJson, LMarker, LFeatureGroup, LPopup, LPolyline, LTileLayer, LControlScale },
    data() {
        return {
            zoom: this.initZomm,
            geojsonTracks: {
                type: "FeatureCollection",
                features: [
                    // ...
                ],
            },
            geojsonTracksOptions: {
                // Options that don't rely on Leaflet methods.
            },
        };
    },
}
</script>

<style scoped lang="scss">
#map {
    width: auto;
    height: 100hv;
}
</style>
