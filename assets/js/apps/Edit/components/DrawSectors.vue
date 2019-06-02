<template>
    <div>
        <h1>{{$t("draw.sectors.create_sectors")}}</h1>
        <p>{{$t("draw.sectors.create_sectors_description")}}</p>
        <div ref="sizing" class="no-overflow">
            <div class="main-content">

            </div>
            <div class="sidebar">

            </div>
        </div>
        <div class="no-overflow">
            <canvas ref="canvas" class="canvas"></canvas>
            <div class="sidebar">

            </div>
        </div>

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
        width: calc(100vw - 4em);

        display: flex;
        align-content: stretch;
    }

    .main-content {
        height: 100%;
        flex-grow: 1;
        background-color: red;
    }

    .sidebar {
        width: 50em;
        background-color: green;
        height: 100%;
    }
</style>

<script>
    export default {
        props: {
            mapFile: {
                type: Object,
                required: true
            },
            sectors: {
                type: Array,
                required: true
            }
        },
        data: function () {
            return {
                canvas: null,
                backgroundImage: null,
                activeSector: null,
                points: []
            }
        },
        methods: {
            redrawCanvas: function () {
                this.prepareCanvas(this.canvas);
                this.drawBackgroundImage(this.canvas, this.backgroundImage);
                this.drawSectors(this.canvas, this.sectors);
                this.drawActiveSector(this.canvas, this.points);
            },
            drawSectors: function (canvas, sectors) {
                let ctx = canvas.getContext('2d');
                let width = canvas.width;
                let height = canvas.height;
                sectors.forEach(sector => {
                    ctx.fillStyle = this.convertHexToRgbWithOpacity(sector.color, 0.6);
                    ctx.beginPath();
                    ctx.moveTo(sector.points[0].x * width, sector.points[0].y * height);
                    for (let i = 1; i < sector.points.length; i++) {
                        ctx.lineTo(sector.points[i].x * width, sector.points[i].y * height);
                    }
                    ctx.closePath();
                    ctx.fill();
                });
            },
            drawActiveSector: function (canvas, points) {
                if (points.length === 0) {
                    return;
                }

                const size = 20;

                let ctx = canvas.getContext('2d');
                let width = canvas.width;
                let height = canvas.height;
                ctx.fillStyle = "#ef0000";

                const xStart = points[0].x * width;
                const yStart = points[0].y * height;
                console.log(xStart, yStart);
                ctx.fillRect(xStart - size / 2, yStart * height - size / 2, size, size);
            },
            convertHexToRgbWithOpacity: function (hex, opacity) {
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? "rgba(" + parseInt(result[1], 16) + "," + parseInt(result[2], 16) + "," + parseInt(result[3], 16) + "," + opacity + ")" : null;
            },
            drawBackgroundImage: function (canvas, backgroundImage) {
                let ctx = canvas.getContext('2d');
                ctx.drawImage(backgroundImage, 0, 0, canvas.width, canvas.height);
            },
            initializeCanvas: function () {
                let canvas = this.canvas;

                let containerWidth = this.$refs.sizing.children[0].clientWidth;
                let containerHeight = this.$refs.sizing.children[0].clientHeight;
                this.$refs.sizing.remove();

                let imageWidth = this.backgroundImage.naturalWidth;
                let imageHeight = this.backgroundImage.naturalHeight;

                let imageAspectRatio = imageWidth / imageHeight;
                let canvasAspectRatio = containerWidth / containerHeight;

                let width = containerWidth;
                let height = containerHeight / imageAspectRatio * canvasAspectRatio;

                // set canvas size
                if (imageAspectRatio < canvasAspectRatio) {
                    height = containerHeight;
                    width = containerWidth / canvasAspectRatio * imageAspectRatio;
                }

                canvas.height = height;
                canvas.width = width;

                canvas.style.height = canvas.height;
                canvas.style.width = canvas.width;

                this.redrawCanvas();

                canvas.addEventListener("click", this.onClickCanvas, false);
            },
            onClickCanvas: function (args) {
                let canvas = this.canvas;

                var rect = canvas.getBoundingClientRect();
                const point = {
                    x: (args.clientX - rect.left) / rect.width,   // scale mouse coordinates after they have
                    y: (args.clientY - rect.top) / rect.height     // been adjusted to be relative to element
                };

                this.points.push(point);

                this.redrawCanvas();
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

            // create background image
            const backgroundImage = new Image();

            // draw first time when image is load
            let initializeCanvas = this.initializeCanvas;
            backgroundImage.onload = function () {
                initializeCanvas();
            };

            backgroundImage.src = '/api/map_file/' + this.mapFile.id + '/image/sector_frame';
            this.backgroundImage = backgroundImage;

        }
    }

</script>
