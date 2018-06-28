<template>
    <div id="dispatch">
        <div v-if="craftsmen.length > 0">
            <Craftsman
                    v-for="craftsman in craftsmen"
                    v-bind:key="craftsman.id"
                    v-bind:craftsman="craftsman">
            </Craftsman>
        </div>
        <div v-else-if="!isLoading">
            <p>{{ $t("no_craftsmen") }}</p>
        </div>
    </div>
</template>

<script>
    import Craftsman from "./components/Craftsman"
    import axios from "axios"

    export default {
        data() {
            return {
                craftsmen: [],
                isLoading: true,
                constructionSiteId: null
            }
        },
        components: {
            Craftsman
        },
        mounted() {
            // Add a response interceptor
            axios.interceptors.response.use(
                response => {
                    return response.data;
                },
                error => {
                    console.log(error.response.data.message);
                    return Promise.reject(error);
                }
            );

            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;
                console.log(this.constructionSiteId);
                axios.post("/api/dispatch/craftsman/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.craftsmen = response.data.craftsmen;
                    this.isLoading = false;
                });
            });
        },
    }

</script>