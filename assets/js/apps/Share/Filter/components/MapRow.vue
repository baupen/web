<template>
    <tr @click="$emit('clicked-row')">
        <td>
            {{map.name}}<br/>
            <span class="small">{{map.context}}</span>
        </td>
        <td>{{ map.issues.filter(i => i.reviewedAt === null).length }}</td>
        <td>{{ map.issues.filter(i => i.reviewedAt !== null).length }}</td>
    </tr>
</template>

<script>
    export default {
        props: {
            map: {
                type: Object,
                required: true
            }
        }
    }
</script>