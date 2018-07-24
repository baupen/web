<template>
    <b-card class="text-center">
        <a class="card-text" :href="link">
            <h3>{{ number }}</h3>
            <p class="card-text">{{description}}</p>
        </a>
    </b-card>
</template>

<script>
    import bCard from 'bootstrap-vue/es/components/card/card'

    export default {
        props: {
            number: {
                type: Number,
                required: true
            },
            description: {
                type: String,
                required: true
            },
            link: {
                type: String,
                required: true
            }
        },
        components: {
            bCard
        }
    }
</script>