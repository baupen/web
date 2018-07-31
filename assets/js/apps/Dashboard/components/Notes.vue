<template>
    <div>
        <div class="input-group">
            <input type="text" @keyup.enter="add" :disabled="isLoading" v-model="newNote" :class="{ 'disabled': isLoading }" class="form-control">
            <span class="input-group-btn">
                <button :disabled="isLoading || newNote.length === 0"
                        class="btn" :class="{ 'disabled': isLoading || newNote.length === 0}"
                        @click="add" type="button">
                    {{$t("notes.actions.add_new")}}</button>
            </span>
        </div>

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
    import BaseTextInput from "../../components/Base/BaseTextInput";

    export default {
        components: {BaseTextInput, NoteEntry, AtomSpinner},
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
                    "note": {content: note.content, id: note.id}
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