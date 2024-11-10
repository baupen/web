<template>
  <div class="row">
    <div class="col-auto">
      <toggle-open-closed-task :construction-manager-iri="constructionManagerIri" :task="task"/>
    </div>
    <div class="col">
      <p class="mb-0">{{ task.description }}</p>
      <p class="text-secondary mb-0">
        <template v-if="task.closedAt">
          {{ closedByName }}<span>, </span>
          <date-time-human-readable :value="task.closedAt"/>
        </template>
        <template v-else>
          {{ createdByName }}
        </template>
      </p>
    </div>
    <div class="col-auto col-deadline" v-if="showDeadline">
      <date-human-readable :value="task.deadline"/>
    </div>
    <div class="col-auto" v-if="enableMutations">
      <div class="btn-group">
        <edit-task-button :task="task" />
        <remove-task-button :task="task" @removed="$emit('removed')"/>
      </div>
    </div>
  </div>
</template>

<script>

import {constructionManagerFormatter} from "../../services/formatters";
import DateTimeHumanReadable from "../Library/View/DateTimeHumanReadable.vue";
import DateHumanReadable from "../Library/View/DateHumanReadable.vue";
import ToggleOpenClosedTask from "../Action/ToggleOpenClosedTask.vue";
import EditTaskButton from "../Action/EditTaskButton.vue";
import RemoveTaskButton from "../Action/RemoveTaskButton.vue";

export default {
  components: {RemoveTaskButton, EditTaskButton, ToggleOpenClosedTask, DateTimeHumanReadable, DateHumanReadable},
  emits: ['removed'],
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
    enableMutations: {
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
