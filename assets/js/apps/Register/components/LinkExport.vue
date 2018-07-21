<template>
    <div>
        <h4 class="clickable" @click="enabled = !enabled" :class="{'mark' : enabled }">{{$t("export.link.name")}}</h4>

        <div v-if="enabled">
            <div class="form-group">
                <base-checkbox v-model="linkLimit.enabled">
                    {{$t("export.link.with_limit")}}
                </base-checkbox>

                <template v-if="linkLimit.enabled">
                    <base-date-input v-model="linkLimit.limit">
                        {{$t("export.link.valid_till")}}
                    </base-date-input>
                </template>
            </div>

            <p>
                <button class="btn btn-primary" :class="{'disabled' : isLoading}" @click="generateLink">
                    {{$t("export.link.generate")}}
                </button>
            </p>

            <input v-if="link !== null" class="form-control" :value="link"/>
        </div>
    </div>
</template>

<script>

    import FilterStatus from './FilterStatus'
    import BaseCheckbox from '../../components/Base/BaseCheckbox'
    import BaseDateInput from '../../components/Base/BaseDateInput'
    import $ from 'jquery'
    import axios from "axios"

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
                linkLimit: {
                    enabled: false,
                    limit: null
                },
                link: null,
                isLoading: false
            }
        },
        methods: {
            generateLink: function () {
                this.isLoading = true;

                let newObj = {};
                newObj["filter"] = this.filter;
                newObj["limit"] = this.linkLimit;

                axios.get("/api/register/link/create?" + $.param(newObj)).then((response) => {
                    this.link = response.data.link;
                    this.isLoading = false;
                });
            }
        },
        watch: {
            filter: {
                handler() {
                    this.link = null;
                },
                deep: true
            },
            linkLimit: {
                handler() {
                    this.link = null;
                },
                deep: true
            }
        },
        computed: {
            url: function () {
            }
        },
        components: {
            FilterStatus,
            BaseCheckbox,
            BaseDateInput
        },
        mounted() {
            //get unique id of component for id attribute
            this.id = this._uid;
        }
    }
</script>

<style>
</style>