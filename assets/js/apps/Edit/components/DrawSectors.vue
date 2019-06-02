<template>
    <div>
        <h1>{{$t("draw.sectors.create_sectors")}}</h1>
        <p>{{$t("draw.sectors.create_sectors_description")}}</p>
        <canvas ref="canvas" class="no-overflow">

        </canvas>

        <div class="mt-3">
            <button class="btn btn-outline-primary float-left" @click="$emit('draw-outline')">
                {{$t("draw.sector_frame.draw_outline")}}
            </button>
            <button class="btn btn-primary float-right" @click="$emit('close')">{{$t("draw.actions.close")}}</button>
        </div>
    </div>
</template>

<style scoped>
    .no-overflow {
        height: calc(100vh - 25em);
        width: calc(100vh - 4em);
    }
</style>

<script>
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
                canvas: null,
                backgroundImage: null
            }
        },
        methods: {
            drawSectors: function () {
                this.prepareCanvas(this.canvas);
                this.drawBackgroundImage(this.canvas);

                let ctx = this.canvas.getContext('2d');
                ctx.fillStyle = '#f00';
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.lineTo(100, 50);
                ctx.lineTo(50, 100);
                ctx.lineTo(0, 90);
                ctx.closePath();
                ctx.fill();
            },
            drawBackgroundImage: function(canvas) {
                let ctx = canvas.getContext('2d');
                ctx.drawImage(this.backgroundImage, 0, 0, canvas.width, canvas.height);
            },
            initializeCanvas: function(canvas) {
                // Set up CSS size.
                canvas.style.width = canvas.clientWidth;
                canvas.style.height = canvas.clientHeight;

                // Resize canvas and scale future draws.
                let scaleFactor = 6;
                canvas.width = Math.ceil(canvas.width * scaleFactor);
                canvas.height = Math.ceil(canvas.height * scaleFactor);
            },
            prepareCanvas: function (canvas) {
                // clear content
                let ctx = canvas.getContext('2d');
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                ctx.imageSmoothingEnabled = false;
            }
        },
        mounted() {
            this.canvas = this.$refs.canvas;
            this.initializeCanvas(this.canvas);

            // create background image
            const backgroundImage = new Image();

            // draw first time when image is load
            let drawSectors = this.drawSectors;
            backgroundImage.onload = function () {
                drawSectors();
            };

            backgroundImage.src = '/api/map_file/' + this.mapFile.id + '/image/sector_frame';
            this.backgroundImage = backgroundImage;

        }
    }

</script>
