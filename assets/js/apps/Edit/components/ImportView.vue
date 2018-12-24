<template>
    <div class="jumbotron">
        <h2>{{$t("import_craftsmen.title")}}</h2>
        <p class="text-secondary">{{$t("import_craftsmen.help")}}</p>
        <p class="alert alert-info">{{$t("import_craftsmen.copy_paste_from_excel")}}</p>
        <textarea class="form-control" rows="3" v-model="excelContent"
                  :placeholder="$t('import_craftsmen.copy_paste_area_placeholder')"></textarea>
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
                activeError: null
            }
        },
        components: {
            TextEditField,
            MapTableRow
        },
        methods: {
            parseExcelContent: function (content) {
                content = this.cleanupText(content);
                const {rowDivider, lineDivider} = this.getDividers(content);
                const dataTable = content.split(lineDivider).map(line => line.split(rowDivider).map(entry => entry.trim()));
                const normalizedDataTable = this.normalizeDataTable(dataTable);

                //check if header first row
                let header = [];
                if (normalizedDataTable.length > 0) {
                    const possibleHeader = normalizedDataTable[0];
                    const normalizedPossibleHeader = this.normalizeTextArray(possibleHeader);
                    if (this.isHeader(normalizedPossibleHeader) > 0.5) {
                        // set header & remove first row from data table
                        header = normalizedPossibleHeader;
                        normalizedDataTable.splice(0, 1);
                    }
                }

                const rowWiseDataTable = this.switchDataTableDimensions(normalizedDataTable);
                const highThreshold = 0.8;
                const lowThreshold = 0.2;
                let columnMapping = [];
                for (let i = 0; i < rowWiseDataTable.length; i++) {
                    const currentRow = this.normalizeTextArray(rowWiseDataTable[i]);
                    const header = header.length > i ? header[i] : null;

                    if (this.isEmail(currentRow, header) > highThreshold) {
                        columnMapping[i] = "email";
                    } else if (this.isPhone(currentRow, header) > highThreshold) {
                        columnMapping[i] = "phone";
                    } else if (this.isPlzPlace(currentRow, header) > highThreshold) {
                        columnMapping[i] = "plz_place";
                    } else if (this.isStreetNumber(currentRow, header) > highThreshold) {
                        columnMapping[i] = "street_number";
                    } else if (this.isCompany(currentRow, header) > highThreshold) {
                        columnMapping[i] = "company";
                    } else if (this.isContactName(currentRow, header) > highThreshold) {
                        columnMapping[i] = "contact_name";
                    } else if (this.isTrade(currentRow, header) > lowThreshold) {
                        columnMapping[i] = "trade";
                    } else {
                        columnMapping[i] = "";
                    }
                }


            },
            cleanupText: function (text) {
                // normalize line endings to lf
                return eol.lf(text);
            },
            getDividers: function (content) {
                const rowDivider1 = this.countCharactersInText(content, "\n");
                const rowDivider2 = this.countCharactersInText(content, ";");

                const lineDivider1 = this.countCharactersInText(content, "\t");
                const lineDivider2 = this.countCharactersInText(content, ",");

                const rowDivider = rowDivider1 < rowDivider2 ? rowDivider2 : rowDivider1;
                const lineDivider = lineDivider1 < lineDivider2 ? lineDivider2 : lineDivider1;

                return [rowDivider, lineDivider];
            },
            normalizeDataTable: function (dataTable) {
                // remove empty rows
                dataTable = dataTable.filter(line => line.join().length > 0);

                //ensure all columns are of same size
                const maxColumns = Math.max(...dataTable.map(line => line.length));
                dataTable.filter(line => line.join().length > 0).forEach(line => {
                    while (line.length < maxColumns) {
                        line.push("")
                    }
                });

                return dataTable;
            },
            switchDataTableDimensions: function (dataTable) {
                let result = [];
                for (let i = 0; i < dataTable.length; i++) {
                    result.push([]);
                }

                for (let i = 0; i < dataTable.length; i++) {
                    for (let j = 0; j < dataTable[i].length; i++) {
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

                return true;
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

                const twoWords = new RegExp(/^\w+ \w+$/);
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

                const tradeSelection = ["bauleiter", "baumeister", "fenster", "storen", "lüftung", "küchen", "gips", "türen", "platten", "wand", "gärtner", "dach", "dichtung", "isolation"];
                const tradeMatches = withoutNumbers.filter(c => this.anyInText(c, tradeSelection));

                const diff = tradeMatches.length / withoutNumbers.length;
                return withoutNumbers.length / content.length * diff;
            }
        },
        computed: {},
        watch: {
            excelContent: function () {
                if (this.excelContent !== null) {
                    const parseResult = this.parseExcelContent(this.excelContent);
                    if (parseResult !== true) {
                        this.excelError = parseResult;
                    }
                }
            }
        }
    }

</script>