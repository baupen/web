<template>
    <div>
        <h4 class="clickable" @click="enabled = !enabled" :class="{'mark' : enabled }">{{$t("export.pdf.name")}}</h4>

        <div v-if="enabled">
            <base-checkbox v-model="reportElements.withImages">
                {{$t("export.pdf.with_images")}}
            </base-checkbox>

            <p class="small">{{$t("export.pdf.tables.name")}}</p>

            <base-checkbox v-model="reportElements.tables.tableByCraftsman">
                {{$t("export.pdf.tables.table_by_craftsman")}}
            </base-checkbox>
            <base-checkbox v-model="reportElements.tables.tableByTrade">
                {{$t("export.pdf.tables.table_by_trade")}}
            </base-checkbox>
            <base-checkbox v-model="reportElements.tables.tableByMap">
                {{$t("export.pdf.tables.table_by_map")}}
            </base-checkbox>

            <a :href="url" class="btn btn-primary" target="_blank">
                {{$t("export.pdf.generate")}}
            </a>
        </div>
    </div>
</template>

<script>

    import FilterStatus from './FilterStatus'
    import BaseCheckbox from './BaseCheckbox'
    import $ from 'jquery'

    export default {
        props: {
            filter: {
                type: Object,
                required: true
            }
        },
        data: function () {
            return {
                id: null,
                enabled: false,
                reportElements: {
                    tables: {
                        tableByCraftsman: true,
                        tableByTrade: false,
                        tableByMap: false
                    },
                    withImages: true
                }
            }
        },
        computed: {
            url: function () {
                let newObj = {};
                newObj["filter"] = this.filter;
                newObj["reportElements"] = this.reportElements;
                return "/report" + "?" + $.param(newObj);
            }
        },
        components: {
            FilterStatus,
            BaseCheckbox
        },
        mounted() {
            //get unique id of component for id attribute
            this.id = this._uid;
        }
    }
</script>

<style>
</style>