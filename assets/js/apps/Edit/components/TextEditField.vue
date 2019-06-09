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
                {{ valueWithDefault }}
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
                currentValue: null,
                lastSavedValue: null
            }
        },
        methods: {
            confirmEdit: function () {
                this.saveChanges();
                this.$emit('stop-edit');
            },
            saveChanges: function () {
                if (this.lastSavedValue !== this.currentValue) {
                    this.lastSavedValue = this.currentValue;
                    this.$emit('input', this.lastSavedValue);
                }
            },
            abortEdit: function () {
                this.currentValue = this.value;
                this.saveChanges();
                this.$emit('stop-edit');
            },
            tabbed: function (event) {
                this.saveChanges();
                this.$emit(event.shiftKey ? 'backward' : 'forward');
            }
        },
        computed: {
            valueWithDefault: function () {
                if (this.value.trim().length === 0) {
                    return "---";
                }
                return this.value;
            }
        },
        watch: {
            value: function () {
                this.currentValue = this.value;
            },
            editEnabled: function (newValue) {
                if (newValue) {
                    //focus input on next tick
                    this.$nextTick(() => {
                        this.$refs.edit.focus();
                    });
                } else {
                    this.saveChanges();
                }
            }
        },
        mounted() {
            this.currentValue = this.value;
            this.lastSavedValue = this.value;
        }
    }
</script>
