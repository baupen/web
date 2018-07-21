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
    <div v-else class="editable hoverable" @click.exact.prevent.stop="$emit('edit-start')" @mouseover="hover = true"
         @mouseleave="hover = false">
        <div class="tooltip-wrapper">
            <div class="tooltip bs-tooltip-top progressbar-tooltip" :class="{'show': hover}">
                <div class="tooltip-inner">
                    <span v-if="issue.reviewedAt !== null">{{$t("issue.status_values.reviewed")}}</span>
                    <span v-else-if="issue.respondedAt !== null">{{$t("issue.status_values.responded")}}</span>
                    <span v-else-if="issue.isRead">{{$t("issue.status_values.read")}}</span>
                    <span v-else>{{$t("issue.status_values.registered")}}</span>
                </div>
            </div>
        </div>
        <ul class="progressbar">
            <li :class="{'active': issue.isRead || issue.respondedAt !== null }"></li>
            <li :class="{'active': issue.respondedAt !== null}"></li>
            <li :class="{'active': issue.reviewedAt !== null}"></li>
        </ul>
    </div>
</template>

<style>
    .tooltip-wrapper {
        position: relative;
    }

    .progressbar-tooltip {
        top: -3em;
        left: 20%;
    }
</style>


<script>
    import moment from "moment";
    import BaseSliderCheckbox from '../Base/BaseSliderCheckbox'
    import BaseCheckbox from '../Base/BaseCheckbox'

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);


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
                hover: false
            }
        },
        methods: {
            editConfirm: function () {
                //if any changes emit change event
                if (this.respondedStatusSet !== (this.issue.respondedAt !== null) || this.reviewedStatusSet !== (this.issue.reviewedAt !== null)) {
                    this.$emit('edit-confirm', this.respondedStatusSet, this.reviewedStatusSet);
                }
            },
            mouseOver: function () {
                this.hover = !this.hover;
            }
        },
        computed: {
            formattedResponseLimit: function () {
                if (this.issue.responseLimit === null) {
                    return this.$t("issue.no_response_limit");
                }
                return moment(this.issue.responseLimit).fromNow();
            },
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