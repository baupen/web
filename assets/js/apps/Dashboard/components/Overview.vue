<template>
    <div>
        <h2>{{$t("register.name")}}</h2>
        <b-alert :show="overview.newIssuesCount > 0" variant="warning">
            <h4>{{ $tc("dialog.new_issues_in_foyer", overview.newIssuesCount, {count: overview.newIssuesCount})}}</h4>
            <p><a href="/foyer">{{$t("dialog.add_to_register")}}</a></p>
        </b-alert>
        <b-card-group>
            <number-tile link="/register?view=open"
                         :number="overview.openIssuesCount"
                         :description="$t('register.status_actions.open')"/>

            <number-tile link="/register?view=overdue"
                         :number="overview.overdueIssuesCount"
                         :description="$t('register.status_actions.overdue')"/>
        </b-card-group>
        <b-card-group>
            <number-tile link="/register?view=to_inspect"
                         :number="overview.respondedNotReviewedIssuesCount"
                         :description="$t('register.status_actions.to_inspect')"/>

            <number-tile link="/register?view=marked"
                         :number="overview.markedIssuesCount"
                         :description="$t('register.status_actions.marked')"/>
        </b-card-group>
    </div>
</template>

<script>
    import bCardGroup from 'bootstrap-vue/es/components/card/card-group'
    import bAlert from 'bootstrap-vue/es/components/alert/alert'
    import NumberTile from "./NumberTile";
    import axios from 'axios'
    import {AtomSpinner} from 'epic-spinners'

    export default {
        data() {
            return {
                isMounting: true,
                constructionSite: Object
            }
        },
        props: {
            overview: {
                type: Object,
                required: true
            },
            constructionSiteId: String
        },
        components: {
            bCardGroup,
            NumberTile,
            AtomSpinner,
            bAlert
        },
        mounted() {
            axios.post("/api/dashboard/constructionSite", {
                "constructionSiteId": this.constructionSiteId
            }).then((response) => {
                this.constructionSite = response.data.constructionSite;
                this.isMounting = false;
            });
        }
    }
</script>