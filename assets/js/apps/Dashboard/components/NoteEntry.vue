<template>
    <div>
        <p v-if="!editActive">
            <span v-html="parsedContent"></span>
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

            <div class="input-group">
                <input type="text" @keyup.enter="save" v-model="noteEdit" class="form-control form-control-sm">
                <span class="input-group-btn input-group-btn">
                <button :disabled="noteEdit.length === 0"
                        class="btn btn-sm btn-outline-primary" :class="{ 'disabled': noteEdit.length === 0}"
                        @click="save" type="button">
                    {{$t("actions.save")}}</button>
            </span>
            </div>
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
            },
            parsedContent: function () {
                let start = "";
                let inIssue = false;
                let issueId = "";
                this.entry.content += ' ';
                this.entry.content.split('').forEach(c => {
                    if (inIssue) {
                        if (c === ' ') {
                            inIssue = false;
                            if (issueId.length > 0) {
                                start += '<a href="/register?issue=' + issueId + '">#' + issueId + '</a> ';
                                issueId = "";
                            } else {
                                start += '#' + c;
                            }
                        } else if (c >= '0' && c <= '9') {
                            issueId += c;
                        } else {
                            inIssue = false;
                            start += '#' + issueId;
                            issueId = "";
                        }
                    } else {
                        if (c === '#') {
                            inIssue = true;
                        } else {
                            start += c;
                        }
                    }
                });
                start += issueId;
                return start.trim();
            }
        }
    }
</script>