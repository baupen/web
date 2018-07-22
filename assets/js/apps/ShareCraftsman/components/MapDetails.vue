<template>
    <div class="map-wrapper">
        <h2>{{map.name}}</h2>
        <p v-if="map.context !== ''" class="text-secondary"> {{ map.context }} </p>
        <div class="card-columns">
            <div v-if="imageShareView !== null" class="card">
                <img class="card-img clickable" :src="imageShareView"
                     @click.prevent="$emit('open-lightbox', imageFull)">
            </div>
            <issue-details v-for="issue in map.issues" v-bind:key="issue.id"
                           :issue="issue" :issue-has-response="issuesWithResponse.indexOf(issue) >= 0"
                           @open-lightbox="$emit('open-lightbox', arguments[0])"
                           @send-response="$emit('issue-send-response', arguments[0])"
                           @remove-response="$emit('issue-remove-response', arguments[0])"
            />
        </div>
    </div>
</template>

<script>
    import IssueDetails from './IssueDetails'

    export default {
        props: {
            map: {
                type: Object,
                required: true
            },
            issuesWithResponse: {
                type: Array,
                required: true
            }
        },
        data: function () {
            return {
                imageShareView: this.map.imageShareView,
                imageFull: this.map.imageFull
            }
        },
        computed: {
            issuesWithoutResponse: function () {
                return this.map.issues.filter(i => this.issuesWithResponse.indexOf(i) === -1);
            }
        },
        methods: {
            hash: function (message) {
                // We transform the string into an arraybuffer.
                const buffer = new TextEncoder("utf-8").encode(message);
                return crypto.subtle.digest("SHA-256", buffer).then(function (hash) {
                    const hexCodes = [];
                    const view = new DataView(hash);
                    for (let i = 0; i < view.byteLength; i += 4) {
                        // Using getUint32 reduces the number of iterations needed (we process 4 bytes each time)
                        const value = view.getUint32(i);
                        // toString(16) will give the hex representation of the number without padding
                        const stringValue = value.toString(16);
                        // We use concatenation and slice for padding
                        const padding = '00000000';
                        const paddedValue = (padding + stringValue).slice(-padding.length);
                        hexCodes.push(paddedValue);
                    }

                    // Join all the hex strings into one
                    return hexCodes.join("");
                });
            }
        },
        watch: {
            issuesWithoutResponse: {
                deep: true,
                handler: function () {
                    //maps may not have an image
                    if (this.map.imageFull === null) {
                        return null;
                    }

                    //compute authentication hash
                    let str = this.map.id + "," + this.issuesWithoutResponse.map(i => i.id).join(",");
                    this.hash(str).then(hash => {
                        // cut off size & hash
                        const path = this.map.imageFull.split("/");
                        path.pop();
                        path.pop();
                        const newBaseLink = path.join("/");

                        // set refreshed links
                        this.imageShareView = newBaseLink + "/" + hash + "/share_view";
                        this.imageFull = newBaseLink + "/" + hash + "/full";
                    });
                }
            }
        },
        components: {
            IssueDetails
        }
    }
</script>