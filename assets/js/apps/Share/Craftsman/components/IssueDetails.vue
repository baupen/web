<template>
    <div class="card numbered-card" :class="{ 'border-success' : issueHasResponse }">
        <img v-if="issue.imageShareView !== ''" class="card-img-top clickable"
             :src="issue.imageShareView" @click.prevent="$emit('open-lightbox', issue.imageFull)">
        <div class="card-number"
             :class="{ 'bg-success text-white' : issueHasResponse, 'bg-warning text-white': !issueHasResponse }">
            {{ issue.number }}
        </div>
        <div class="card-body">
            <p class="card-text">{{issue.description}}</p>
            <p class="card-text">
                <small class="small">{{$t("issue.response_limit")}}:
                    {{ formatDateTime(issue.responseLimit) }}
                </small>
            </p>
            <template>
                <button v-if="!issueHasResponse" @click.prevent="$emit('send-response', issue)"
                        class="btn btn-outline-success">
                    {{$t("actions.send_response")}}
                </button>
                <a href="#" v-else="issueHasResponse" @click.prevent="$emit('remove-response', issue)">
                    {{$t("actions.remove_response")}}
                </a>
            </template>
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
            },
            issueHasResponse: {
                type: Boolean,
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
                return value === null ? "-" : moment(value).fromNow();
            }
        }
    }
</script>