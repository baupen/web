<template>
    <div>
        <BaseTextarea v-model="newNote">{{$t("notes.actions.add_new")}}</BaseTextarea>
        <button  v-if="newNote.length > 0" :disabled="isLoading" class="btn"
                :class="{ 'btn-primary': newNote.length > 0, 'disabled': isLoading}">
            {{$t("notes.actions.add_new")}}
        </button>
        <atom-spinner v-if="isMounting"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <div v-else>
            <hr/>
            <note-entry v-for="entry in notes" :key="entry.id" @save="save(entry)" @remove="remove(entry)" :entry="entry"/>
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
        methods: {
            add: function () {
                this.isLoading = true;
                axios.post("/api/notes/create", {
                    "constructionSiteId": this.constructionSiteId,
                    "note": {content: newNote}
                }).then((response) => {
                    this.notes.unshift(response.data.note);
                    this.newNote = "";
                    this.isLoading = false;
                });
            },
            save: function (note) {
                axios.post("/api/notes/update", {
                    "constructionSiteId": this.constructionSiteId,
                    "note": {content: note.content}
                }).then((response) => {
                    this.notes.push(response.data.note);
                    this.newNote = "";
                });
            },
            remove: function (note) {
                axios.post("/api/notes/delete", {
                    "constructionSiteId": this.constructionSiteId,
                    "noteId": note.id
                }).then((response) => {
                    this.notes.push(response.data.note);
                    this.newNote = "";
                });
            }
        },
        mounted() {
            axios.post("/api/notes/list", {
                "constructionSiteId": this.constructionSiteId
            }).then((response) => {
                this.notes = response.data.notes;
                this.isMounting = false;
            });
        }
    }
</script>