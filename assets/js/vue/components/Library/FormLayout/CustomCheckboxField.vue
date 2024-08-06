<template>
  <div class="mb-3">
    <custom-checkbox :for-id="forId" :label="label" :label-icon="labelIcon" :label-icon-checked="labelIconChecked" :checked="checked" :secondary-label="secondaryLabel" :required="required">
      <slot></slot>
      <template v-slot:after>
        <div v-if="showReset">
          <a class="btn-link clickable" @click="$emit('reset')">
            {{ $t('_form.reset') }}
          </a>
        </div>
        <slot name="after"></slot>
      </template>
    </custom-checkbox>
  </div>
</template>

<script>

import CustomCheckbox from '../FormInput/CustomCheckbox'
export default {
  emits: ['reset'],
  components: {
    CustomCheckbox
  },
  props: {
    forId: {
      type: String,
      required: false
    },
    label: {
      type: String,
      required: false
    },
    labelIcon: {
      type: Array,
      required: false
    },
    labelIconChecked: {
      type: Array,
      required: false
    },
    checked: {
      type: Boolean,
      required: false
    },
    secondaryLabel: {
      type: String,
      required: false
    },
    required: {
      type: Boolean,
      default: false
    },
    showReset: {
      type: Boolean,
      default: false
    }
  }
}
</script>
