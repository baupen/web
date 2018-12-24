<template>
    <div class="jumbotron">
        <h2>{{$t("import_craftsmen.title")}}</h2>
        <p class="text-secondary">{{$t("import_craftsmen.help")}}</p>
        <p class="alert alert-info" v-if="showExcelContent">{{$t("import_craftsmen.copy_paste_from_excel")}}</p>
        <textarea class="form-control" rows="3"
                  v-if="showExcelContent"
                  v-model="excelContent"
                  :placeholder="$t('import_craftsmen.copy_paste_area_placeholder')"></textarea>


        <template v-if="!showExcelContent">
            <p class="alert alert-success" v-if="invalidContentTypeSelections.length === 0">
                {{$t("import_craftsmen.content_types_valid")}}
            </p>
            <p class="alert alert-danger" v-if="invalidContentTypeSelections.length > 0">
                {{$t("import_craftsmen.invalid_content_types", {invalidContentTypes:
                invalidContentTypeSelections.map(ict => ict.text).join(", ")})}}

                <button class="btn btn-danger" @click="abortImport">
                    {{$t("import_craftsmen.actions.abort")}}
                </button>
            </p>
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th v-for="(entry, index) in dataTableHeader" :key="'header-' + index">
                        <select class="form-control" v-model="entry.contentType">
                            <option v-for="(contentType, contentTypeIndex) in contentTypes"
                                    :key="'content-type-' + contentTypeIndex"
                                    :value="contentType.value">
                                {{contentType.text}}
                            </option>
                        </select>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(row, index) in dataTable" :key="'row-' + index">
                    <td v-for="(cell, columnIndex) in row" :key="'column-' + index + '-' + columnIndex">
                        {{cell}}
                    </td>
                </tr>
                </tbody>
            </table>

            <template v-if="invalidContentTypeSelections.length === 0">
                <div class="vertical-spacer-big"></div>
                <h2>{{$t("import_craftsmen.change_preview")}}</h2>

                <template v-if="importActions.length === 0">
                    <p class="alert alert-success">
                        {{$t("import_craftsmen.no_more_changes_detected")}}
                        <button class="btn btn-link" @click="closeImport">
                            {{$t("import_craftsmen.actions.close")}}
                        </button>
                    </p>
                </template>
                <template v-else>
                    <p class="alert alert-warning" v-if="importActions.length > 0">
                        {{$t("import_craftsmen.changes_detected")}}
                    </p>

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <td>{{ $t('import_craftsmen.changes.title')}}</td>
                            <td>{{ $t('craftsman.email') }}</td>
                            <td>{{ $t('craftsman.contact_name') }}</td>
                            <td>{{ $t('craftsman.company') }}</td>
                            <td>{{ $t('craftsman.trade') }}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(importAction, index) in importActions" :key="'import-action-' + index">
                            <td>
                                <span v-if="importAction.action === 'add'">
                                    {{$t('import_craftsmen.changes.add')}}
                                </span>
                                <span v-else-if="importAction.action === 'update'">
                                    {{$t('import_craftsmen.changes.update')}}
                                </span>
                                <span v-else-if="importAction.action === 'remove'">
                                    {{$t('import_craftsmen.changes.remove')}}
                                </span>
                            </td>
                            <td>{{importAction.email}}</td>
                            <template v-if="importAction.changeSet !== null">
                                <td>{{importAction.changeSet.contactName}}</td>
                                <td>{{importAction.changeSet.company}}</td>
                                <td>{{importAction.changeSet.trade}}</td>
                            </template>
                            <template v-else>
                                <td></td>
                                <td></td>
                                <td></td>
                            </template>
                        </tr>
                        </tbody>
                    </table>

                    <p>
                        <span>
                            <br/>
                        </span>
                    </p>

                    <div class="btn-group">
                        <button class="btn btn-danger" @click="abortImport">
                            {{$t("import_craftsmen.actions.abort")}}
                        </button>
                        <button class="btn btn-outline-primary" @click="applyImport">
                            {{$t("import_craftsmen.actions.apply_import")}}
                        </button>
                    </div>
                </template>
            </template>
        </template>
    </div>
</template>

<script>
    import moment from "moment";
    import MapTableRow from "./MapTableRow";
    import TextEditField from "./TextEditField";
    import eol from 'eol'

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        props: {
            craftsmanContainers: {
                type: Array,
                required: true
            }
        },
        data: function () {
            return {
                locale: lang,
                excelContent: null,
                activeError: null,
                dataTable: [],
                dataTableHeader: [],
                dataTableMapping: [],
                showExcelContent: true,
                contentTypes: []
            }
        },
        components: {
            TextEditField,
            MapTableRow
        },
        methods: {
            abortImport: function () {
                this.excelContent = null;
                this.showExcelContent = true;
            },
            closeImport: function () {
                this.abortImport();
                this.$emit("close");
            },
            applyImport: function () {
                let writePropertiesFunc = (container, importAction) => {
                    container.craftsman.email = importAction.email;
                    container.craftsman.contactName = importAction.changeSet.contactName;
                    container.craftsman.company = importAction.changeSet.company;
                    container.craftsman.trade = importAction.changeSet.trade;
                };

                this.importActions.forEach(ia => {
                    if (ia.action === 'add') {
                        this.$emit('craftsman-add', (newContainer) => writePropertiesFunc(newContainer, ia));
                    } else if (ia.action === 'update') {
                        writePropertiesFunc(ia.craftsmanContainer, ia);
                        this.$emit('craftsman-save', ia.craftsmanContainer);
                    } else if (ia.action === 'remove') {
                        this.$emit('craftsman-remove', ia.craftsmanContainer);
                    }
                });
            },
            parseExcelContent: function () {
                this.dataTableHeader = [];
                this.dataTable = [];

                if (this.excelContent === null) {
                    return;
                }

                this.showExcelContent = false;

                const content = this.cleanupText(this.excelContent);
                const [rowDivider, columnDivider] = this.getDividers(content);
                const dataTable = content.split(rowDivider).map(line => line.split(columnDivider).map(entry => entry.trim()));
                const normalizedDataTable = this.normalizeDataTable(dataTable);

                //check if header first row
                let header = [];
                let normalizedHeader = [];
                if (normalizedDataTable.length > 0) {
                    const possibleHeader = normalizedDataTable[0];
                    const normalizedPossibleHeader = this.normalizeTextArray(possibleHeader);
                    if (this.isHeader(normalizedPossibleHeader) > 0.5) {
                        // set header & remove first row from data table
                        header = possibleHeader;
                        normalizedHeader = normalizedPossibleHeader;
                        normalizedDataTable.splice(0, 1);
                    }
                }

                // match types to the columns
                const rowWiseDataTable = this.switchDataTableDimensions(normalizedDataTable);
                const highThreshold = 0.8;
                const middleThreshold = 0.4;
                const lowThreshold = 0.2;
                let columnMapping = [];
                for (let i = 0; i < rowWiseDataTable.length; i++) {
                    const cleanupArray = this.cleanupArray(rowWiseDataTable[i]);
                    const currentRow = this.normalizeTextArray(cleanupArray);
                    const currentHeader = normalizedHeader.length > i ? normalizedHeader[i] : null;

                    if (this.isEmail(currentRow, currentHeader) > highThreshold) {
                        columnMapping[i] = "email";
                    } else if (this.isPhone(currentRow, currentHeader) > highThreshold) {
                        columnMapping[i] = "phone";
                    } else if (this.isPlzPlace(currentRow, currentHeader) > highThreshold) {
                        columnMapping[i] = "plz_place";
                    } else if (this.isStreetNumber(currentRow, currentHeader) > highThreshold) {
                        columnMapping[i] = "street_number";
                    } else if (this.isCompany(currentRow, currentHeader) > middleThreshold) {
                        columnMapping[i] = "company";
                    } else if (this.isContactName(currentRow, currentHeader) > highThreshold) {
                        columnMapping[i] = "contact_name";
                    } else if (this.isTrade(currentRow, currentHeader) > lowThreshold) {
                        columnMapping[i] = "trade";
                    } else {
                        columnMapping[i] = "";
                    }
                }

                // format result to easily display
                let dataTableHeader = [];
                for (let i = 0; i < columnMapping.length; i++) {
                    dataTableHeader.push({
                        name: header.length > i ? header[i] : null,
                        contentType: columnMapping[i]
                    })
                }

                // publish
                this.dataTableHeader = dataTableHeader;
                this.dataTable = normalizedDataTable;
            },
            cleanupText: function (text) {
                // normalize line endings to lf
                return eol.lf(text);
            },
            cleanupArray: function (array) {
                const noEmpty = array.filter(e => e.length > 0);

                let seen = {};
                return noEmpty.filter(function (item) {
                    return seen.hasOwnProperty(item) ? false : (seen[item] = true);
                });
            },
            getDividers: function (content) {
                const rowDivider1 = this.countCharactersInText(content, "\n");
                const rowDivider2 = this.countCharactersInText(content, ";");

                const columnDivider1 = this.countCharactersInText(content, "\t");
                const columnDivider2 = this.countCharactersInText(content, ",");

                const rowDivider = rowDivider1 < rowDivider2 ? ";" : "\n";
                const columnDivider = columnDivider1 < columnDivider2 ? "," : "\t";

                return [rowDivider, columnDivider];
            },
            normalizeDataTable: function (dataTable) {
                // remove empty rows
                dataTable = dataTable.filter(line => line.join("").length > 0);

                //ensure all columns are of same size
                const maxColumns = Math.max(...dataTable.map(line => line.length));
                dataTable.forEach(line => {
                    while (line.length < maxColumns) {
                        line.push("")
                    }
                });

                return dataTable;
            },
            switchDataTableDimensions: function (dataTable) {
                if (dataTable.length === 0) {
                    return [];
                }

                let result = [];
                for (let i = 0; i < dataTable[0].length; i++) {
                    result.push([]);
                }

                for (let i = 0; i < dataTable.length; i++) {
                    for (let j = 0; j < dataTable[i].length; j++) {
                        result[j][i] = dataTable[i][j];
                    }
                }

                return result;
            },
            countCharactersInText: function (text, character) {
                let result = 0;
                const textLength = text.length;
                for (let i = 0; i < textLength; i++) {
                    if (text[i] === character) {
                        result++;
                    }
                }
                return result;
            },
            normalizeTextArray: function (text) {
                return text.map(t => t.toLowerCase());
            },
            anyInText: function (text, array) {
                const arrayLength = array.length;
                for (let i = 0; i < arrayLength; i++) {
                    if (text.indexOf(array[i]) >= 0) {
                        return true;
                    }
                }

                return false;
            },
            isHeader: function (content) {
                const possibleHeaders = ["name", "firma", "email", "e-mail", "plz", "ort", "strasse", "adresse", "arbeitsgattung"];
                return content.filter(c => this.anyInText(c, possibleHeaders)).length / content.length;
            },
            isStreetNumber: function (content, header = null) {
                if (header !== null && this.anyInText(header, ["adresse", "strasse"])) {
                    return 1;
                }

                const streetAndNumber = new RegExp(/^[A-Z][a-z]+ [0-9]+$/);
                return content.filter(c => streetAndNumber.test(c)).length / content.length;
            },
            isPlzPlace: function (content, header = null) {
                if (header !== null && this.anyInText(header, ["ort", "plz"])) {
                    return 1;
                }

                const plzAndPlace = new RegExp(/^[0-9]{4} [A-Z]([a-z])+$/);
                return content.filter(c => plzAndPlace.test(c)).length / content.length;
            },
            isPhone: function (content, header = null) {
                if (header !== null && this.anyInText(header, ["tel", "mobile"])) {
                    return 1;
                }

                const internationalNumber = new RegExp(/^\+{0,1}[0-9 ]+$/);
                return content.filter(c => internationalNumber.test(c)).length / content.length;
            },
            isCompany: function (content, header = null) {
                if (header !== null && this.anyInText(header, ["firma"])) {
                    return 1;
                }

                return content.filter(c => c.indexOf(" ag") > 0 || c.indexOf(" gmbh") > 0).length / content.length;
            },
            isEmail: function (content, header = null) {
                if (header !== null && this.anyInText(header, ["email", "e-mail", "mailadresse"])) {
                    return 1;
                }

                const simpleCheck = new RegExp(/^\S+@\S+$/);
                return content.filter(c => simpleCheck.test(c)).length / content.length;
            },
            isContactName: function (content, header = null) {
                if (header !== null && this.anyInText(header, ["ansprech", "name"])) {
                    return 1;
                }

                const containsNumbers = new RegExp(/[0-9]/);
                const withoutNumbers = content.filter(c => !containsNumbers.test(c));

                const twoWords = new RegExp(/^([a-zäüöè]){3,} ([a-zäüöè]){3,}$/);
                const twoWordsMatches = content.filter(c => twoWords.test(c));

                const diff = twoWordsMatches.length / withoutNumbers.length;
                return withoutNumbers.length / content.length * diff;
            },
            isTrade: function (content, header = null) {
                if (header !== null && this.anyInText(header, ["handwerk", "gewerbe", "arbeitsgattung"])) {
                    return 1;
                }

                const containsNumbers = new RegExp("[0-9]");
                const withoutNumbers = content.filter(c => !containsNumbers.test(c));

                const tradeSelection = [
                    "bauleit", "baumeister", "projektleit", "bauherrschaft",
                    "fenster", "storen", "lüftung", "küchen",
                    "gips", "türen", "platten", "wand",
                    "gärtner", "dach", "dichtung", "isolation",
                    "sanitär", "elektro", "aushub", "zimmermann"
                ];
                const tradeMatches = withoutNumbers.filter(c => this.anyInText(c, tradeSelection));

                const companyMatches = content.filter(c => c.indexOf(" ag") > 0 || c.indexOf(" gmbh") > 0).length;

                const diff = (tradeMatches.length - companyMatches) / (withoutNumbers.length - companyMatches);
                return withoutNumbers.length / content.length * diff;
            }
        },
        computed: {
            invalidContentTypeSelections: function () {
                return this.contentTypes
                    .filter(ct => ct.value !== null)
                    .filter(ct => this.dataTableHeader.filter(dth => dth.contentType === ct.value).length !== 1);
            },
            importActions: function () {
                if (!this.invalidContentTypeSelections || this.dataTable === null || this.dataTableHeader === null) {
                    return []
                }

                // lookup of existing data
                let emailLookup = {};
                this.craftsmanContainers.forEach(cc => {
                    emailLookup[cc.craftsman.email] = cc;
                });

                // get indexes of relevant columns
                let rowsWithWritableValues = [0, 0, 0, 0];
                let writableValues = ["email", "contact_name", "company", "trade"];
                this.dataTableHeader.forEach((entry, index) => {
                    const writableValueIndex = writableValues.indexOf(entry.contentType);
                    if (writableValueIndex >= 0) {
                        rowsWithWritableValues[writableValueIndex] = index;
                    }
                });

                // create lookup of relevant data
                let newDataLookup = {};
                this.dataTable.forEach(row => {
                    let email = row[rowsWithWritableValues[0]];
                    if (email !== null && email.length > 0) {
                        newDataLookup[email] = {
                            contactName: row[rowsWithWritableValues[1]],
                            company: row[rowsWithWritableValues[2]],
                            trade: row[rowsWithWritableValues[3]]
                        }
                    }
                });

                // create add/update actions
                let actions = [];
                let emailsFound = [];
                for (const email in newDataLookup) {
                    if (Object.prototype.hasOwnProperty.call(newDataLookup, email)) {
                        emailsFound.push(email);
                        const newObj = newDataLookup[email];

                        if (Object.prototype.hasOwnProperty.call(emailLookup, email)) {
                            const oldObj = emailLookup[email];
                            if (oldObj.craftsman.contactName !== newObj.contactName ||
                                oldObj.craftsman.company !== newObj.company ||
                                oldObj.craftsman.trade !== newObj.trade) {

                                // update if any property different
                                actions.push({
                                    action: "update",
                                    email: email,
                                    craftsmanContainer: oldObj,
                                    changeSet: newObj
                                });
                            }
                        } else {

                            // add if email not knows yet
                            actions.push({
                                action: "add",
                                email: email,
                                craftsmanContainer: null,
                                changeSet: newObj
                            });
                        }
                    }
                }

                // create remove actions
                for (const email in emailLookup) {
                    if (Object.prototype.hasOwnProperty.call(emailLookup, email)) {
                        if (emailsFound.indexOf(email) === -1) {
                            const toBeRemovedObject = emailLookup[email];

                            // only propose to remove if it is possible
                            if (toBeRemovedObject.craftsman.issueCount === 0 && toBeRemovedObject.pendingChange !== "remove") {
                                actions.push({
                                    action: "remove",
                                    email: email,
                                    craftsmanContainer: toBeRemovedObject,
                                    changeSet: null
                                });
                            }
                        }
                    }
                }

                return actions;
            }
        },
        watch: {
            excelContent: function () {
                this.parseExcelContent();
            }
        },
        mounted() {
            this.contentTypes.push({
                value: "email",
                text: this.$t("import_craftsmen.content_types.email")
            });

            this.contentTypes.push({
                value: "contact_name",
                text: this.$t("import_craftsmen.content_types.contact_name")
            });

            this.contentTypes.push({
                value: "company",
                text: this.$t("import_craftsmen.content_types.company")
            });

            this.contentTypes.push({
                value: "trade",
                text: this.$t("import_craftsmen.content_types.trade")
            });

            this.contentTypes.push({
                value: null,
                text: "-"
            });

            this.parseExcelContent();
        }
    }

</script>