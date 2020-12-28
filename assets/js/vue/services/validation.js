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
      return value !== null && value.toString().length > 0
    },
    errorMessage: 'validation.required'
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

const resetFields = function (fields) {
  for (const fieldName in fields) {
    if (Object.prototype.hasOwnProperty.call(fields, fieldName)) {
      fields[fieldName].dirty = false
      fields[fieldName].errors = []
    }
  }
}

export {
  createField,
  requiredRule,
  validateField,
  validateFields,
  resetFields
}
