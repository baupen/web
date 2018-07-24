<template>
    <p>
        <span v-if="entry.type === 'response-received'"
              v-html="$tc('feed.entries.response_received', entry.count, { count: entry.count, craftsman: craftsmanLink })">
        </span>
        <span v-else-if="entry.type === 'visited-webpage'"
              v-html="$tc('feed.entries.visited_webpage', entry.count, { craftsman: craftsmanLink })">
        </span>
        <span v-else-if="entry.type === 'overdue'"
              v-html="$tc('feed.entries.overdue_limit', entry.count, { count: entry.count, craftsman: craftsmanLink })">
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
            },
            craftsmanLink: function () {
                return '<a href="/register?craftsman=' + this.entry.craftsman.id + '">' + this.entry.craftsman.name + '</a>';
            }
        }
    }
</script>