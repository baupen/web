export const createTranslator = function (locale, fallbackLocale, dictionary) {
  let _locale = locale
  const _fallbackLocale = fallbackLocale
  const _dictionary = dictionary

  return {
    getLocale () {
      return _locale
    },

    setLocale (locale) {
      _locale = locale
    },

    translate (key, params = {}, locale = undefined) {
      const currentLocale = locale || _locale
      let template = getNestedValue(_dictionary[currentLocale], key)
      if (!template) {
        const error = `[translator] Missing the "${key}" key within the "${currentLocale}" dictionary`
        if (_fallbackLocale !== currentLocale) {
          template = getNestedValue(_dictionary[_fallbackLocale], key)
          if (!template) {
            console.error(error + ` and the "${_fallbackLocale}" fallback locale.`)
            return ''
          } else {
            console.warn(error + `, but found it in the "${_fallbackLocale}" fallback locale.`)
          }
        } else {
          console.error(error + '.')
          return ''
        }
      }

      if ('count' in params) {
        const templateParts = template.split('|')

        if (params.count < templateParts.length) {
          return fillTemplate(templateParts[params.count], params)
        } else {
          return fillTemplate(templateParts[templateParts.length - 1], params)
        }
      }

      return fillTemplate(template, params)
    }
  }
}

const placeholderRegex = /{(.*?)}/g
const fillTemplate = function (template, templateData) {
  return template.replace(placeholderRegex, (match, placeholderPath) => {
    return getNestedValue(templateData, placeholderPath.trim()) ?? ''
  })
}

const getNestedValue = function (sourceObject, path) {
  const pathSegments = path.split('.')

  let currentValue = sourceObject
  for (let pathIndex = 0; pathIndex < pathSegments.length; pathIndex++) {
    currentValue = currentValue ? currentValue[pathSegments[pathIndex]] : undefined
  }

  return currentValue
}
