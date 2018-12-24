<template>
    <div id="edit">
        <p>
            <button class="btn btn-primary"
                    @click="$emit('craftsman-add', (newContainer) => startEdit(newContainer, 'contactName'))">
                {{$t("edit_craftsmen.actions.add_craftsman")}}
            </button>
        </p>

        <table v-if="orderedCraftsmanContainers.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("craftsman.contact_name")}}</th>
                <th>{{$t("craftsman.email")}}</th>
                <th>{{$t("craftsman.company")}}</th>
                <th>{{$t("craftsman.trade")}}</th>
                <th class="minimal-width">{{$t('issue_count')}}</th>
                <th class="minimal-width"></th>
            </tr>
            </thead>
            <tbody>

            <tr v-for="craftsmanContainer in orderedCraftsmanContainers" :key="craftsmanContainer.craftsman.id">
                <td v-for="editableField in editableFields" :key="craftsmanContainer.craftsman.id + editableField">
                    <text-edit-field
                            v-model="craftsmanContainer.craftsman[editableField]"
                            :edit-enabled="editCraftsmanContainer === craftsmanContainer && editField === editableField"
                            @start-edit="startEdit(craftsmanContainer, editableField)"
                            @stop-edit="stopEdit"
                            @backward="changeEditField(-1)"
                            @forward="changeEditField(1)"
                    />
                </td>
                <td>{{craftsmanContainer.craftsman.issueCount}}</td>
                <td>
                    <button class="btn btn-danger" v-if="craftsmanContainer.craftsman.issueCount === 0"
                            @click="$emit('craftsman-remove', craftsmanContainer)">
                        <font-awesome-icon :icon="['fal', 'trash']"/>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import moment from "moment";
    import MapTableRow from "./MapTableRow";
    import MapFileView from "./MapFileView";
    import TextEditField from "./TextEditField";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        props: {
            craftsmanContainers: {
                type: Array,
                required: true
            }
        },
        data: function () {
            return {
                locale: lang,
                editCraftsmanContainer: null,
                editField: null,
                editableFields: ['contactName', 'email', 'company', 'trade']
            }
        },
        components: {
            TextEditField,
            MapTableRow
        },
        methods: {
            startEdit: function (craftsmanContainer, field) {
                this.editCraftsmanContainer = craftsmanContainer;
                this.editField = field;

                this.$emit('craftsman-save', craftsmanContainer);
            },
            stopEdit: function () {
                this.editCraftsmanContainer = null;
                this.editField = null;
            },
            changeEditField: function (permutation) {
                let newEditFieldIndex = this.editableFields.indexOf(this.editField) + permutation;
                const editableFieldsLength = this.editableFields.length;
                if (newEditFieldIndex < 0) {
                    newEditFieldIndex += editableFieldsLength;

                    // go to previous craftsman
                    let newCraftsmanIndex = this.orderedCraftsmanContainers.indexOf(this.editCraftsmanContainer) - 1;
                    this.editCraftsmanContainer = newCraftsmanIndex >= 0 ? this.orderedCraftsmanContainers[newCraftsmanIndex] : null;
                } else if (newEditFieldIndex >= editableFieldsLength) {
                    newEditFieldIndex -= editableFieldsLength;

                    // go to next craftsman
                    let newCraftsmanIndex = this.orderedCraftsmanContainers.indexOf(this.editCraftsmanContainer) + 1;
                    this.editCraftsmanContainer = newCraftsmanIndex < this.orderedCraftsmanContainers.length ? this.orderedCraftsmanContainers[newCraftsmanIndex] : null;
                }

                this.editField = this.editableFields[newEditFieldIndex];
            }
        },
        computed: {
            orderedCraftsmanContainers: function () {
                return this.craftsmanContainers
                    .filter(m => m.pendingChange !== "remove")
                    .sort((mf1, mf2) => mf1.craftsman.contactName.localeCompare(mf2.craftsman.contactName));
            }
        }
    }

</script>