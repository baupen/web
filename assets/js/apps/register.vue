<template>
    <div id="register">
        <issue-edit-table :is-mounting="isLoading" :craftsmen="craftsmen" :issues="issues"></issue-edit-table>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import IssueEditTable from "./components/IssueEditTable"
    import {de} from 'vuejs-datepicker/dist/locale'

    moment.locale('de');

    Array.prototype.unique = function () {
        return Array.from(new Set(this));
    };

    export default {
        data: function () {
            return {
                issues: [],
                isLoading: true,
                craftsmen: []
            }
        },
        components: {
            IssueEditTable
        },
        mounted() {
            // Add a response interceptor
            axios.interceptors.response.use(
                response => {
                    return response.data;
                },
                error => {
                    this.displayErrorFlash(this.$t("error") + " (" + error.response.data.message + ")");
                    console.log("request failed");
                    console.log(error.response.data);
                    return Promise.reject(error);
                }
            );
            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;

                axios.post("/api/register/issue/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.issues = response.data.issues;
                    this.isLoading = false;
                });

                axios.post("/api/register/craftsman/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.craftsmen = response.data.craftsmen;
                });
            });
        },
    }

</script>

<style>
    .filter-field {
        max-width: 400px;
    }

    .editable {
        display: inline-block;
        border: 1px solid rgba(0, 0, 0, 0)
    }

    .editable:hover {
        border: 1px solid
    }

    .clickable {
        cursor: pointer;
    }

    .file-upload-field > .form-control {
        width: 100%;
        padding: 1rem;
        margin: 0.5rem 0;
    }

    input[type=checkbox] {
        transform: scale(1.4);
    }

    .form-control-preselected {
        padding: 0.5rem;
    }
</style>