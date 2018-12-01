import 'promise-polyfill/src/polyfill';
import 'core-js/es6/symbol';
import 'core-js/es6/object';
import 'core-js/es6/function';
import 'core-js/es6/parse-int';
import 'core-js/es6/parse-float';
import 'core-js/es6/number';
import 'core-js/es6/math';
import 'core-js/es6/string';
import 'core-js/es6/date';
import 'core-js/es6/array';
import 'core-js/es6/regexp';
import 'core-js/es6/map';
import 'core-js/es6/weak-map';
import 'core-js/es6/set';

// Plain Javascript
// event listener: DOM ready
function addLoadEvent (func) {
  var oldonload = window.onload;
  if (typeof window.onload !== 'function') {
    window.onload = func;
  } else {
    window.onload = function () {
      if (oldonload) {
        oldonload();
      }
      func();
    };
  }
}

// call plugin function after DOM ready
addLoadEvent(function () {
  outdatedBrowser({
    bgColor: '#f25648',
    color: '#ffffff',
    cssProp: 'borderImage'
  });
});

var outdatedBrowser = function (options) {
  // Variable definition (before ajax)
  var outdated = document.getElementById('outdated');

  // Define opacity and fadeIn/fadeOut functions
  var done = true;

  function functionOpacity (opacityValue) {
    outdated.style.opacity = opacityValue / 100;
    outdated.style.filter = 'alpha(opacity=' + opacityValue + ')';
  }

  function functionFadeIn (opacityValue) {
    functionOpacity(opacityValue);
    if (opacityValue === 1) {
      outdated.style.display = 'block';
    }
    if (opacityValue === 100) {
      done = true;
    }
  }

  var supports = (function () {
    var div = document.createElement('div');

    return function (prop) {
      div.style[prop] = 'inherit';
      return div.style[prop] === 'inherit';
    };
  })();

  // if browser does not supports css3 property (transform=default), if does > exit all this
  if (!supports(options.cssProp)) {
    if (done && outdated.style.opacity !== '1') {
      done = false;
      for (var i = 1; i <= 100; i++) {
        setTimeout((function (x) {
          return function () {
            functionFadeIn(x);
          };
        })(i), i * 8);
      }
    }
  } else {
    return;
  }

  startStylesAndEvents();

  // events and colors
  function startStylesAndEvents () {
    var btnClose = document.getElementById('btnCloseUpdateBrowser');
    var btnUpdate = document.getElementById('btnUpdateBrowser');

    // check settings attributes
    outdated.style.backgroundColor = options.bgColor;
    // way too hard to put !important on IE6
    outdated.style.color = options.color;
    outdated.children[0].style.color = options.color;
    outdated.children[1].style.color = options.color;

    // check settings attributes
    btnUpdate.style.color = options.color;
    // btnUpdate.style.borderColor = options.color;
    if (btnUpdate.style.borderColor) btnUpdate.style.borderColor = options.color;
    btnClose.style.color = options.color;

    // close button
    btnClose.onmousedown = function () {
      outdated.style.display = 'none';
      return false;
    };

    // Override the update button color to match the background color
    btnUpdate.onmouseover = function () {
      this.style.color = options.bgColor;
      this.style.backgroundColor = options.color;
    };
    btnUpdate.onmouseout = function () {
      this.style.color = options.color;
      this.style.backgroundColor = options.bgColor;
    };
  }// end styles and events

/// /////END of outdatedBrowser function
};
