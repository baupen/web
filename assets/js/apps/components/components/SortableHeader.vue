<template>
    <th @click="$emit('do-sort')" class="sortable" :class="{ active: sortState !== 0 }">
        <slot></slot>
        <font-awesome-icon v-if="sortState === 0" :icon="['fal', 'sort']"/>
        <font-awesome-icon v-else :icon="sortState > 0 ? 'sort-up' : 'sort-down'"/>
    </th>
</template>

<script>
    export default {
        props: {
            sortState: {
                type: Number,
                required: true
            }
        },
    }
</script>