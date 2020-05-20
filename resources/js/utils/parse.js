export const cloneOf = (obj) => {
    return JSON.parse(JSON.stringify(obj));
}

export const trim = (string, character) => {
    const first = [ ...string ].findIndex(char => char !== character);
    const last  = [ ...string ].reverse().findIndex(char => char !== character);
    return string.substring(first, string.length - last);
}

export const basename = (str) => {
    return str.split('\\').pop().split('/').pop();
}

export const getFileExtension = (filename) =>
{
    var ext = /^.+\.([^.]+)$/.exec(filename);
    return ext == null ? "" : ext[1];
}
