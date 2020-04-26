<template>
    <div>
        <p>
            <button class="btn btn-primary"
                    @click="addCraftsman">
                {{$t("edit_craftsmen.actions.add_craftsman")}}
            </button>
            <button class="btn btn-outline-primary" @click="importViewActive = !importViewActive">
                <span v-if="!importViewActive">
                    {{$t("edit_craftsmen.actions.show_import")}}
                </span>
                <span v-else>
                    {{$t("edit_craftsmen.actions.hide_import")}}
                </span>
            </button>
        </p>

        <import-view
                v-if="importViewActive"
                :craftsman-containers="craftsmanContainers"
                @save="$emit('save', arguments[0])"
                @remove="$emit('remove', arguments[0])"
                @close="importViewActive = false"
        />
        <table v-if="orderedCraftsmanContainers.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("craftsman.email")}}</th>
                <th>{{$t("craftsman.contact_name")}}</th>
                <th>{{$t("craftsman.company")}}</th>
                <th>{{$t("craftsman.trade")}}</th>
                <th class="minimal-width">{{$t('issue_count')}}</th>
                <th class="minimal-width"></th>
            </tr>
            </thead>
            <tbody>

            <craftsman-row v-for="craftsmanContainer in orderedCraftsmanContainers"
                           :key="craftsmanContainer.craftsman.id"
                           :craftsman-container="craftsmanContainer"
                           :edit-active="editCraftsmanContainer === craftsmanContainer"
                           @start-edit="startEdit(craftsmanContainer)"
                           @stop-edit="stopEdit"
                           @save="$emit('save', craftsmanContainer)"
                           @remove="$emit('remove', craftsmanContainer)"
            />
            </tbody>
        </table>
    </div>
</template>

<script>
    import moment from "moment";
    import MapTableRow from "./MapTableRow";
    import ImportView from "./ImportView";
    import CraftsmanRow from "./CraftsmanRow";
    import uuid from "uuid/v4"

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
                importViewActive: false,
                initialOrderedCraftsmanContainers: [],
            }
        },
        components: {
            CraftsmanRow,
            ImportView,
            MapTableRow
        },
        methods: {
            addCraftsman: function () {
                const newContainer = {
                    new: true,
                    craftsman: {
                        id: uuid(),
                        contactName: "",
                        email: "",
                        company: "",
                        trade: "",
                        issueCount: 0,
                        canRemove: true
                    }
                };

                this.initialOrderedCraftsmanContainers.unshift(newContainer);
                this.$emit('add', newContainer);
                this.startEdit(newContainer);
            },
            startEdit: function (craftsmanContainer) {
                this.editCraftsmanContainer = craftsmanContainer;
            },
            stopEdit: function () {
                this.editCraftsmanContainer = null;
            }
        },
        computed: {
            orderedCraftsmanContainers: function () {
                const craftsmanIds = new Set(this.craftsmanContainers.map(c => c.craftsman.id));
                return this.initialOrderedCraftsmanContainers.filter(container => craftsmanIds.has(container.craftsman.id));
            }
        },
        mounted() {
            this.initialOrderedCraftsmanContainers = this.craftsmanContainers
                .filter(m => m.pendingChange !== "remove")
                .sort((mf1, mf2) => mf1.craftsman.contactName.localeCompare(mf2.craftsman.contactName));
        }
    }

</script>
