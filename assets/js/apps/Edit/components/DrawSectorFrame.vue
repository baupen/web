<template>
    <div>
        <h1>Grundriss umranden</h1>
        <p>Grundriss umrangen, um einfacher Bereiche einzuzeichnen (ohne Materialinformationen o. Ä.)</p>
        <img ref="image" class="no-overflow" :src="'/api/map_file/' + this.mapFile.id + '/image'" alt="map file image">

        <button class="btn btn-primary mt-3 float-right" @click="updateFrame">weiter</button>
        <button class="btn btn-primary mt-3 float-right" @click="reset">rtese</button>
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
            reset: function() {
                const imageData = this.cropper.getImageData();

                const imageHeight = imageData.naturalHeight;
                const imageWidth = imageData.naturalWidth;

                const payload = {
                    x: this.frame.startX * imageWidth,
                    y: this.frame.startY * imageHeight,
                    width: this.frame.width * imageWidth,
                    height: this.frame.height * imageHeight,
                };

                this.cropper.setData(payload);
            },
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

                this.$emit("save-frame", frame);
            }
        },
        mounted() {
            let imageElement = this.$refs.image;
            let cropper = new Cropper(imageElement, {
                viewMode: 1, // do not exceed canvas

                // do not move, rotate, scale or zoom the image
                movable: false,
                rotatable: false,
                scalable: false,
                zoomable: false,
            });

            this.cropper = cropper;
            let reset = this.reset;

            imageElement.addEventListener('ready', function () {
                if (this.cropper === cropper) {
                    reset();
                }
            });
        }
    }

</script>
