<template>
    <div>
        <h4 class="clickable" @click="toggleFilter" :class="{'mark' : filter.enabled }">{{$t("issue.status")}}</h4>

        <div v-if="filter.enabled">
            <div class="form-group">
                <base-checkbox v-model="filter.registered">
                    {{$t("issue.status_values.registered")}}
                </base-checkbox>

                <base-checkbox v-model="filter.read">
                    {{$t("issue.status_values.read")}}
                </base-checkbox>

                <base-checkbox v-model="filter.responded">
                    {{$t("issue.status_values.responded")}}
                </base-checkbox>

                <base-checkbox v-model="filter.reviewed">
                    {{$t("issue.status_values.reviewed")}}
                </base-checkbox>
            </div>
        </div>
    </div>
</template>

<script>
    import BaseCheckbox from '../../../components/Base/BaseCheckbox'
    export default {
        props: {
            filter: {
                type: Object,
                required: true
            }
        },
        components: {
            BaseCheckbox
        },
        methods: {
            toggleFilter: function () {
                this.filter.enabled = !this.filter.enabled;
            }
        }
    }
</script>

<style>
</style>