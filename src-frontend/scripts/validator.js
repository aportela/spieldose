let module = {
    invalidFields: []
};

module.clear = function () {
    module.invalidFields = [];
}

module.setInvalid = function (field, message) {
    module.invalidFields.push({ field: field, message: message });
}

module.hasInvalidFields = function () {
    return (module.invalidFields.length > 0);
}

module.hasInvalidField = function (field) {
    return (module.invalidFields.findIndex(e => e.field == field) > -1);
}

module.getInvalidFieldMessage = function (field) {
    let idx = module.invalidFields.findIndex(e => e.field == field);
    if (idx > -1) {
        return (module.invalidFields[idx].message);
    } else {
        return (null);
    }
}

export default module;
