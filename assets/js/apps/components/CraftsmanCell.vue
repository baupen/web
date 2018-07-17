<template>
    <span v-if="editEnabled" class="form-group">
        <select class="form-control form-control-sm"
                v-model="selectedTrade"
                @keyup.tab="focusCraftsman"
                @keyup.escape="$emit('abort-edit')"
                :ref="'trade'">
            <option v-for="trade in selectableTrades" v-bind:value="trade">
                {{ trade }}
            </option>
        </select>
        <select class="form-control form-control-sm"
                v-model="selectedCraftsman"
                @keyup.escape="$emit('abort-edit')"
                :ref="'craftsman'">
            <option v-for="craftsman in selectableCraftsmen" v-bind:value="craftsman">
                {{ craftsman.name }}
            </option>
        </select>
        <button class="btn btn-primary" @click="confirmEdit">{{$t("save")}}</button>
        <button class="btn btn-outline-secondary" @click="$emit('abort-edit')">{{$t("abort")}}</button>
    </span>
    <div v-else class="editable">
        <span v-if="issue.craftsmanId === null">
            {{$t("no_craftsman_set")}}
        </span>
        <span v-else-if="!(issue.craftsmanId in this.craftsmanById)">
            {{$t("craftsman_not_found")}}
        </span>
        <span v-else>
            {{ craftsmanTrade}}<br/>
            {{ craftsmanName}}
        </span>
    </div>
</template>


<script>
    export default {
        props: {
            issue: {
                type: Object,
                required: true
            },
            craftsmen: {
                type: Array,
                required: true
            },
            editEnabled: {
                type: Boolean,
                required: true
            }
        },
        data: function () {
            return {
                selectedTrade: null,
                selectedCraftsman: null
            }
        },
        methods: {
            focusCraftsman: function () {
                this.$nextTick(() => {
                    let input = this.$refs['craftsman'][0];
                    input.focus();
                });
            },
            confirmEdit: function () {
                this.issue.craftsmanId = this.selectedCraftsman.id;
                this.$emit('confirm-edit');
            }
        },
        watch: {
            editEnabled: function () {
                //only perform operations if edit enabled
                if (!this.editEnabled) {
                    return;
                }

                //set trade selected
                if (this.issue.craftsmanId in this.craftsmanById) {
                    this.selectedTrade = this.craftsmanById[this.issue.craftsmanId].trade;
                } else {
                    this.selectedTrade = this.craftsmanById[this.craftsmen[0].id].trade;
                }

                //focus input on next tick
                this.$nextTick(() => {
                    let input = this.$refs['trade'][0];
                    input.focus();
                });
            }
        },
        computed: {
            selectableCraftsmen: function () {
                return this.craftsmen.filter(c => c.trade === this.selectedTrade);
            },
            selectableTrades: function () {
                return this.craftsmen.map(c => c.trade).unique();
            },
            craftsmanTrade: function () {
                if (this.issue.craftsmanId in this.craftsmanById) {
                    return this.craftsmanById[this.issue.craftsmanId].trade;
                }
                return "-";
            },
            craftsmanName: function () {
                if (this.issue.craftsmanId in this.craftsmanById) {
                    return this.craftsmanById[this.issue.craftsmanId].name;
                }
                return "-";
            },
            craftsmanById: function () {
                let res = [];
                this.craftsmen.forEach(c => res[c.id] = c);
                return res;
            }
        }
    }

</script>