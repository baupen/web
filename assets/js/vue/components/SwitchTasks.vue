<template>
  <div class="card">
    <div class="card-body limited-height">
      <div v-if="tasks === null" class="loading-center">
        <loading-indicator-secondary/>
      </div>
      <template v-else-if="sortedDeadlines.length ===0">
        <p class="mb-0">
          keine
        </p>
      </template>
      <template v-else>
        <div class="row g-5">
          <div class="col-12" v-for="deadline in sortedDeadlines" :key="deadline">
            <h2><date-human-readable :value="deadline" :hide-current-year="true"/></h2>
            <div class="row g-3">
              <div class="col-12" v-for="task in deadlineGroupedTasks[deadline]" :key="task['@id']">
                <h3>
                  {{constructionSites.find(site => site['@id'] === task.constructionSite).name}}
                </h3>
                <task-row :task="task"
                          :construction-managers="constructionManagers"
                          :construction-manager-iri="constructionManagerIri"/>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>

import LoadingIndicatorSecondary from "./Library/View/LoadingIndicatorSecondary.vue";
import {addNonDuplicatesById, api, iriToId} from "../services/api";
import TaskRow from "./View/TaskRow.vue";
import AddTaskButton from "./Action/AddTaskButton.vue";
import DateHumanReadable from "./Library/View/DateHumanReadable.vue";

export default {
  components: {
    DateHumanReadable,
    AddTaskButton,
    TaskRow,
    LoadingIndicatorSecondary,
  },
  data() {
    return {
      tasks: null,
      isLoading: true,
      loadedUntilWeek: 0,
    }
  },
  props: {
    constructionSites: {
      type: Array,
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
    deadlineGroupedTasks: function () {
      const deadlineGroupTasks = {}
      this.tasks.forEach(task => {
        if (!(task.deadline in deadlineGroupTasks)) {
          deadlineGroupTasks[task.deadline] = []
        }
        deadlineGroupTasks[task.deadline].push(task)
      })

      Object.keys(deadlineGroupTasks).forEach(key => {
        deadlineGroupTasks[key].sort((a, b) => {
          const aConstructionSite = this.constructionSites.find(site => site['@id'] === a.constructionSite)
          const bConstructionSite = this.constructionSites.find(site => site['@id'] === b.constructionSite)

          return aConstructionSite.name.localeCompare(bConstructionSite.name)
        })
      })

      return deadlineGroupTasks
    },
    sortedDeadlines: function () {
      const deadlines = Object.keys(this.deadlineGroupedTasks)
      deadlines.sort((a, b) => a.localeCompare(b))

      return deadlines
    }
  },
  methods: {
    startLoadMore: function () {
      if (this.loadedUntilWeek === 1) {
        this.loadedUntilWeek += 3
      } else {
        this.loadedUntilWeek += 4
      }
    },
    loadTasks: function (untilWeek) {
      this.isLoading = true

      const today = new Date();
      let query = {
        'order[deadline]': "desc",
        'exists[closedAt]': 'false',
        'exists[deadline]': 'true',
        'constructionSite': this.constructionSites.map(site => iriToId(site['@id']))
      };
      if (this.loadedUntilWeek > 0) {
        const alreadyLoadedDeadline = new Date(today);
        alreadyLoadedDeadline.setDate(alreadyLoadedDeadline.getDate() + 7 * this.loadedUntilWeek);
        query['deadline[after]'] = alreadyLoadedDeadline;
      }
      const newLoadedDeadline = new Date(today);
      newLoadedDeadline.setDate(newLoadedDeadline.getDate() + 7 * untilWeek);
      query['deadline[before]'] = newLoadedDeadline;

      api.getTasksQuery(query)
          .then(entries => {
            if (this.tasks) {
              addNonDuplicatesById(this.tasks, entries)
            } else {
              this.tasks = entries
            }
            this.loadedUntilWeek = untilWeek
            this.isLoading = false
          })
    }
  },
  mounted() {
    this.loadTasks(1)
  }
}
</script>

<style scoped>
.limited-height {
  max-height: 30em;
}

</style>
