<template>
  <div :class="'form-group ' + sizeClassName">
    <label :for="id">{{ label }} <span v-if="required" class="text-danger">*</span></label>
    <input :id="id" class="form-control" :class="{'is-valid': dirty && isValid, 'is-invalid': dirty && !isValid }" ref="input"
           @blur="dirty = true"
           :type="type" :required="required"
           v-model="localModelValue"
           @input="emitUpdate">
    <div class="invalid-feedback" v-if="dirty && !isValid">
      <span v-if="required && !localModelValue">{{$t('validation.required')}}<br/></span>
    </div>
  </div>
</template>

<script>

export default {
  emits: ['update:modelValue', 'valid'],
  data() {
    return {
      localModelValue: "",
      dirty: false
    }
  },
  props: {
    modelValue: null,
    id: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: "text"
    },
    size: {
      type: Number,
      default: 0
    },
    required: {
      type: Boolean,
      default: true
    },
    focus: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    sizeClassName: function () {
      return (this.size !== 0) ? 'col-md-' + this.size : '';
    },
    isValid: function () {
      return !this.required || !!this.localModelValue;
    }
  },
  watch: {
    isValid: function () {
      this.emitValid();
    }
  },
  methods: {
    emitUpdate() {
      this.$emit('update:modelValue', this.localModelValue)
    },
    emitValid() {
      this.$emit('valid', this.isValid)
    }
  },
  mounted() {
    if (this.focus) {
      this.$refs.input.focus()
    }

    this.localModelValue = this.modelValue;
    this.emitValid();
  }
}
</script>

<style scoped="true">
  input:required {
    box-shadow: none;
  }
</style>
