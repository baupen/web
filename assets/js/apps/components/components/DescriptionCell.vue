<template>
    <span v-if="editEnabled" class="form-group" @click.exact.prevent.stop="">
        <input type="text" ref="description" @keyup.enter="editConfirm" @keyup.esc="$emit('edit-abort')" v-model="description" class="form-control form-control-sm" />
        <button class="btn btn-primary" @click="editConfirm">
            <slot name="save-button-content"></slot>
        </button>
        <button class="btn btn-outline-secondary" @click="$emit('edit-abort')">{{$t("actions.abort")}}</button>
    </span>
    <div v-else class="editable" @click.exact.prevent.stop="$emit('edit-start')">
        <span>
            {{ issue.description }}
        </span>
    </div>
</template>


<script>
    export default {
        props: {
            issue: {
                type: Object,
                required: true
            },
            editEnabled: {
                type: Boolean,
                required: true
            }
        },
        data: function () {
            return {
                description: null
            }
        },
        methods: {
            editConfirm: function () {
                this.issue.description = this.description;
                this.$emit('edit-confirm');
            }
        },
        watch: {
            editEnabled: function () {
                //only perform operations if edit enabled
                if (!this.editEnabled) {
                    return;
                }

                this.description = this.issue.description;

                //focus input on next tick
                this.$nextTick(() => {
                    let input = this.$refs.description;
                    input.focus();
                });
            }
        }
    }

</script>