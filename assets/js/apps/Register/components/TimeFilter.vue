<template>
    <div>
        <h4 @click="toggleFilter" class="clickable" :class="{'mark' : filter.enabled }">{{$t("filter.time")}}</h4>
        <div v-if="filter.enabled">
            <base-checkbox v-model="filter.registered.active">
                {{$t("filter.time_set", {state: $t("issue.status_values.registered")})}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.registered.active">
                <time-filter-entry :status="filter.registered"/>
            </div>

            <base-checkbox v-model="filter.read.active">
                {{ $t("issue.status_values.read") }}
            </base-checkbox>

            <base-checkbox v-model="filter.responded.active">
                {{$t("filter.time_set", {state: $t("issue.status_values.responded")})}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.responded.active">
                <time-filter-entry :status="filter.responded"/>
            </div>

            <base-checkbox v-model="filter.reviewed.active">
                {{$t("filter.time_set", {state: $t("issue.status_values.reviewed")})}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.reviewed.active">
                <time-filter-entry :status="filter.reviewed"/>
            </div>
        </div>
    </div>
</template>

<script>

    import TimeFilterEntry from './TimeFilterEntry'
    import BaseCheckbox from '../../components/Base/BaseCheckbox'

    export default {
        props: {
            filter: {
                type: Object,
                required: true
            }
        },
        components: {
            TimeFilterEntry,
            BaseCheckbox
        },
        methods: {
            toggleFilter: function () {
                this.filter.enabled = !this.filter.enabled;
            }
        }
    }
</script>