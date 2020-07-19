<template>
    <div class="form-group">
        <label class="col-form-label-sm" :for="id">
            <slot>

            </slot>
        </label>
        <flat-pickr
                :id="id"
                v-model="dateValue"
                :config="datePickerConfig"
                class="form-control form-control-sm">
        </flat-pickr>
    </div>
</template>

<script>
    import flatPickr from 'vue-flatpickr-component';
    import moment from 'moment';

    export default {
        props: {
            value: String
        },
        data: function () {
            return {
                dateValue: null,
                id: null
            }
        },
        watch: {
            dateValue: function () {
                //emit event to work as v-model
                this.$emit('input', this.dateValue);
            }
        },
        components: {
            flatPickr
        },
        computed: {
            datePickerConfig: function() {
                return {
                    altInput: true,
                    altFormat: "DD.MM.YYYY HH:mm",
                    dateFormat: "iso",
                    parseDate: (datestr, format) => {
                        if (format === "iso") {
                            return moment(datestr).toDate();
                        } else {
                            return moment(datestr, format, true).toDate();
                        }
                    },
                    formatDate: (date, format, locale) => {
                        if (format === "iso") {
                            return moment(date).format();
                        } else {
                            return moment(date).format(format);
                        }
                        // locale can also be used
                    },
                    enableTime: true,
                    time_24hr: true
                }
            }
        },
        mounted() {
            //get unique id of component for id attribute
            this.id = this._uid;
            this.dateValue = this.value;
        }
    }
</script>