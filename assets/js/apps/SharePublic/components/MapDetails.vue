<template>
    <div class="map-wrapper">
        <h2>{{map.name}}</h2>
        <p v-if="map.context !== ''" class="text-secondary"> {{ map.context }} </p>
        <div class="card-columns">
            <div v-if="this.map.imageShareView !== null" class="card">
                <img class="card-img clickable" :src="this.map.imageShareView"
                     @click.prevent="$emit('open-lightbox', this.map.imageFull)">
            </div>
            <issue-details v-for="issue in map.issues" v-bind:key="issue.id"
                           :issue="issue"
                           @open-lightbox="$emit('open-lightbox', arguments[0])"
            />
        </div>
    </div>
</template>

<script>
    import IssueDetails from './IssueDetails'

    export default {
        props: {
            map: {
                type: Object,
                required: true
            }
        },
        components: {
            IssueDetails
        }
    }
</script>