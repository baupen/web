<template>
    <p>
        <span v-if="entry.type === 'response-received'">
            {{ $tc("feed.entries.response_received", entry.count,
            { count: entry.count, craftsman: entry.craftsmanName })}}
        </span>
        <span v-else-if="entry.type === 'visited-webpage'">
            {{ $t("feed.entries.visited_webpage",
            { craftsman: entry.craftsmanName })}}
        </span>
        <span v-else-if="entry.type === 'overdue'">
            {{ $tc("feed.entries.overdue_limit", entry.count,
            { count: entry.count, craftsman: entry.craftsmanName })}}
        </span>
        - <span class="text-secondary">{{ formattedTimestamp }}</span>
    </p>
</template>

<script>
    import moment from "moment";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        props: {
            entry: {
                type: Object,
                required: true
            }
        },
        computed: {
            formattedTimestamp: function () {
                return moment(this.entry.timestamp).fromNow();
            }
        }
    }
</script>