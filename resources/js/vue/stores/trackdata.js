import {data} from "autoprefixer";

export const useTrackStore = defineStore('tracks', {
    state: () => ({ data: null }),
    getters: {
        data: data,
    },
    actions: {
        fetch(nordEast, southWest) {
        },
    },
})
