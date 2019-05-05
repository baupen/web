<template>
    <div>
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab"
                   aria-controls="nav-home" aria-selected="true">{{$t("edit_maps.title")}}</a>
                <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab"
                   aria-controls="nav-profile" aria-selected="false">{{$t("edit_map_files.title")}}</a>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" ref="mapView" id="nav-home" role="tabpanel"
                 aria-labelledby="nav-home-tab">
                <map-view class="pt-2"
                          :map-containers="mapContainers"
                          :map-file-containers="mapFileContainers"
                          @maps-reorder="emitIfTabActive(0, 'maps-reorder')"
                          @map-add="emitIfTabActive(0, 'map-add', arguments[0])"
                          @map-save="emitIfTabActive(0, 'map-save', arguments[0])"
                          @map-remove="emitIfTabActive(0, 'map-remove', arguments[0])"
                />
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <map-file-view class="pt-2"
                               :map-containers="mapContainers"
                               :map-file-containers="mapFileContainers"
                               @file-dropped="emitIfTabActive(1, 'map-file-dropped', arguments[0])"
                               @start-upload="emitIfTabActive(1, 'map-file-start-upload', arguments[0])"
                               @abort-upload="emitIfTabActive(1, 'map-file-abort-upload', arguments[0])"
                               @save="emitIfTabActive(1, 'map-file-save', arguments[0])"
                />
            </div>
        </div>
    </div>
</template>

<style>
    .tab-content {
        border-left: 1px solid #ddd;
        border-right: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        padding: 1em;
    }

    .nav-tabs {
        margin-bottom: 0;
    }
</style>

<script>
    import moment from "moment";
    import MapView from "./MapView";
    import MapFileView from "./MapFileView";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        props: {
            mapFileContainers: {
                type: Array,
                required: true
            },
            mapContainers: {
                type: Array,
                required: true
            }
        },
        components: {
            MapFileView,
            MapView
        },
        methods: {
            emitIfTabActive(tabIndex, eventName, argument) {
                if (this.$refs.mapView.classList.contains("active")) {
                    if (tabIndex === 0) {
                        this.$emit(eventName, argument);
                    }
                } else if (tabIndex === 1) {
                    this.$emit(eventName, argument);
                }
            }
        }
    }

</script>
