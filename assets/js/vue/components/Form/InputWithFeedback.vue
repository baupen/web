<template>
  <div>
    <input :id="id" class="form-control" :class="{'is-valid': dirty && isValid, 'is-invalid': dirty && !isValid }"
           ref="input"
           @blur="dirty = true"
           :type="type" :required="required"
           v-model="localModelValue"
           @input="emitUpdate">
    <div class="invalid-feedback" v-if="dirty && !isValid">
      <span v-if="required && !localModelValue">{{ $t('validation.required') }}<br/></span>
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
    type: {
      type: String,
      default: "text"
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
