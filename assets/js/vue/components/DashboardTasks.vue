<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="tasks === null">
        <loading-indicator-secondary/>
      </div>
      <template v-else>
        <add-task-button :construction-manager-iri="constructionManagerIri" :construction-site="constructionSite"
                         @added="this.tasks.push($event)"/>

        <div class="mt-3">
          <task-row class="mb-2" v-for="task in orderedOpenTasks" :key="task['@id']" :task="task"
                    :construction-managers="constructionManagers"
                    :construction-manager-iri="constructionManagerIri"/>
        </div>

        <div v-if="orderedClosedTasks.length">
          <a class="mt-3" v-if="!showClosedTasks" href="" @click.prevent="showClosedTasks = true">
            {{ $tc('dashboard.show_closed_tasks', orderedClosedTasks.length) }}
          </a>
          <div :class="orderedOpenTasks.length ? 'mt-5' : 'mt-3'" v-else>
            <task-row class="mb-2" v-for="task in orderedClosedTasks" :key="task['@id']" :task="task"
                      :construction-managers="constructionManagers"
                      :construction-manager-iri="constructionManagerIri"/>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>

import ConstructionSiteTimeline from "./View/ConstructionSiteTimeline.vue";
import LoadingIndicatorSecondary from "./Library/View/LoadingIndicatorSecondary.vue";
import {api} from "../services/api";
import TaskRow from "./View/TaskRow.vue";
import AddTaskButton from "./Action/AddTaskButton.vue";

export default {
  components: {
    AddTaskButton,
    TaskRow,
    LoadingIndicatorSecondary,
    ConstructionSiteTimeline,
  },
  data() {
    return {
      tasks: null,
      showClosedTasks: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    },
  },
  computed: {
    orderedOpenTasks: function () {
      const openTasks = this.tasks.filter(task => !task.closedAt)
      openTasks.sort((a, b) => (a.deadline ?? '').localeCompare(b.deadline ?? ''))
      return openTasks
    },
    orderedClosedTasks: function () {
      const openTasks = this.tasks.filter(task => task.closedAt)
      openTasks.sort((a, b) => (a.closedAt).localeCompare(b.closedAt))
      return openTasks
    }
  },
  mounted() {
    api.getTasks(this.constructionSite)
        .then(entries => {
          this.tasks = entries
        })
  }
}
</script>

<style scoped>
.limited-height {
  max-height: 30em;
  overflow-x: scroll;
}

</style>
