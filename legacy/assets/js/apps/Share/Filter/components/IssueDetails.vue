<template>
    <div class="card numbered-card">
        <img v-if="issue.imageShareView !== ''" class="card-img-top clickable"
             :src="issue.imageShareView" @click.prevent="$emit('open-lightbox', issue.imageFull)">
        <div class="card-number"
             :class="{ 'bg-success text-white' : issue.reviewedAt !== null, 'bg-warning text-white': issue.reviewedAt === null }">
            {{ issue.number }}
        </div>
        <div class="card-body">
            <p class="card-text">{{issue.description}}</p>
        </div>
        <div class="card-footer">
            <small class="text-muted">{{issue.registrationByName}} -
                {{formatDateTime(issue.registeredAt)}}
            </small>
        </div>
    </div>
</template>

<script>
    import moment from "moment";

    const lang = document.documentElement.lang.substr(0, 2);

    export default {
        props: {
            issue: {
                type: Object,
                required: true
            }
        },
        data() {
            return {
                locale: lang
            }
        },
        methods: {
            formatDateTime: function (value) {
                return value === null ? "-" : moment(value).locale(this.locale).fromNow();
            }
        }
    }
</script>