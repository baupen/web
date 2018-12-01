<template>
    <div>
        <atom-spinner v-if="isMounting"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <div v-else>
            <h2>{{constructionSite.name}}</h2>
            <img :src="constructionSite.imageMedium" class="img-fluid" alt="">
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import {AtomSpinner} from 'epic-spinners'
    import FeedEntry from "./FeedEntry";

    export default {
        components: {FeedEntry, AtomSpinner},
        data: function () {
            return {
                isMounting: true,
                constructionSite: null
            }
        },
        props: {
            constructionSiteId: {
                type: String,
                required: true
            }
        },
        computed: {
            showEntries: function () {
                if (this.feed === null) {
                    return [];
                }
                let currentEntries = 0;
                return this.feed.entries.filter(e => currentEntries++ < this.maxEntries);
            }
        },
        mounted() {
            axios.post("/api/dashboard/constructionSite", {
                "constructionSiteId": this.constructionSiteId
            }).then((response) => {
                this.constructionSite = response.data.constructionSite;
                this.isMounting = false;
            });
        }
    }
</script>