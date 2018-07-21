<template>
    <span v-if="editEnabled" class="form-group" @click.exact.prevent.stop="">
        <date-picker :lang="datePickerTranslation" format="dd.MM.yyyy" ref="responseLimit" @keyup.enter="editConfirm" @keyup.esc="$emit('edit-abort')" v-model="responseLimit" class="form-control form-control-sm" />
        <button class="btn btn-primary" @click="editConfirm">
            <slot name="save-button-content"></slot>
        </button>
        <button class="btn btn-outline-secondary" @click="$emit('edit-abort')">{{$t("actions.abort")}}</button>
    </span>
    <div v-else class="editable" @click.exact.prevent.stop="$emit('edit-start')">
        <span>
            {{ formattedResponseLimit }}
        </span>
    </div>
</template>


<script>
    import moment from "moment";
    import DatePicker from 'vuejs-datepicker';
    const locale = require('vuejs-datepicker/dist/locale');

    const lang = document.documentElement.lang.substr(0, 2);
    const datePickerTranslation = locale[lang];
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
            DatePicker
        },
        data: function () {
            return {
                responseLimit: null,
                datePickerTranslation
            }
        },
        methods: {
            editConfirm: function () {
                //parse response limit; weird library behaviour leaves the variable empty if window only opened; but not set by user
                if (typeof this.responseLimit.toISOString !== "function") {
                    if (this.responseLimit.responseLimit !== null) {
                        this.responseLimit = Date.parse(issue.responseLimit);
                    } else {
                        this.responseLimit = new Date();
                    }
                }

                this.issue.responseLimit = this.responseLimit.toISOString();
                this.$emit('edit-confirm');
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
                if (this.issue.responseLimit !== null) {
                    this.responseLimit = Date.parse(this.issue.responseLimit);
                } else {
                    this.responseLimit = new Date();
                }

                //focus input on next tick
                this.$nextTick(() => {
                    let input = this.$refs.responseLimit;
                    input.$el.getElementsByTagName("div")[0].getElementsByTagName("input")[0].click();
                });
            }
        }
    }

</script>