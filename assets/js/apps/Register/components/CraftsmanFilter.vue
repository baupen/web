<template>
    <div>
        <h4 class="clickable" @click="toggleFilter" :class="{'mark' : filter.enabled }">{{$t("issue.craftsman")}}</h4>

        <div v-if="filter.enabled">
            <div v-for="craftsman in craftsmen" v-bind:key="craftsman.id" class="custom-control custom-checkbox">
                <input type="checkbox"
                       class="custom-control-input"
                       :id="'craftsman' + craftsman.id  + id"
                       v-model="filter.craftsmen" :value="craftsman">
                <label class="custom-control-label" :for="'craftsman' + craftsman.id  + id">
                    {{ craftsman.name }}
                </label>
            </div>
        </div>
    </div>
</template>

<script>

    import FilterStatus from './StatusFilterEntry'
    import BaseCheckbox from '../../components/Base/BaseCheckbox'

    export default {
        props: {
            filter: {
                type: Object,
                required: true
            },
            craftsmen: {
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