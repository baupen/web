<template>
    <b-card
            :title="constructionSite.name"
            :img-src="constructionSite.imageMedium"
            :img-alt="constructionSite.name"
            img-top
    >
        <p>
            <span class="pre">{{constructionSite.address.join("\n") }}</span>
        </p>
        <div slot="footer">
            <small class="text-muted">
                {{ constructionSite.constructionManagers.join(", ")}}
            </small>
        </div>
        <b-button variant="primary" :href="constructionSite.switchLink">
            {{$t("actions.switch")}}
        </b-button>
    </b-card>
</template>

<style>
    .pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: inherit;
    }
</style>

<script>
    import bCard from 'bootstrap-vue/es/components/card/card'
    import bButton from 'bootstrap-vue/es/components/button/button'

    export default {
        props: {
            constructionSite: {
                type: Object,
                required: true
            }
        },
        components: {
            bCard,
            bButton
        }
    }
</script>