<template>
    <div>
        <h4 class="clickable" @click="enabled = !enabled" :class="{'mark' : enabled }">{{$t("export.pdf.name")}}</h4>

        <div v-if="enabled">
            <div class="form-group">
                <base-checkbox v-model="reportElements.withImages">
                    {{$t("export.pdf.with_images")}}
                </base-checkbox>
            </div>

            <div class="form-group">

                <label class="small">{{$t("export.pdf.tables.name")}}</label>

                <base-checkbox v-model="reportElements.tables.tableByCraftsman">
                    {{$t("export.pdf.tables.table_by_craftsman")}}
                </base-checkbox>
                <base-checkbox v-model="reportElements.tables.tableByTrade">
                    {{$t("export.pdf.tables.table_by_trade")}}
                </base-checkbox>
                <base-checkbox v-model="reportElements.tables.tableByMap">
                    {{$t("export.pdf.tables.table_by_map")}}
                </base-checkbox>
            </div>

            <p>
                <a :href="url" class="btn btn-primary" target="_blank">
                    {{$t("export.pdf.generate")}}
                </a>
            </p>
        </div>
    </div>
</template>

<script>
    import BaseCheckbox from '../../../components/Base/BaseCheckbox'
    import NormalizeFilter from '../../mixins/NormalizeFilter'
    import $ from 'jquery'

    export default {
        mixins: [NormalizeFilter],
        props: {
            filter: {
                type: Object,
                required: true
            },
            constructionSiteId: {
                type: String
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
                newObj["filter"] = this.minimizeFilter(this.filter, this.constructionSiteId);
                newObj["reportElements"] = this.reportElements;
                return "/report" + "?" + $.param(newObj);
            }
        },
        components: {
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