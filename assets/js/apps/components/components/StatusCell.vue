<template>
    <div v-if="editEnabled" @click.exact.stop="">
        <base-slider-checkbox v-model="respondedStatusSet">
            {{$t("issue.status_values.responded")}}
        </base-slider-checkbox>
        <base-slider-checkbox v-model="reviewedStatusSet">
            {{$t("issue.status_values.reviewed")}}
        </base-slider-checkbox>
        <button class="btn btn-primary" @click="editConfirm">
            <slot name="save-button-content"></slot>
        </button>
        <button class="btn btn-outline-secondary" @click="$emit('edit-abort')">{{$t("actions.abort")}}</button>
    </div>
    <div v-else>
        <div class="tooltip-wrapper">
            <div class="tooltip bs-tooltip-top progressbar-tooltip" :class="{'show': hover && !expanded}">
                <div class="tooltip-inner">
                    <span v-if="issue.reviewedAt !== null">{{$t("issue.status_values.reviewed")}}</span>
                    <span v-else-if="issue.respondedAt !== null">{{$t("issue.status_values.responded")}}</span>
                    <span v-else-if="issue.isRead">{{$t("issue.status_values.read")}}</span>
                    <span v-else>{{$t("issue.status_values.registered")}}</span>
                </div>
            </div>
        </div>
        <div class="hoverable" @mouseover="hover = true"
             @mouseleave="hover = false">
            <ul class="progressbar editable" @click.exact.prevent.stop="$emit('edit-start')">
                <li :class="{'active': issue.isRead || issue.respondedAt !== null || issue.reviewedAt !== null }"></li>
                <li :class="{'active': issue.respondedAt !== null || issue.reviewedAt !== null}"></li>
                <li :class="{'active': issue.reviewedAt !== null}"></li>
            </ul>
        </div>
        <div>
            <a href="#" v-if="!expanded" @click.prevent="expanded = true">{{ $t("view.more") }}</a>
            <a href="#" v-if="expanded" @click.prevent="expanded = false">{{ $t("view.less") }}</a>
            <div v-if="expanded">
                <span v-if="issue.registeredAt != null">
                    <b>{{$t("issue.status_values.registered")}}</b>
                    {{ formatDateTime(issue.registeredAt) }},
                    {{ issue.registrationByName }}
                    <br/>
                </span>
                <span v-if="issue.respondedAt != null">
                    <b>{{$t("issue.status_values.responded")}}</b>
                    {{ formatDateTime(issue.respondedAt) }},
                    {{ issue.responseByName }}
                    <br/>
                </span>

                <span v-if="issue.reviewedAt != null">
                    <b>{{$t("issue.status_values.reviewed")}}</b>
                    {{ formatDateTime(issue.reviewedAt) }},
                    {{ issue.reviewByName }}
                    <br/>
                </span>
            </div>
        </div>
    </div>
</template>

<style>
    .tooltip-wrapper {
        position: relative;
        pointer-events: none;
    }

    .progressbar-tooltip {
        top: -3em;
        left: 20%;
    }
</style>


<script>
    import moment from "moment";
    import BaseSliderCheckbox from '../Base/BaseSliderCheckbox'

    const lang = document.documentElement.lang.substr(0, 2);


    export default {
        props: {
            issue: {
                type: Object,
                required: true
            },
            editEnabled: {
                type: Boolean,
                required: true
            }
        },
        components: {
            BaseSliderCheckbox
        },
        data: function () {
            return {
                respondedStatusSet: false,
                reviewedStatusSet: false,
                hover: false,
                expanded: false,
                locale: lang
            }
        },
        methods: {
            editConfirm: function () {
                //if any changes emit change event
                if (this.respondedStatusSet !== (this.issue.respondedAt !== null) || this.reviewedStatusSet !== (this.issue.reviewedAt !== null)) {
                    this.$emit('edit-confirm', this.respondedStatusSet, this.reviewedStatusSet);
                } else {
                    console.log("no changes detected");
                    this.$emit('edit-abort');
                }
            },
            formatDateTime: function (dateTime) {
                if (dateTime === null) {
                    return "-";
                }
                return moment(dateTime).locale(this.locale).fromNow();
            }
        },
        computed: {
            formattedResponseLimit: function () {
                if (this.issue.responseLimit === null) {
                    return this.$t("issue.no_response_limit");
                }
                return this.formatDateTime(this.issue.responseLimit);
            }
        },
        watch: {
            editEnabled: function () {
                //only perform operations if edit enabled
                if (!this.editEnabled) {
                    return;
                }

                //parse response limit
                this.respondedStatusSet = this.issue.respondedAt !== null;
                this.reviewedStatusSet = this.issue.reviewedAt !== null;
            }
        }
    }

</script>