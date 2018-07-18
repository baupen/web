<template>
    <div>
        <h4 class="clickable" @click="toggleFilter" :class="{'mark' : filter.enabled }">{{$t("issue.trade")}}</h4>

        <div v-if="filter.enabled">
            <div v-for="trade in trades" v-bind:key="trade" class="custom-control custom-checkbox">
                <input type="checkbox"
                       class="custom-control-input"
                       :id="'trade' + trade + id"
                       v-model="filter.trades" :value="trade">
                <label class="custom-control-label" :for="'trade' + trade + id">
                    {{ trade }}
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
        computed: {
            trades: function() {
                return this.craftsmen.map(c => c.trade).unique();
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