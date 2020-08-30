<template>
    <div>
        <div class="form-group mb-2">
            <label for="streetAddress">{{$t("construction_site.street_address")}}</label>
            <input type="text" v-model.lazy="$v.streetAddress.$model" @change="submit" class="form-control"
                   id="streetAddress" :class="{'is-invalid': $v.streetAddress.$dirty && $v.streetAddress.$invalid}"
                   :placeholder="$t('construction_site.street_address')">
            <div class="invalid-feedback">
                {{$t('validation.required')}}
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                <label for="postalCode">{{$t("construction_site.postal_code")}}</label>
                <input type="number" v-model.lazy="$v.postalCode.$model" @change="submit" class="form-control"
                       id="postalCode" :class="{'is-invalid': $v.postalCode.$dirty && $v.postalCode.$invalid}"
                       :placeholder="$t('construction_site.postal_code')">
                <div class="invalid-feedback">
                    {{$t('validation.required')}}
                </div>
            </div>
            <div class="col">
                <label for="locality">{{$t("construction_site.locality")}}</label>
                <input type="text" v-model.lazy="$v.locality.$model" @change="submit" class="form-control"
                       id="locality" :class="{'is-invalid': $v.locality.$dirty && $v.locality.$invalid}"
                       :placeholder="$t('construction_site.locality')">
                <div class="invalid-feedback">
                    {{$t('validation.required')}}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {required} from 'vuelidate/lib/validators'

    export default {
        data: function () {
            return {
                streetAddress: null,
                postalCode: null,
                locality: null
            }
        },
        props: {
            constructionSite: {
                type: Object,
                required: true
            }
        },
        methods: {
            submit: function () {
                this.$v.$touch();
                if (!this.$v.$invalid) {
                    this.constructionSite.streetAddress = this.streetAddress;
                    this.constructionSite.postalCode = Number.parseInt(this.postalCode);
                    this.constructionSite.locality = this.locality;
                    this.$emit("save");
                }
            },
        },
        validations: {
            streetAddress: {
                required
            },
            postalCode: {
                required
            },
            locality: {
                required
            }
        },
        mounted() {
            this.streetAddress = this.constructionSite.streetAddress;
            this.postalCode = this.constructionSite.postalCode;
            this.locality = this.constructionSite.locality;
        }
    }
</script>
