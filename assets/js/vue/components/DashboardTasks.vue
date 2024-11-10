<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="tasks === null">
        <loading-indicator-secondary/>
      </div>
      <template v-else>
        <add-task-button :construction-manager-iri="constructionManagerIri" :construction-site="constructionSite"
                         @added="this.tasks.push($event)"/>

        <template v-if="orderedOpenTasks.length">
          <div class="mt-3 row g-2">
            <div class="col-12" v-for="task in orderedOpenTasks" :key="task['@id']">
              <task-row
                  :task="task"
                  :construction-managers="constructionManagers"
                  :construction-manager-iri="constructionManagerIri"/>
            </div>
          </div>
        </template>

        <template v-if="orderedClosedTasks.length">
          <a class="mt-3 d-inline-block" v-if="!showClosedTasks" href="" @click.prevent="showClosedTasks = true">
            {{ $tc('dashboard.show_closed_tasks', orderedClosedTasks.length) }}
          </a>
          <div class="row g-2" :class="orderedOpenTasks.length ? 'mt-5' : 'mt-3'" v-else>
            <div class="col-12" v-for="task in orderedClosedTasks" :key="task['@id']">
              <task-row :key="task['@id']" :task="task"
                        :construction-managers="constructionManagers"
                        :construction-manager-iri="constructionManagerIri"/>
            </div>
          </div>
        </template>
      </template>
    </div>
  </div>
</template>

<script>

import LoadingIndicatorSecondary from "./Library/View/LoadingIndicatorSecondary.vue";
import {api} from "../services/api";
import TaskRow from "./View/TaskRow.vue";
import AddTaskButton from "./Action/AddTaskButton.vue";

export default {
  components: {
    AddTaskButton,
    TaskRow,
    LoadingIndicatorSecondary,
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
      return this.tasks.filter(task => !task.closedAt)
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
  overflow-y: scroll;
  overflow-x: hidden;
}

</style>
