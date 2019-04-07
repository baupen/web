<template>
    <div>
        <span v-if="editEnabled" class="form-group">
            <input type="text"
                   ref="edit"
                   class="form-control form-control-sm"
                   :placeholder="placeholder"
                   @keyup.enter="confirmEdit"
                   @keyup.esc="abortEdit"
                   @keydown.tab.prevent.stop="tabbed"
                   v-model="currentValue"
            />
        </span>
        <div v-else class="editable" @click="$emit('start-edit')">
            <span>
                {{ value }}
            </span>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            value: {
                type: String,
                required: true
            },
            placeholder: {
                type: String,
                required: false
            },
            editEnabled: {
                type: Boolean,
                required: true
            }
        },
        data: function () {
            return {
                currentValue: null
            }
        },
        methods: {
            confirmEdit: function () {
                if (this.value !== this.currentValue) {
                    this.$emit('input', this.currentValue);
                }
                this.$emit('stop-edit');
            },
            abortEdit: function () {
                this.currentValue = this.value;
                this.$emit('stop-edit');
            },
            tabbed: function (event) {
                if (this.value !== this.currentValue) {
                    this.$emit('input', this.currentValue);
                }
                this.$emit(event.shiftKey ? 'backward' : 'forward');
            }
        },
        watch: {
            value: function () {
                this.currentValue = this.value;
            },
            editEnabled: function () {
                if (this.editEnabled) {
                    //focus input on next tick
                    this.$nextTick(() => {
                        this.$refs.edit.focus();
                    });
                }
            }
        },
        mounted() {
            this.currentValue = this.value;
        }
    }
</script>