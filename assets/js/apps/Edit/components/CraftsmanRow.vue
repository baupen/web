<template>
    <tr :class="{'table-warning': !isValid}">
        <td v-for="editableField in editableFields" :key="craftsmanContainer.craftsman.id + editableField">
            <text-edit-field
                    v-model="craftsmanContainer.craftsman[editableField]"
                    :placeholder="editableFieldPlaceholders[editableField]"
                    :edit-enabled="editActive && editField === editableField"
                    @start-edit="startEdit(craftsmanContainer, editableField)"
                    @stop-edit="stopEdit"
                    @backward="changeEditField(-1)"
                    @forward="changeEditField(1)"
            />
        </td>
        <td>{{craftsmanContainer.craftsman.issueCount}}</td>
        <td>
            <button class="btn btn-danger" v-if="craftsmanContainer.craftsman.canRemove"
                    @click="$emit('remove', craftsmanContainer)">
                <font-awesome-icon :icon="['fal', 'trash']"/>
            </button>
        </td>
    </tr>
</template>

<script>
    import TextEditField from "./TextEditField";

    export default {
        props: {
            craftsmanContainer: {
                type: Object,
                required: true
            },
            editActive: {
                type: Boolean,
                required: true
            }
        },
        data: function () {
            return {
                editField: null,
                editableFields: ['email', 'contactName', 'company', 'trade'],
                editableFieldPlaceholders: {
                    email: this.$t('edit_craftsmen.placeholders.email'),
                    contactName: this.$t('edit_craftsmen.placeholders.contact_name'),
                    company: this.$t('edit_craftsmen.placeholders.company'),
                    trade: this.$t('edit_craftsmen.placeholders.trade')
                }
            }
        },
        components: {
            TextEditField
        },
        methods: {
            startEdit: function (craftsmanContainer, field) {
                this.$emit('start-edit');
                this.editField = field;
            },
            stopEdit: function () {
                this.$emit('stop-edit');
                this.editField = null;

                if (this.isValid) {
                    this.$emit('save');
                }
            },
            changeEditField: function (permutation) {
                let newEditFieldIndex = this.editableFields.indexOf(this.editField) + permutation;
                const editableFieldsLength = this.editableFields.length;
                if (newEditFieldIndex < 0) {
                    newEditFieldIndex += editableFieldsLength;
                } else if (newEditFieldIndex >= editableFieldsLength) {
                    newEditFieldIndex = 0;
                }

                this.editField = this.editableFields[newEditFieldIndex];

                if (this.isValid) {
                    this.$emit('save');
                }
            }
        },
        computed: {
            isValid: function () {
                return this.editableFields.map(field => this.craftsmanContainer.craftsman[field]).filter(value => value === null || value.length === 0).length === 0;
            }
        }

    }
</script>
