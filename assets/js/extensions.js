//remove duplicate entries from array
Array.prototype.unique = function () {
    return Array.from(new Set(this));
};
