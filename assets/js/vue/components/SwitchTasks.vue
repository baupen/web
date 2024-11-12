<template>
  <div class="card shadow">
    <div class="card-body limited-height">
      <div v-if="tasks === null" class="loading-center">
        <loading-indicator-secondary/>
      </div>
      <template v-else>
        <template v-if="sortedDeadlines.length ===0">
          <p>
            {{ $t('switch.no_tasks_pending') }}
          </p>
        </template>
        <template v-else>
          <div class="row g-5 mb-5">
            <div class="col-12" v-for="deadline in sortedDeadlines" :key="deadline">
              <h3>
                <date-human-readable :value="deadline"/>
              </h3>
              <div class="row g-3">
                <div class="col-12"
                     v-for="([constructionSiteId, tasks]) in Object.entries(deadlineConstructionSiteGroupedTasks[deadline])"
                     :key="constructionSiteId">
                  <p class="mb-1">
                    <strong>
                      {{ constructionSites.find(site => site['@id'] === constructionSiteId).name }}
                    </strong>
                  </p>
                  <div class="row g-1">
                    <div class="col-12" v-for="task in tasks" :key="task['@id']">
                      <task-row :task="task"
                                :construction-managers="constructionManagers"
                                :construction-manager-iri="constructionManagerIri"
                                :show-deadline="false"
                                :enable-mutations="false"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <p class="text-secondary text-center mb-2">
          {{ $t('switch.tasks_loaded_until', {deadlineLoaded: loadedUntilDate}) }}
        </p>
        <p class="text-center mb-0">
          <button class="btn btn-outline-secondary" @click="startLoadMore" :disabled="isLoading">
            {{ $t('switch.load_more_tasks') }}
          </button>
        </p>
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
import EnterConstructionSite from "./Action/EnterConstructionSite.vue";
import moment from "moment";

export default {
  components: {
    EnterConstructionSite,
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
  watch: {
    constructionSites: {
      deep: true,
      handler: function () {
        const targetWeek = this.loadedUntilWeek
        this.loadedUntilWeek = 0
        this.isLoading = true
        this.tasks = null
        this.loadTasks(targetWeek)
      }
    }
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

      return deadlineGroupTasks
    },
    deadlineConstructionSiteGroupedTasks: function () {
      const deadlineConstructionSiteGroupedTasks = {}
      Object.keys(this.deadlineGroupedTasks).forEach(deadline => {
        let constructionSiteGroup = {}

        this.deadlineGroupedTasks[deadline].forEach(task => {
          if (!(task.constructionSite in constructionSiteGroup)) {
            constructionSiteGroup[task.constructionSite] = []
          }
          constructionSiteGroup[task.constructionSite].push(task)
        })

        Object.keys(constructionSiteGroup).forEach(key => {
          constructionSiteGroup[key].sort((a, b) => a.createdAt.localeCompare(b.createdAt))
        })

        deadlineConstructionSiteGroupedTasks[deadline] = constructionSiteGroup
      })

      return deadlineConstructionSiteGroupedTasks
    },
    sortedDeadlines: function () {
      const deadlines = Object.keys(this.deadlineGroupedTasks)
      deadlines.sort((a, b) => a.localeCompare(b))

      return deadlines
    },
    loadedUntilDate: function () {
      const today = new Date();
      const alreadyLoadedDeadline = new Date(today);
      alreadyLoadedDeadline.setDate(alreadyLoadedDeadline.getDate() + 7 * this.loadedUntilWeek);

      return moment(alreadyLoadedDeadline).format('L')
    }
  },
  methods: {
    startLoadMore: function () {
      if (this.loadedUntilWeek === 1) {
        this.loadTasks(4)
      } else {
        this.loadTasks(this.loadedUntilWeek + 4)
      }
    },
    loadTasks: function (untilWeek) {
      this.isLoading = true

      const today = new Date();
      let query = {
        'order[deadline]': "desc",
        'exists[closedAt]': 'false',
        'exists[deadline]': 'true',
        'constructionSite[]': this.constructionSites.map(site => iriToId(site['@id']))
      };
      if (this.loadedUntilWeek > 0) {
        const alreadyLoadedDeadline = new Date(today);
        alreadyLoadedDeadline.setDate(alreadyLoadedDeadline.getDate() + 7 * this.loadedUntilWeek);
        query['deadline[after]'] = alreadyLoadedDeadline.toISOString();
      }
      const newLoadedDeadline = new Date(today);
      newLoadedDeadline.setDate(newLoadedDeadline.getDate() + 7 * untilWeek);
      query['deadline[before]'] = newLoadedDeadline.toISOString();

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
  max-height: 50em;
  overflow-y: scroll;
  overflow-x: hidden;
}

</style>
