const createField = function () {
  const field = {
    dirty: false,
    rules: [],
    errors: []
  }

  for (let i = 0; i < arguments.length; i++) {
    field.rules.push(arguments[i])
  }

  return field
}

const requiredRule = function () {
  return {
    isValid: function (value) {
      return !!value
    },
    errorMessage: '_validation.required'
  }
}

const validateField = function (field, value) {
  field.errors = field.rules
    .filter(rule => !rule.isValid(value))
    .map(rule => rule.errorMessage)

  field.valid = field.dirty && field.errors.length === 0
  field.invalid = field.dirty && field.errors.length > 0
}

const validateFields = function (fields, values) {
  for (const fieldName in fields) {
    if (Object.prototype.hasOwnProperty.call(fields, fieldName)) {
      validateField(fields[fieldName], values[fieldName])
    }
  }
}

const fieldValues = function (fields, values) {
  const result = {}
  for (const fieldName in fields) {
    if (Object.prototype.hasOwnProperty.call(fields, fieldName)) {
      result[fieldName] = values[fieldName]
    }
  }

  return result
}

const changedFieldValues = function (fields, values, template = null) {
  const result = {}
  for (const fieldName in fields) {
    if (Object.prototype.hasOwnProperty.call(fields, fieldName)) {
      if (template && template[fieldName] === values[fieldName]) {
        continue
      }

      result[fieldName] = values[fieldName]
    }
  }

  return result
}

const resetFields = function (fields) {
  for (const fieldName in fields) {
    if (Object.prototype.hasOwnProperty.call(fields, fieldName)) {
      fields[fieldName].dirty = false
      fields[fieldName].errors = []
      fields[fieldName].valid = false
      fields[fieldName].invalid = false
    }
  }
}

export {
  createField,
  requiredRule,
  validateField,
  validateFields,
  changedFieldValues,
  fieldValues,
  resetFields
}
