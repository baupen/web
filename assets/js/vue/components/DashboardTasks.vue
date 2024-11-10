<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="tasks === null">
        <loading-indicator-secondary/>
      </div>
      <template v-else>
        <add-task-button :construction-manager-iri="constructionManagerIri" :construction-site="constructionSite"
                         @added="this.tasks.push($event)"/>

        <template v-if="tasks.length">
          <div class="mt-3 row g-2">
            <div class="col-12" v-for="task in orderedTasks" :key="task['@id']">
              <task-row
                  :task="task"
                  :construction-managers="constructionManagers"
                  :construction-manager-iri="constructionManagerIri"
                  @removed="removedTask(task)"
              />
            </div>
          </div>
        </template>

        <p class="text-center mb-0 mt-5" v-if="!closedTasks && !loadingClosedTasks">
          <button class="btn btn-outline-secondary" @click="loadClosedTasks">
            {{ $tc('dashboard.load_closed_tasks') }}
          </button>
        </p>
        <div class="loading-center mt-5" v-if="loadingClosedTasks">
          <loading-indicator-secondary/>
        </div>

        <div class="row g-2" v-if="closedTasks?.length" :class="tasks.length ? 'mt-5' : 'mt-3'">
          <div class="col-12" v-for="task in closedTasks" :key="task['@id']">
            <task-row :key="task['@id']" :task="task"
                      :construction-managers="constructionManagers"
                      :construction-manager-iri="constructionManagerIri"
            />
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>

import LoadingIndicatorSecondary from "./Library/View/LoadingIndicatorSecondary.vue";
import {api, iriToId} from "../services/api";
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
      closedTasks: null,
      loadingClosedTasks: false
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
    orderedTasks: function () {
      const openTasks = [...this.tasks]
      openTasks.sort((a, b) => (a.deadline).localeCompare(b.deadline))
      return openTasks
    },
  },
  methods: {
    loadClosedTasks: function () {
      this.loadingClosedTasks = true;

      const query = {
        'order[closedAt]': 'desc',
        'exists[closedAt]': 'true',
        'constructionSite': iriToId(this.constructionSite['@id'])
      };

      api.getTasksQuery(query)
          .then(entries => {
            this.closedTasks = []
            entries.forEach(add => {
              if (!this.tasks.find(o => o['@id'] === add['@id'])) {
                this.closedTasks.push(add)
              }
            })

            this.loadingClosedTasks = false
          })
    },
    removedTask: function (task) {
      this.tasks = this.tasks.filter(candidate => candidate !== task)
      this.closedTasks = this.closedTasks.filter(candidate => candidate !== task)
    }
  },
  mounted() {
    const query = {
      'order[deadline]': "desc",
      'exists[closedAt]': 'false',
      'constructionSite': iriToId(this.constructionSite['@id'])
    };

    api.getTasksQuery(query)
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

.loading-center > * {
  display: block;
  margin: 0 auto;
}
</style>
