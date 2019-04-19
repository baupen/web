<template>
    <div>
        <h2>{{$t("filter.name")}}</h2>
        <div class="card">
            <div class="card-body">
                <base-checkbox v-model="filter.onlyMarked">
                    {{$t("search.only_marked")}}
                </base-checkbox>
                <base-checkbox v-model="filter.onlyOverLimit">
                    {{$t("search.only_over_limit")}}
                </base-checkbox>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <status-filter :filter="filter.status"/>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <craftsman-filter :filter="filter.craftsman" :craftsmen="craftsmen"/>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <trade-filter :filter="filter.trade" :craftsmen="craftsmen"/>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <map-filter :filter="filter.map" :maps="maps"/>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <time-filter :filter="filter.time"/>
            </div>
        </div>
    </div>
</template>

<script>
    import BaseCheckbox from '../../components/Base/BaseCheckbox'
    import StatusFilter from "./IssueFilter/StatusFilter";
    import CraftsmanFilter from "./IssueFilter/CraftsmanFilter";
    import TradeFilter from "./IssueFilter/TradeFilter";
    import MapFilter from "./IssueFilter/MapFilter";
    import TimeFilter from "./IssueFilter/TimeFilter";

    export default {
        components: {TimeFilter, MapFilter, TradeFilter, CraftsmanFilter, StatusFilter, BaseCheckbox},
        props: {
            filter: {
                type: Object,
                required: true
            },
            craftsmen: {
                type: Array,
                required: true
            },
            maps: {
                type: Array,
                required: true
            },
            constructionSiteId: {
                type: String
            }
        }
    }
</script>

<style>
</style>