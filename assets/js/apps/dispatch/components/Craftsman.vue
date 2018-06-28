<template>
    <div class="col-md-5">
        {{ craftsman.name }}
    </div>
</template>


<script>
    import format from 'date-fns/format'

    export default {
        props: {
            craftsman: {
                type: Object,
                required: true
            }
        },
        methods: {
            formatDateTime: function (date) {
                return format(date, ["DD.MM.YYYY HH:mm"])
            },
            displayFrontendUser: function (user) {
                if (user == null) {
                    return null;
                }
                return user.fullName;
            }
        }
    }
</script>