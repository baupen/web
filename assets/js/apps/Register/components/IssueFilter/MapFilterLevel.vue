<template>
    <div>
        <div v-for="map in maps" v-bind:key="map.id" class="custom-control custom-checkbox">
            <input type="checkbox"
                   class="custom-control-input"
                   :id="'map' + map.id + id"
                   v-model="filter.maps" :value="map"
                   @change="onMapSelect(map)"
            >
            <label class="custom-control-label" :for="'map' + map.id + id">
                {{ map.name }}
            </label>
            <div class="map-level">
                <map-filter-level v-if="map.hasOwnProperty('children') && map.children.length > 0" :filter="filter" :maps="map.children" />
            </div>
        </div>
    </div>
</template>

<script>
    import MapFilterLevel from './MapFilterLevel'

    export default {
        props: {
            filter: {
                type: Object,
                required: true
            },
            maps: {
                type: Array,
                required: true
            }
        },
        name: 'map-filter-level',
        data: function () {
            return {
                id: null
            }
        },
        methods: {
            onMapSelect: function (map) {
                if (this.filter.maps.includes(map)) {
                    if (!this.someChildContained(map.children)) {
                        this.addAllWithChildren(map.children);
                    }
                } else {
                  if (this.everyChildContained(map.children)) {
                    this.removeAllWithChildren(map.children);
                  }
                }
            },
            addAllWithChildren: function(maps) {
              maps.forEach(m =>  {
                this.filter.maps.push(m);
                this.addAllWithChildren(m.children);
              });
            },
            removeAllWithChildren: function(maps) {
              maps.forEach(m =>  {
                this.filter.maps.splice(this.filter.maps.indexOf(m), 1);
                this.removeAllWithChildren(m.children);
              });
            },
            someChildContained: function(maps) {
                return maps.some(m =>  this.filter.maps.includes(m) || this.someChildContained(m.children));
            },
            everyChildContained: function(maps) {
                return maps.every(m =>  this.filter.maps.includes(m) && this.everyChildContained(m.children));
            }
        },
        components: {
            MapFilterLevel
        },
        mounted() {
            //get unique id of component for id attribute
            this.id = this._uid;
        }
    }
</script>

<style>
</style>
