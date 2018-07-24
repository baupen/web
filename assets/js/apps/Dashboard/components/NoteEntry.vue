<template>
    <div>
        <p v-if="!editActive">
            <span>{{ entry.content }}</span>
            <span class="text-secondary">
                <span v-if="entry.canEdit">
                    <font-awesome-icon class="clickable" @click="startEdit" :icon="['fal', 'pencil']"/> -
                </span>
                {{ formattedTimestamp }} - {{ entry.authorName }}
                <span v-if="entry.canEdit">
                    - <font-awesome-icon class="clickable" v-if="entry.canEdit" @click="$emit('remove')"
                                         :icon="['fal', 'trash']"/>
                </span>
            </span>
        </p>
        <div v-else>
            <BaseTextInput v-model="noteEdit"></BaseTextInput>
            <button v-if="noteEdit.length > 0"
                    class="btn" :class="{ 'btn-outline-primary': noteEdit.length > 0 }"
                    @click="save">
                {{$t("notes.actions.add_new")}}
            </button>
        </div>
    </div>
</template>

<script>
    import moment from "moment";
    import BaseTextarea from "../../components/Base/BaseTextarea";
    import BaseTextInput from "../../components/Base/BaseTextInput";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        components: {BaseTextInput, BaseTextarea},
        props: {
            entry: {
                type: Object,
                required: true
            }
        },
        data: function () {
            return {
                editActive: false,

                noteEdit: ""
            }
        },
        methods: {
            save: function () {
                this.entry.content = this.noteEdit;
                this.editActive = false;
                this.$emit('save');
            },
            startEdit: function () {
                this.noteEdit = this.entry.content;
                this.editActive = true;
            }
        },
        computed: {
            formattedTimestamp: function () {
                return moment(this.entry.timestamp).fromNow();
            }
        }
    }
</script>