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

extensionsForDoc = {
    1: 'docm|dotm|odt|docx|dotx|text|txt|dot|doc',
    2: 'ppt|pptm|ppsm|potm|odp|pptx|ppsx|potx',
    3: 'xls|xltm|ods|xlsx|xltx|csv',
    4: 'dst|dwf|dwfx|dwg|dws|dwt|dxb|dxf',
    5: 'pdf',
    6: 'bmp|gif|heic|heif|pjp|jpg|pjpeg|jpeg|jfif|png|tif|ico|webp',
    7: '3gpp|3gp2|avi|m4v|mp4|mpg|mpeg|ogm|ogv|mov|webm|m4v|mkv|asx|wm|wmv|wvx|avi',
    8: 'flac|mid|mp3|m4a|mp3|opus|oga|ogg|wav|m4a|mid|wav'
};

function returnExtensions(string, dot = '', separator = '|') {
    let extensions = '';

    if (string && string.trim().length > 0) {
        const allowedFileTypes = string.split(',');
        if (allowedFileTypes.length > 0) {
            allowedFileTypes.forEach(type => {
                const trimmedType = type.trim();
                if (extensionsForDoc[trimmedType] && extensionsForDoc[trimmedType].length > 0) {
                    extensions += extensionsForDoc[trimmedType].replace(/\|/g, separator) + separator;
                }
            });
        }
    }

    extensions = extensions.slice(0, -separator.length);
    let extensionsArray = extensions.split(separator);

    extensionsArray = extensionsArray.map(element => dot + element);
    extensions = extensionsArray.join(separator);

    return extensions;
}

function formatBytes(string) {
    if (string == '1024') {
        return "1 MB";
    } else if (string == '10240') {
        return "10 MB";
    } else if (string == '102400') {
        return "100 MB";
    } else if (string == '1024000') {
        return "1 GB";
    } else if (string == '10240000') {
        return "10 GB";
    } else {
        return "10 MB";
    }
}