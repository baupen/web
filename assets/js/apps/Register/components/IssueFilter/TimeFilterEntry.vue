<template>
    <div class="row">
        <div class="col-md-6">            
            <base-date-time-input v-model="status.start">
                {{$t("filter.later_than")}}
            </base-date-time-input>
        </div>
        <div class="col-md-6">
            <base-date-time-input v-model="status.end">
                {{$t("filter.earlier_than")}}
            </base-date-time-input>
        </div>
    </div>
</template>

<script>
    import BaseDateTimeInput from '../../../components/Base/BaseDateTimeInput'
    import BaseSliderCheckbox from "../../../components/Base/BaseSliderCheckbox"

    export default {
        props: {
            status: {
                type: Object,
                required: true,
            }
        },
        components: {
            BaseSliderCheckbox,
            BaseDateTimeInput
        }
    }
</script>