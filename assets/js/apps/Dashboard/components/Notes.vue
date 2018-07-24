<template>
    <div>
        <BaseTextarea v-model="newNote" :placeholder="$t('notes.actions.add_new')"></BaseTextarea>
        <button v-if="newNote.length > 0"
                :disabled="isLoading"
                class="btn" :class="{ 'btn-primary': newNote.length > 0, 'disabled': isLoading}"
                @click="add">
            {{$t("notes.actions.add_new")}}
        </button>
        <atom-spinner v-if="isMounting"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <div v-else>
            <hr/>
            <note-entry v-for="entry in notes" :key="entry.id"
                        @save="save(entry)"
                        @remove="remove(entry)"
                        :entry="entry"/>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    import {AtomSpinner} from 'epic-spinners'
    import NoteEntry from "./NoteEntry";
    import BaseTextarea from "../../components/Base/BaseTextarea";

    export default {
        components: {BaseTextarea, NoteEntry, AtomSpinner},
        data: function () {
            return {
                isMounting: false,
                notes: [],

                isLoading: false,
                newNote: ""
            }
        },
        props: {
            constructionSiteId: {
                type: String,
                required: true
            }
        },
        methods: {
            add: function () {
                this.isLoading = true;
                axios.post("/api/note/create", {
                    "constructionSiteId": this.constructionSiteId,
                    "note": {content: this.newNote}
                }).then((response) => {
                    this.notes.unshift(response.data.note);
                    this.newNote = "";
                    this.isLoading = false;
                });
            },
            save: function (note) {
                axios.post("/api/note/update", {
                    "constructionSiteId": this.constructionSiteId,
                    "note": {content: note.content}
                }).then((response) => {
                    const newNote = response.data.note;
                    const match = this.notes.filter(n => n.id === newNote.id);
                    match[0].content = newNote.content;
                    match[0].authorName = newNote.authorName;
                    match[0].timestamp = newNote.timestamp;
                });
            },
            remove: function (note) {
                axios.post("/api/note/delete", {
                    "constructionSiteId": this.constructionSiteId,
                    "noteId": note.id
                }).then((response) => {
                    this.notes = this.notes.filter(n => n.id !== note.id);
                });
            }
        },
        mounted() {
            axios.post("/api/note/list", {
                "constructionSiteId": this.constructionSiteId
            }).then((response) => {
                this.notes = response.data.notes;
                this.isMounting = false;
            });
        }
    }
</script>