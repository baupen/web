<template>
    <div>
        <div class="form-inline">
            <div class="form-group">
                <input :class="{'is-invalid': newEmailInvalid}" id="email" type="email" v-model="newEmail" :placeholder="$t('edit_external_construction_managers.placeholders.email')" class="form-control">
            </div>
            <button type="submit" @click="addConstructionManager" class="btn btn-primary">{{$t("edit_external_construction_managers.actions.add")}}</button>
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
                newEmailInvalid: false
            }
        },
        components: {
            CraftsmanRow,
            ImportView,
            MapTableRow
        },
        methods: {
            addConstructionManager: function () {
                if (this.newEmail.length < 2 || this.externalConstructionManager.filter(cm => cm.email === this.newEmail).length > 0) {
                    this.newEmailInvalid = true;
                    return;
                }
                this.newEmailInvalid = false;

                const constructionManager = {
                    id: uuid(),
                    email: this.newEmail,
                };

                this.$emit('add', constructionManager);
                this.newEmail = "";
            },
        },
        computed: {
            orderedConstructionManagers: function () {
                return this.externalConstructionManagers.sort((mf1, mf2) => mf1.email.localeCompare(mf2.email));
            }
        }
    }

</script>
