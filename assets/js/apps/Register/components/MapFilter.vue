<template>
    <div>
        <h4 class="clickable" @click="toggleFilter" :class="{'mark' : filter.enabled }">{{$t("issue.map")}}</h4>

        <div v-if="filter.enabled">
            <map-filter-level :filter="filter" :maps="maps"/>
        </div>
    </div>
</template>

<script>

    import FilterStatus from './FilterStatus'
    import BaseCheckbox from '../../components/Base/BaseCheckbox'
    import MapFilterLevel from './MapFilterLevel'

    export default {
        props: {
            filter: {
                type: Object,
                required: true
            },
            maps: {
                type: Array,
                required: true
            }
        },
        data: function () {
            return {
                id: null
            }
        },
        components: {
            FilterStatus,
            BaseCheckbox,
            MapFilterLevel
        },
        methods: {
            toggleFilter: function () {
                this.filter.enabled = !this.filter.enabled;
            }
        },
        mounted() {
            //get unique id of component for id attribute
            this.id = this._uid;
        }
    }
</script>

<style>
</style>