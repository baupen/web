<template>
    <p>
        <span>{{ entry.content }}</span>
        - <span class="text-secondary">{{ formattedTimestamp }} - {{ entry.authorName }}</span>
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