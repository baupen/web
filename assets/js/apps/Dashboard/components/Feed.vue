<template>
    <div>
        <atom-spinner v-if="isMounting"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <div v-else>
            <feed-entry v-for="entry in feed.entries" :key="entry.id" :entry="entry" />
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import {AtomSpinner} from 'epic-spinners'
    import FeedEntry from "./FeedEntry";

    export default {
        components: {FeedEntry, AtomSpinner},
        data: function() {
            return {
                isMounting: false,
                feed: []
            }
        },
        mounted() {
            axios.post("/api/dashboard/feed/list", {
                "constructionSiteId": this.constructionSiteId
            }).then((response) => {
                this.feed = response.data.feed;
                this.isMounting = false;
            });
        }
    }
</script>