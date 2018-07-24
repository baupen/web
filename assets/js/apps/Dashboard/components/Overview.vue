<template>

    <b-card-group>
        <number-tile link="/register?view=open"
                     :number="overview.openIssuesCount"
                     :description="$t('register.status_actions.open')"/>

        <number-tile link="/register?view=overdue"
                     :number="overview.overdueIssuesCount"
                     :description="$t('register.status_actions.overdue')"/>

        <number-tile link="/register?view=to_inspect"
                     :number="overview.respondedNotReviewedIssuesCount"
                     :description="$t('register.status_actions.to_inspect')"/>

        <number-tile link="/register?view=marked"
                     :number="overview.markedIssuesCount"
                     :description="$t('register.status_actions.marked')"/>
    </b-card-group>
</template>

<script>
    import bCardGroup from 'bootstrap-vue/es/components/card/card-group'
    import NumberTile from "./NumberTile";

    export default {
        props: {
            overview: {
                type: Object,
                required: true
            }
        },
        components: {
            bCardGroup,
            NumberTile
        }
    }
</script>