<template>
    <div>
        <h1>Grundriss umranden</h1>
        <p>Grundriss umrangen, um einfacher Bereiche einzuzeichnen (ohne Materialinformationen o. Ä.)</p>
        <img ref="image" class="no-overflow" :src="'/api/map_file/' + this.mapFile.id + '/image'" alt="map file image">

        <button class="btn btn-primary" @click="updateFrame">weiter</button>
    </div>
</template>

<style scoped>
    .no-overflow {
        max-height: calc(100vh - 25em);
        max-width: calc(100vh - 4em);
    }
</style>

<script>
    import Cropper from 'cropperjs';

    export default {
        props: {
            mapFile: {
                type: Object,
                required: true
            },
            frame: {
                type: Object
            }
        },
        data: function () {
            return {
                cropper: null
            }
        },
        methods: {
            updateFrame: function () {
                const imageData = this.cropper.getImageData();
                const cropData = this.cropper.getData();

                const imageHeight = imageData.naturalHeight;
                const imageWidth = imageData.naturalWidth;

                let frame =  {
                    startX: cropData.x / imageWidth,
                    startY: cropData.y/ imageHeight,
                    width: cropData.width / imageWidth,
                    height: cropData.height / imageHeight
                };

                this.$emit("update-frame", frame);
                this.$emit("proceed");
            }
        },
        mounted() {
            let imageElement = this.$refs.image;
            this.cropper = new Cropper(imageElement, {
                viewMode: 1, // do not exceed canvas

                // do not move, rotate, scale or zoom the image
                movable: false,
                rotatable: false,
                scalable: false,
                zoomable: false,
            });
        }
    }

</script>
