<template>
    <tr @click="$emit('clicked-row')">
        <td>
            {{map.name}}<br/>
            <span class="small">{{map.context}}</span>
        </td>
        <td>{{ issuesWithoutResponse.length }}</td>
        <td>{{ nextResponseLimit }}</td>
    </tr>
</template>

<script>
    import moment from 'moment'

    const lang = document.documentElement.lang.substr(0, 2);

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
        data() {
            return {
                locale: lang
            }
        },
        methods: {
            formatDateTime: function (value) {
                return value === null ? "-" : moment(value).locale(this.locale).fromNow();
            }
        },
        computed: {
            issuesWithoutResponse: function () {
                return this.map.issues.filter(i => this.issuesWithResponse.indexOf(i) === -1);
            },
            nextResponseLimit: function () {
                let currentResponseLimit = null;
                this.issuesWithoutResponse.forEach(i => {
                    if (currentResponseLimit === null || (i.responseLimit !== null && i.responseLimit < currentResponseLimit)) {
                        currentResponseLimit = i.responseLimit;
                    }
                });

                if (currentResponseLimit === null) {
                    return "-"
                }
                return this.formatDateTime(currentResponseLimit);
            }
        }
    }
</script>