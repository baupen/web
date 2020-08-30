<template>
    <div>
        <h2>{{$t("export.name")}}</h2>
        <div class="card">
            <div class="card-body">
                <link-export :filter="filter" :construction-site-id="constructionSiteId"/>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <pdf-export :filter="filter" :construction-site-id="constructionSiteId"/>
            </div>
        </div>
    </div>
</template>

<script>
    import LinkExport from "./IssueExport/LinkExport";
    import PdfExport from "./IssueExport/PdfExport";

    export default {
        components: {PdfExport, LinkExport},
        props: {
            filter: {
                type: Object,
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
