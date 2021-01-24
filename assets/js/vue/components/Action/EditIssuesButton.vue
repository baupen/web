<template>
  <button-with-modal-confirm
      :title="$t('edit_issues_button.modal_title')" color="secondary" :can-confirm="editedFields.length > 0"
      :confirm-title="storeIssuesText" :button-disabled="disabled"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" class="pr-1"/>
      {{ $t('edit_issues_button.modal_title') }}

    </template>
    <div>

      <custom-checkbox-field for-id="is-marked" :label="$t('issue.is_marked')">
        <input
            class="custom-control-input" type="checkbox" id="is-marked"
            :class="{'is-valid': fields.isMarked.dirty && !fields.isMarked.errors.length, 'is-invalid': fields.isMarked.dirty && fields.isMarked.errors.length }"
            v-model="issue.isMarked"
            :true-value="true"
            :false-value="false"
            :indeterminate.prop="issue.isMarked === null"
            @input="fields.isMarked.dirty = true"
            @change="validate('isMarked')"
        >
        <template v-slot:after>
          <div>
            <a class="btn-link clickable" v-if="fields.isMarked.dirty" @click="reset('isMarked')">
              {{ $t('edit_issues_button.actions.reset') }}
            </a>
          </div>
        </template>
      </custom-checkbox-field>

      <custom-checkbox-field for-id="was-added-with-client" :label="$t('issue.was_added_with_client')">
        <input
            class="custom-control-input" type="checkbox" id="was-added-with-client"
            :class="{'is-valid': fields.wasAddedWithClient.dirty && !fields.wasAddedWithClient.errors.length, 'is-invalid': fields.wasAddedWithClient.dirty && fields.wasAddedWithClient.errors.length }"
            v-model="issue.wasAddedWithClient"
            :true-value="true"
            :false-value="false"
            :indeterminate.prop="issue.wasAddedWithClient === null"
            @input="fields.wasAddedWithClient.dirty = true"
            @change="validate('wasAddedWithClient')"
        >
        <template v-slot:after>
          <div>
            <a class="btn-link clickable" v-if="fields.wasAddedWithClient.dirty" @click="reset('wasAddedWithClient')">
              {{ $t('edit_issues_button.actions.reset') }}
            </a>
          </div>
        </template>
      </custom-checkbox-field>

      <hr/>

      <form-field for-id="description" :label="$t('issue.description')">
        <input id="description" class="form-control" type="text" required="required"
               :class="{'is-valid': fields.description.dirty && !fields.description.errors.length, 'is-invalid': fields.description.dirty && fields.description.errors.length }"
               v-model="issue.description"
               @change="validate('description')"
               @input="fields.description.dirty = true">
        <invalid-feedback :errors="fields.description.errors"/>
        <a class="btn-link clickable" v-if="fields.description.dirty" @click="reset('description')">
          {{ $t('edit_issues_button.actions.reset') }}
        </a>
      </form-field>

      <form-field for-id="craftsman" :label="$t('issue.craftsman')">
        <select class="custom-select"
                v-model="tradeFilter">
          <option :value="null">{{ $t('edit_issues_button.no_trade_filter') }}</option>
          <option disabled></option>
          <option v-for="trade in sortedTrade" :value="trade">
            {{ trade }}
          </option>
        </select>
        <select class="custom-select"
                :class="{'is-valid': fields.craftsman.dirty && !fields.craftsman.errors.length, 'is-invalid': fields.craftsman.dirty && fields.craftsman.errors.length }"
                v-model="issue.craftsman"
                @input="fields.craftsman.dirty = true"
                @change="validate('craftsman')"
        >
          <option v-if="!tradeFilter" :value="null">{{ $t('edit_issues_button.no_craftsman') }}</option>
          <option v-if="!tradeFilter" disabled></option>
          <option v-for="craftsman in sortedCraftsmen" :value="craftsman['@id']"
                  :key="craftsman['@id']">
            {{ craftsman.company }} - {{ craftsman.contactName }}
          </option>
        </select>
        <invalid-feedback :errors="fields.description.errors"/>
        <a class="btn-link clickable" v-if="fields.craftsman.dirty" @click="reset('craftsman')">
          {{ $t('edit_issues_button.actions.reset') }}
        </a>
      </form-field>

      <form-field for-id="deadline" :label="$t('issue.deadline')">
        <span ref="deadline-anchor"/>
        <flat-pickr
            id="deadline" class="form-control"
            v-model="issue.deadline"
            @input="fields.deadline.dirty = true"
            @change="validate('deadline')"
            :config="datePickerConfig">
        </flat-pickr>
        <invalid-feedback :errors="fields.deadline.errors"/>
        <a class="btn-link clickable" v-if="fields.deadline.dirty" @click="reset('deadline')">
          {{ $t('edit_issues_button.actions.reset') }}
        </a>
      </form-field>

      <hr/>

      <form-field for-id="image" :label="$t('issue.image')">
        <dropzone
            v-if="!image"
            id="image" :help="$t('edit_issues_button.image_drop_or_choose')"
            :valid-file-types="validFileTypes"
            @input="image = $event[0]" />
        <input v-if="image" id="image" class="form-control is-valid" type="text" readonly="readonly"
               :value="image.name">
        <a class="btn-link clickable" v-if="image" @click="image = null">
          {{ $t('edit_issues_button.actions.reset') }}
        </a>
      </form-field>

    </div>

  </button-with-modal-confirm>
</template>

<script>

import {dateConfig, flatPickr} from "../../services/flatpickr";
import {createField, resetFields, validateField} from "../../services/validation";
import {validImageTypes} from "../../services/api";
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import Dropzone from '../Library/FormInput/Dropzone'

export default {
  emits: ['save', 'save-image'],
  components: {
    Dropzone,
    InvalidFeedback,
    FormField,
    CustomCheckboxField,
    ButtonWithModalConfirm,
    flatPickr
  },
  data() {
    return {
      fields: {
        isMarked: createField(),
        wasAddedWithClient: createField(),
        description: createField(),
        craftsman: createField(),
        deadline: createField()
      },
      tradeFilter: null,
      isMarkedIndeterminate: false,
      issue: null,
      image: null
    }
  },
  props: {
    issues: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
    disabled: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    storeIssuesText: function () {
      if (this.editedFields.length === 0) {
        return this.$tc('edit_issues_button.actions.save_issues', this.issues.length, {'count': this.issues.length})
      }

      let translatedEditedFields = this.editedFields.map(field => this.$t('issue.' + field.replace(/([A-Z])/g, "_$1").toLowerCase()))
      let fields = translatedEditedFields.join(", ")
      return this.$tc('edit_issues_button.actions.save_issue_fields', this.issues.length, {
        'count': this.issues.length,
        'fields': fields
      })
    },
    validFileTypes: function () {
      return validImageTypes;
    },
    datePickerConfig: function () {
      return dateConfig;
    },
    selectableCraftsmen: function () {
      return this.craftsmen.filter(c => !c.isDeleted)
    },
    sortedCraftsmen: function () {
      let selectableCraftsmen = this.selectableCraftsmen
      if (this.tradeFilter) {
        selectableCraftsmen = selectableCraftsmen.filter(c => c.trade === this.tradeFilter)
      }

      return selectableCraftsmen.sort((a, b) => a.company.localeCompare(b.company))
    },
    sortedTrade: function () {
      const tradeSet = new Set(this.selectableCraftsmen.map(c => c.trade))

      return Array.from(tradeSet).sort()
    },
    editedFields: function () {
      let editedFields = []
      for (let field in this.fields) {
        if (Object.prototype.hasOwnProperty.call(this.fields, field)) {
          if (this.fields[field].dirty) {
            editedFields.push(field)
          }
        }
      }

      if (this.image) {
        editedFields.push('image')
      }

      return editedFields
    },
    unionIssue: function () {
      const sameValue = (field) => {
        if (this.issues.length === 0) {
          return null;
        }

        let defaultValue = this.issues[0][field]
        if (!this.issues.every(i => i[field] === defaultValue)) {
          return null;
        }

        return defaultValue;
      }

      return {
        isMarked: sameValue('isMarked'),
        wasAddedWithClient: sameValue('wasAddedWithClient'),
        description: sameValue('description'),
        craftsman: sameValue('craftsman'),
        deadline: sameValue('deadline')
      }
    }
  },
  watch: {
    sortedCraftsmen: function () {
      if (this.sortedCraftsmen.length === 1) {
        this.issue.craftsman = this.sortedCraftsmen[0]['@id']
        this.fields.craftsman.dirty = true
      }
    },
    unionIssue: function () {
      this.issue = Object.assign({}, this.unionIssue);
      if (this.issue.craftsman) {
        this.tradeFilter = this.craftsmen.find(c => c['@id'] === this.issue.craftsman).trade
      } else {
        this.tradeFilter = null
      }

      this.$nextTick(() => {
        resetFields(this.fields)
      })
    },
    'fields.deadline.dirty': function () {
      if (!this.$refs['deadline-anchor']) {
        return
      }

      const visibleInput = this.$refs['deadline-anchor'].parentElement.childNodes[4]
      if (this.fields.deadline.dirty) {
        visibleInput.classList.add('is-valid');
      } else {
        visibleInput.classList.remove('is-valid');
      }
    }
  },
  methods: {
    validate: function (field) {
      validateField(this.fields[field], this.issue[field])
    },
    reset: function (field) {
      this.fields[field].dirty = false
      this.issue[field] = this.unionIssue[field]
    },
    confirm: function () {
      let editedFields = this.editedFields
      if (editedFields.includes('image')) {
        this.$emit('save-image', this.image)
        this.image = null;

        editedFields.splice(editedFields.indexOf('image'), 1);
      }

      if (editedFields.length > 0) {
        const payload = {}
        this.editedFields.forEach(field => {
          payload[field] = this.issue[field]
        })

        // set empty datetime to null
        if (payload.deadline === "") {
          payload.deadline = null;
        }

        this.$emit('save', payload)
      }
    }
  },
  mounted() {
    this.issue = Object.assign({}, this.unionIssue);
  }
}
</script>
