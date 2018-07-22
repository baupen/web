<template>
    <div>
        <h4 @click="toggleFilter" class="clickable" :class="{'mark' : filter.enabled }">{{$t("issue.status")}}</h4>
        <div v-if="filter.enabled">
            <base-checkbox v-model="filter.registered.active">
                {{$t("issue.status_values.registered")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.registered.active">
                <status-filter-entry :status="filter.registered" :allow-value-choose="false"/>
            </div>

            <base-checkbox v-model="filter.read.active">
                {{$t("issue.status_values.read")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.read.active">
                <status-filter-entry :status="filter.read" :allow-date-choose="false"/>
            </div>

            <base-checkbox v-model="filter.responded.active">
                {{$t("issue.status_values.responded")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.responded.active">
                <status-filter-entry :status="filter.responded"/>
            </div>

            <base-checkbox v-model="filter.reviewed.active">
                {{$t("issue.status_values.reviewed")}}
            </base-checkbox>
            <div class="filter-property-wrapper" v-if="filter.reviewed.active">
                <status-filter-entry :status="filter.reviewed"/>
            </div>
        </div>
    </div>
</template>

<script>

    import StatusFilterEntry from './StatusFilterEntry'
    import BaseCheckbox from '../../components/Base/BaseCheckbox'

    export default {
        props: {
            filter: {
                type: Object,
                required: true
            }
        },
        components: {
            StatusFilterEntry,
            BaseCheckbox
        },
        methods: {
            toggleFilter: function () {
                this.filter.enabled = !this.filter.enabled;
            }
        }
    }
</script>