<template>
    <span v-if="editEnabled" class="form-group" @click.exact.prevent.stop="">
        <select class="form-control form-control-sm"
                v-model="selectedTrade"
                @keyup.escape="$emit('edit-abort')"
                :ref="'trade'">
            <option v-for="trade in selectableTrades" v-bind:value="trade">
                {{ trade }}
            </option>
        </select>
        <select class="form-control form-control-sm"
                v-model="selectedCraftsman"
                @keyup.escape="$emit('edit-abort')"
                :ref="'craftsman'">
            <option v-for="craftsman in selectableCraftsmen" v-bind:value="craftsman">
                {{ craftsman.name }}
            </option>
        </select>
        <button class="btn btn-primary" @click="editConfirm">
            <slot name="save-button-content"></slot>
        </button>
        <button class="btn btn-outline-secondary" @click="$emit('edit-abort')">{{$t("abort")}}</button>
    </span>
    <div v-else class="editable" @click.exact.prevent.stop="$emit('edit-start')">
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
            editConfirm: function () {
                this.issue.craftsmanId = this.selectedCraftsman.id;
                this.$emit('edit-confirm');
            }
        },
        watch: {
            editEnabled: function () {
                //only perform operations if edit enabled
                if (!this.editEnabled) {
                    return;
                }

                //set selected entries
                if (this.issue.craftsmanId in this.craftsmanById) {
                    this.selectedCraftsman = this.craftsmanById[this.issue.craftsmanId];
                } else {
                    this.selectedCraftsman = this.craftsmen[0];
                }
                this.selectedTrade = this.selectedCraftsman.trade;

                //focus input on next tick
                this.$nextTick(() => {
                    let input = this.$refs['trade'][0];
                    input.focus();
                });
            },
            selectedTrade: function () {
                if (this.selectableCraftsmen.indexOf(this.selectedCraftsman) === -1) {
                    this.selectedCraftsman = this.selectableCraftsmen[0];
                }
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