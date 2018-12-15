<template>
    <fragment>
        <tr>
            <td :class="'map-indent map-indent-' + indentSize">
                {{map.name}}
            </td>
            <td>
                <select v-model="map.parentId">
                    <option v-for="map in maps.filter(m => m !== map)" :value="map.id">{{map.name}}</option>
                </select>
            </td>
            <td>{{map.fileId}}</td>
            <td>{{map.preventAutomaticEdit}}</td>
            <td>{{map.issueCount}}</td>
        </tr>
        <template v-for="child in childMaps">
            <map-tree-level :map="child" :maps="maps" :indent-size="indentSize + 1"/>
        </template>
    </fragment>
</template>

<script>
    import bCard from 'bootstrap-vue/es/components/card/card'
    import bButton from 'bootstrap-vue/es/components/button/button'

    export default {
        name: 'map-tree-level',
        props: {
            map: {
                type: Object,
                required: true
            },
            maps: {
                type: Array,
                required: true
            },
            indentSize: {
                type: Number,
                required: true
            }
        },
        components: {
            bCard,
            bButton
        },
        computed: {
            childMaps: function () {
                return this.maps.filter(m => m.parentId === this.map.id);
            }
        }
    }
</script>