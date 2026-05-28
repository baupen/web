// source: https://github.com/lukeed/rosetta/blob/master/src/debug.js
const translator = function (obj) {
  let locale = '';
  const tree = obj || {};

  return {
    set(lang, table) {
      tree[lang] = Object.assign(tree[lang] || {}, table);
    },

    locale(lang) {
      return (locale = lang || locale);
    },

    t(key, params, lang) {
      const val = dlv(tree[lang || locale], key);
      if (val == null) {
        return console.error(`[translator] Missing the "${[].concat(key).join('.')}" key within the "${lang || locale}" dictionary`);
      }
      if (typeof val === 'function') return val(params);
      if (typeof val === 'string') return tmpl(val, params);
      return val;
    }
  };
}

// source: https://github.com/lukeed/templite/blob/master/src/index.js
const RGX = /{{(.*?)}}/g;
const tmpl = function (str, mix) {
  return str.replace(RGX, (x, key, y) => {
    x = 0;
    y = mix;
    key = key.trim().split('.');
    while (y && x < key.length) {
      y = y[key[x++]];
    }
    return y != null ? y : '';
  });
}

// source: https://github.com/developit/dlv/blob/master/index.js
const dlv = function (obj, key, def, p, undef) {
  key = key.split ? key.split('.') : key;
  for (p = 0; p < key.length; p++) {
    obj = obj ? obj[key[p]] : undef;
  }
  return obj === undef ? def : obj;
}
