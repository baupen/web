// library import
import merge from 'deepmerge'

// get all shared lang files
import actions from './actions'
import craftsman from './craftsman'
import issue from './issue'
import map from './map'
import messages from './messages'

// merge in single object; mapping to the correct property
const translations = {actions, craftsman, issue, map, messages};
let sharedTranslations = {};
Object.keys(translations).forEach(property => {
    const translation = translations[property];
    Object.keys(translation).forEach(lang => {
        if (!(lang in sharedTranslations)) {
            sharedTranslations[lang] = {};
        }
        sharedTranslations[lang][property] = translation[lang]
    });
});

// merge passed messages with the shared translations
export default function (messages) {
    return merge(messages, sharedTranslations);
}