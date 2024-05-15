/** Jquery Helpers **/

$.fn.hasAttr = function(name) {  
    return this.attr(name) !== undefined;
};



/** Jquery Helpers **/



/** Javascript Helpers **/

const dd = (...args) => {
    console.log(...args);
    debugger;
}

const exists = (arg) => {
    if (typeof arg !== 'undefined' && arg !== '' && arg !== null) {
        return true;
    }

    return false;
}

/** Javascript Helpers **/