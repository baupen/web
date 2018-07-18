<template>
    <div>
        <h4 class="clickable" @click="toggleFilter" :class="{'mark' : filter.enabled }">{{$t("issue.map")}}</h4>

        <div v-if="filter.enabled">
            <div v-for="map in maps" v-bind:key="map.id" class="custom-control custom-checkbox">
                <input type="checkbox"
                       class="custom-control-input"
                       :id="'map' + map.id + id"
                       v-model="filter.maps" :value="map">
                <label class="custom-control-label" :for="'map' + map.id + id">
                    {{ map.name }}
                </label>
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
            BaseCheckbox
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