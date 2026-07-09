import {ref} from "vue";

const namespaced = true,
    url = '/api/tracks',
    state = {
        tracks: ref([]),
        loading: true,
    },
    getters = {
        getTracks: (state) => state.tracks,
    },
    mutations = {
        setTracks(state, data) {
            state.tracks = data
            state.loading = false
        },
        addTrack(state, data) {
            state.tracks.push(data)
        },
        updateTrack(state, data) {
            state.tracks = state.tracks.map(m => m.id === data.id ? data : m)
        },
        deleteTrack(state, id) {
            state.tracks = state.tracks.filter(m => m.id !== id)
        },
    },
    actions = {
        async fetch({commit}, id) {
            let path = url + '/' + id;
            const resp = await axios.get(path)
            if(resp.data && resp.data.items) {
                commit("setTracks", resp.data.items);
            }
        },
        update({commit}, data) {
            let path = url + '/' + data.id;
            axios.put(path, data)
                .then(resp => {
                    if (resp.data && resp.data.item) {
                        commit("updateTrack", resp.data.item);
                    }
                }).catch(err => console.error(err));
        },
        delete({commit}, data) {
            if(confirm("Daten wirklich löschen?")) {
                let path = url + '/' + data.id;
                axios.delete(path)
                    .then(resp => {
                        if (resp.data && resp.data.id) {
                            commit("deleteTrack", resp.data.id);
                        }
                    }).catch(err => console.error(err));
            }
        },
    };
export default {
    namespaced,
    state,
    getters,
    mutations,
    actions
}
