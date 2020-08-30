const $ = require('jquery');
const compatibleBrowser = typeof Object['__defineSetter__'] === 'function';
if (!compatibleBrowser) {
  $('#outdated').addClass('active');
}
