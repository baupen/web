<template>
    <div>
        <atom-spinner v-if="isMounting"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <div v-else-if="showEntries.length > 0">
            <feed-entry v-for="entry in showEntries" :key="entry.id" :entry="entry" />
            <button class="btn btn-outline-secondary" v-if="showEntries.length < feed.entries.length" @click="maxEntries += 10">
                {{$t("feed.show_more")}}
            </button>
        </div>
        <div v-else>
            <p>{{$t("feed.no_entries_yet")}}</p>
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
                isMounting: true,
                feed: [],
                maxEntries: 20
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
            axios.post("/api/feed/list", {
                "constructionSiteId": this.constructionSiteId
            }).then((response) => {
                this.feed = response.data.feed;
                this.isMounting = false;
            });
        }
    }
</script>