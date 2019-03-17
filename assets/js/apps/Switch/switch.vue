<template>
    <div id="switch">
        <template v-if="managingConstructionSites.length > 0">
            <h2>{{$t("mine.title")}}</h2>
            <p class="text-secondary">{{$t("mine.description")}}</p>
            <atom-spinner v-if="isLoading"
                          :animation-duration="1000"
                          :size="60"
                          :color="'#ff1d5e'"
            />

            <div v-masonry :transition-duration="'0.3s'" :item-selector="'.grid-item'" :gutter="10">
                <div v-masonry-tile class="grid-item" v-for="constructionSite in managingConstructionSites">
                    <construction-site :construction-site="constructionSite"></construction-site>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="alert alert-info">
                {{$t("messages.activate_construction_site")}}
            </div>
        </template>

        <div class="vertical-spacer-big"></div>
        <h2>{{$t("all.title")}}</h2>
        <table class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("construction_site.name")}}</th>
                <th>{{$t("construction_site.address")}}</th>
                <th>{{$t("construction_site.created_at")}}</th>
                <th>{{$t("construction_site.activated")}}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="constructionSite in constructionSites">
                <td>{{constructionSite.name}}</td>
                <td>{{constructionSite.address.join(", ")}}</td>
                <td>{{formatDateTime(constructionSite.createdAt)}}</td>
                <td>
                    <span class="switch">
                        <input type="checkbox"
                               class="switch"
                               :id="'switch-' + constructionSite.id"
                               :checked="constructionSite.isConstructionManagerOf"
                               @change="toggle(constructionSite)">
                        <label :for="'switch-' + constructionSite.id"></label>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<style>
    .grid-item {
        margin-bottom: 10px;
    }
</style>

<script>
    import axios from "axios"
    import moment from "moment";
    import bAlert from 'bootstrap-vue/es/components/alert/alert'
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import ConstructionSite from './components/ConstructionSite'

    const lang = document.documentElement.lang.substr(0, 2);

    moment.locale('de');

    export default {
        data: function () {
            return {
                constructionSites: [],
                isLoading: true,
                locale: lang
            }
        },
        computed: {
            managingConstructionSites: function () {
                return this.constructionSites.filter(c => c.isConstructionManagerOf);
            }
        },
        mixins: [notifications],
        components: {
            ConstructionSite,
            bAlert,
            AtomSpinner
        },
        methods: {
            toggle: function (constructionSite) {
                if (constructionSite.isConstructionManagerOf) {
                    axios.post("/api/switch/remove_access", {
                        constructionSiteId: constructionSite.id
                    }).then(_ => {
                        constructionSite.isConstructionManagerOf = false;
                    });
                } else {
                    axios.post("/api/switch/request_access", {
                        constructionSiteId: constructionSite.id
                    }).then(_ => {
                        constructionSite.isConstructionManagerOf = true;
                    });
                }
                this.$nextTick(function () {
                    this.$redrawVueMasonry();
                });
            },
            formatDateTime: function (dateTime) {
                return moment(dateTime).locale(this.locale).fromNow();
            }
        },
        mounted() {
            // Add a response interceptor
            axios.interceptors.response.use(
                response => {
                    return response.data;
                },
                error => {
                    this.displayErrorFlash(this.$t("messages.danger.unrecoverable") + " (" + error.response.data.message + ")");
                    console.log("request failed");
                    console.log(error.response.data);
                    return Promise.reject(error);
                }
            );

            //fill register
            axios.get("/api/switch/construction_sites").then((response) => {
                this.constructionSites = response.data.constructionSites;
                this.isLoading = false;
            });
        },
    }

</script>