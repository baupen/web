<template>
    <div>
        <div v-if="allowValueChoose" class="form-group">
            <base-slider-checkbox v-model="status.value">
                {{$t("filter.status_set")}}
            </base-slider-checkbox>
        </div>
        <template v-if="allowDateChoose && status.value">
            <div class="row">
                <div class="col-md-6">
                    <base-date-input v-model="status.start">
                        {{$t("filter.later_than")}}
                    </base-date-input>
                </div>
                <div class="col-md-6">
                    <base-date-input v-model="status.end">
                        {{$t("filter.earlier_than")}}
                    </base-date-input>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import BaseDateInput from './BaseDateInput'
    import BaseSliderCheckbox from "./BaseSliderCheckbox"


    export default {
        props: {
            status: {
                type: Object,
                required: true,
            },
            allowDateChoose: false,
            allowValueChoose: true
        },
        components: {
            BaseSliderCheckbox,
            BaseDateInput
        }
    }
</script>