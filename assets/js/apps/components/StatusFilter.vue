<template>
    <div>
        <h4>{{$t("issue.status")}}</h4>
        <base-checkbox v-model="statusFilter.registered.active">
            {{$t("filter.status.registered")}}
        </base-checkbox>
        <div class="filter-property-wrapper" v-if="statusFilter.registered.active">
            <filter-status :status="statusFilter.registered" :allow-value-choose="false"/>
        </div>

        <base-checkbox v-model="statusFilter.read.active">
            {{$t("filter.status.read")}}
        </base-checkbox>
        <div class="filter-property-wrapper" v-if="statusFilter.read.active">
            <filter-status :status="statusFilter.read" :allow-date-choose="false"/>
        </div>


        <base-checkbox v-model="statusFilter.responded.active">
            {{$t("filter.status.responded")}}
        </base-checkbox>
        <div class="filter-property-wrapper" v-if="statusFilter.responded.active">
            <filter-status :status="statusFilter.responded"/>
        </div>

        <base-checkbox v-model="statusFilter.reviewed.active">
            {{$t("filter.status.reviewed")}}
        </base-checkbox>
        <div class="filter-property-wrapper" v-if="statusFilter.reviewed.active">
            <filter-status :status="statusFilter.reviewed"/>
        </div>
    </div>
</template>

<script>

    import FilterStatus from './FilterStatus'
    import BaseCheckbox from './BaseCheckbox'
    
    export default {
        props: {
            statusFilter: {
                type: Object,
                required: true
            }
        },
        components: {
            FilterStatus,
            BaseCheckbox
        }
    }
</script>