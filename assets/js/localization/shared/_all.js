// library import
import merge from 'deepmerge';

// get all shared lang files
import actions from './actions';
import constructionSite from './construction_site';
import craftsman from './craftsman';
import issue from './issue';
import map from './map';
import mapFile from './map_file';
import messages from './messages';
import validation from './validation';
import view from './view';

// merge in single object; mapping to the correct property
const translations = {
  actions,
  construction_site: constructionSite,
  craftsman,
  issue,
  map,
  map_file: mapFile,
  messages,
  validation,
  view
};
let sharedTranslations = {};
Object.keys(translations).forEach(property => {
  const translation = translations[property];
  Object.keys(translation).forEach(lang => {
    if (!(lang in sharedTranslations)) {
      sharedTranslations[lang] = {};
    }
    sharedTranslations[lang][property] = translation[lang];
  });
});

// merge passed messages with the shared translations
export default function (messages) {
  return merge(messages, sharedTranslations);
}
