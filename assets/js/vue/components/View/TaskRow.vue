<template>
  <div class="row">
    <div class="col-auto">
      <toggle-open-closed-task :construction-manager-iri="constructionManagerIri" :task="task" />
    </div>
    <div class="col">
      <p class="mb-0">{{ task.description }}</p>
      <p class="text-secondary mb-0">
        <template v-if="task.closedAt">
          {{ $t('task.closed_by')}}
          {{ closedByName }}<span>, </span>
          <date-time-human-readable :value="task.closedAt"/>
        </template>
        <template v-else>
          {{ $t('task.created_by')}}
          {{ createdByName }}
        </template>
      </p>
    </div>
    <div class="col-auto col-deadline" v-if="showDeadline">
      <date-human-readable :value="task.deadline"/>
    </div>
  </div>
</template>

<script>

import {constructionManagerFormatter, entityFormatter} from "../../services/formatters";
import DateTimeHumanReadable from "../Library/View/DateTimeHumanReadable.vue";
import DateHumanReadable from "../Library/View/DateHumanReadable.vue";
import ToggleOpenClosedTask from "../Action/ToggleOpenClosedTask.vue";

export default {
  components: {ToggleOpenClosedTask, DateTimeHumanReadable, DateHumanReadable},
  props: {
    task: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Object,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: false
    },
    showDeadline: {
      type: Boolean,
      default: true
    },
  },
  computed: {
    createdByName: function () {
      const createdBy = this.constructionManagers.find(cm => cm['@id'] === this.task.createdBy)
      return createdBy ? constructionManagerFormatter.name(createdBy) : null
    },
    closedByName: function () {
      const closedBy = this.constructionManagers.find(cm => cm['@id'] === this.task.closedBy)
      return closedBy ? constructionManagerFormatter.name(closedBy) : null
    },
  },
}
</script>
