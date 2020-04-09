<template>
    <div>
        <div class="form-inline">
            <div class="form-group">
                <input type="email" v-model="newEmail" :placeholder="$t('construction_manager.email')" class="form-control">
            </div>
            <button type="submit" @click="addConstructionManager" class="btn btn-primary"> {{$t("edit_external_construction_managers.actions.add")}}</button>
        </div>

        <table v-if="orderedConstructionManagers.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("construction_manager.email")}}</th>
                <th class="minimal-width"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="constructionManager in orderedConstructionManagers"
                :key="constructionManager.id">
                <td>{{constructionManager.email}}</td>
                <td>
                    <button class="btn btn-danger" @click="$emit('remove', constructionManager)">
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
    import ImportView from "./ImportView";
    import CraftsmanRow from "./CraftsmanRow";
    import uuid from "uuid/v4"

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        props: {
            externalConstructionManagers: {
                type: Array,
                required: true
            }
        },
        data: function () {
            return {
                locale: lang,
                newEmail: "",
                initialOrderedConstructionManagers: [],
            }
        },
        components: {
            CraftsmanRow,
            ImportView,
            MapTableRow
        },
        methods: {
            addConstructionManager: function () {
                const constructionManager = {
                    id: uuid(),
                    email: this.newEmail,
                };

                this.initialOrderedConstructionManagers.unshift(constructionManager);
                this.$emit('add', constructionManager);
                this.newEmail = "";
            },
        },
        computed: {
            orderedConstructionManagers: function () {
                const initialIds = new Set(this.externalConstructionManagers.map(c => c.id));
                return this.initialOrderedConstructionManagers.filter(entry => initialIds.has(entry.id));
            }
        },
        mounted() {
            this.initialOrderedConstructionManagers = this.externalConstructionManagers
                .filter(m => m.pendingChange !== "remove")
                .sort((mf1, mf2) => mf1.email.localeCompare(mf2.email));
        }
    }

</script>
