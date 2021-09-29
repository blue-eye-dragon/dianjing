/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/


 var alertFallback = false;

 if (typeof console === "undefined" || typeof console.log === "undefined") {
   console = {};
   if (alertFallback) {
     console.log = function(msg) {
       alert(msg);
     };
   } else {
     console.log = function() {};
   }
 }


if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
  };
}

if (!String.prototype.repeat) {
  String.prototype.repeat = function(count) {
    'use strict';
    if (this == null)
      return '';

    var result = '' + this;
    count = +count;

    if (count != count)
      count = 0;

    if (count < 0 || count == Infinity)
      return '';

    count = Math.floor(count);
    if (result.length == 0 || count == 0)
      return '';

    if (result.length * count >= 1 << 28)
      return '';

    var maxCount = result.length * count;
    count = Math.floor(Math.log(count) / Math.log(2));
    while (count) {
       result += result;
       count--;
    }

    result += result.substring(0, maxCount - result.length);
    return result;
  }
}


(function (w) {

    w.URLSearchParams = w.URLSearchParams || function (searchString) {
        var self = this;
        self.searchString = searchString;
        self.get = function (name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(self.searchString);
            if (results == null) {
                return null;
            }
            else {
                return decodeURI(results[1]) || 0;
            }
        };
    }

})(window);
// if (!Array.prototype.filter) {

//     Array.prototype.filter = function(func) {
//         "use strict";

//         if (this === void 0 || this === null) {
//             throw new TypeError();
//         }
//         if (typeof func !== "function") {
//             throw new TypeError();
//         }

//         var t = Object(this);
//         var len = t.length >>> 0;
//         var res = [];
//         var thisp = arguments[1];
//         for (var i = 0; i < len; i++) {
//             if (i in t) {
//                 var val = t[i]; // in case func mutates this
//                 if (func.call(thisp, val, i, t)) {
//                     res.push(val);
//                 }
//             }
//         }

//         return res;
//     };
// }