<template>
    <a :href="link">
        <h3>{{ number }}</h3>
        <p>{{description}}</p>
    </a>
</template>

<script>
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
        }
    }
</script>