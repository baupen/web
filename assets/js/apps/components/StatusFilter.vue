<template>
    <div>
        <h4 @click="toggleFilter" class="clickable" :class="{'mark' : filter.enabled }">{{$t("issue.status")}}</h4>
        <div v-if="filter.enabled">
            <base-checkbox v-model="filter.registered.active">
                {{$t("filter.status.registered")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.registered.active">
                <filter-status :status="filter.registered" :allow-value-choose="false"/>
            </div>

            <base-checkbox v-model="filter.read.active">
                {{$t("filter.status.read")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.read.active">
                <filter-status :status="filter.read" :allow-date-choose="false"/>
            </div>


            <base-checkbox v-model="filter.responded.active">
                {{$t("filter.status.responded")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.responded.active">
                <filter-status :status="filter.responded"/>
            </div>

            <base-checkbox v-model="filter.reviewed.active">
                {{$t("filter.status.reviewed")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.reviewed.active">
                <filter-status :status="filter.reviewed"/>
            </div>
        </div>
    </div>
</template>

<script>

    import FilterStatus from './FilterStatus'
    import BaseCheckbox from './BaseCheckbox'

    export default {
        props: {
            filter: {
                type: Object,
                required: true
            }
        },
        components: {
            FilterStatus,
            BaseCheckbox
        },
        methods: {
            toggleFilter: function () {
                this.filter.enabled = !this.filter.enabled;
            }
        }
    }
</script>