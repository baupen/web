<template>
    <div>
        <div class="form-group">
            <label for="name">{{$t("construction_site.name")}}</label>
            <input type="text" v-focus class="form-control" v-model="$v.name.$model" :class="{'is-invalid': $v.name.$dirty && $v.name.$invalid}" id="name"  :placeholder="$t('construction_site.name')" @input="checkIfNameTaken($v.name)" required>
            <div class="invalid-feedback">
                <span v-if="!$v.name.required">{{$t('validation.required')}}</span>
                <span v-if="!$v.name.nameNotTaken">{{$t('validation.name_already_taken')}}</span>
            </div>
        </div>

        <div class="form-group mt-4 mb-2" :class="">
            <label for="streetAddress">{{$t("construction_site.street_address")}}</label>
            <input type="text" v-model="$v.streetAddress.$model" class="form-control" id="streetAddress" :class="{'is-invalid': $v.streetAddress.$dirty && $v.streetAddress.$invalid}" :placeholder="$t('construction_site.street_address')">
            <div class="invalid-feedback">
                {{$t('validation.required')}}
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                <label for="postalCode">{{$t("construction_site.postal_code")}}</label>
                <input type="text" v-model="$v.postalCode.$model" class="form-control" id="postalCode" :class="{'is-invalid': $v.postalCode.$dirty && $v.postalCode.$invalid}"  :placeholder="$t('construction_site.postal_code')">
                <div class="invalid-feedback">
                    {{$t('validation.required')}}
                </div>
            </div>
            <div class="col">
                <label for="locality">{{$t("construction_site.locality")}}</label>
                <input type="text" v-model="$v.locality.$model" class="form-control" id="locality" :class="{'is-invalid': $v.locality.$dirty && $v.locality.$invalid}"  :placeholder="$t('construction_site.locality')">
                <div class="invalid-feedback">
                    {{$t('validation.required')}}
                </div>
            </div>
        </div>
        <b-button variant="primary" type="submit" class="mt-4" @click="submit" :class="{'disabled': $v.$invalid}">{{$t("actions.create_construction_site")}}</b-button>
    </div>
</template>

<script>
    import {required} from 'vuelidate/lib/validators'
    import axios from 'axios';
    import debounce from 'debounce';

    export default {
        data: function () {
            return {
                name: null,
                nameTaken: false,
                streetAddress: null,
                postalCode: null,
                locality: null
            }
        },
        methods: {
            submit: function () {
                this.$v.$touch();
                if (!this.$v.$invalid) {
                    const body = {name: this.name, streetAddress: this.streetAddress, postalCode: this.postalCode, locality: this.locality};
                    axios.post("/api/switch/create", body).then(response => {
                        window.location = "/edit";
                    });
                }
            },
            checkIfNameTaken: debounce(function () {
                axios.post("/api/switch/create/check", {
                    constructionSiteName: this.name
                }).then(response => {
                    this.nameTaken = response.data.constructionSiteNameTaken;
                    this.$v.name.$touch()
                })
            }, 500)
        },
        directives: {
            focus: {
                inserted: function (el) {
                    el.focus()
                }
            }
        },
        validations: {
            name: {
                required,
                nameNotTaken: function () {
                    return !this.nameTaken;
                }
            },
            streetAddress: {
                required
            },
            postalCode: {
                required
            },
            locality: {
                required
            }
        }
    }
</script>
