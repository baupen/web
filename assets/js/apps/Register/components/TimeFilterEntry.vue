<template>
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

<script>
    import BaseDateInput from '../../components/Base/BaseDateInput'
    import BaseSliderCheckbox from "../../components/Base/BaseSliderCheckbox"


    export default {
        props: {
            status: {
                type: Object,
                required: true,
            }
        },
        components: {
            BaseSliderCheckbox,
            BaseDateInput
        }
    }
</script>