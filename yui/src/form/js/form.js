/**
 * JavaScript for form editing skills conditions.
 *
 * @module moodle-availability_skills-form
 */
M.availability_skills = M.availability_skills || {};

/**
 * @class M.availability_skills.form
 * @extends M.core_availability.plugin
 */
M.availability_skills.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} skills Array of objects containing skills in the course.
 */
M.availability_skills.form.initInner = function(skills) {
    this.skills = skills;
};

M.availability_skills.form.getNode = function(json) {
    // Create HTML structure.
    var html = '<span class="col-form-label pr-3"> ' + M.util.get_string('title', 'availability_skills') + '</span>' +
               ' <span class="availability-group form-group"><label>' +
            '<span class="accesshide">' + M.util.get_string('label_chooseskill', 'availability_skills') + ' </span>' +
            '<select class="custom-select" name="skills" title="' + M.util.get_string('label_chooseskill', 'availability_skills') + '">' +
            '<option value="0">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    for (var i = 0; i < this.skills.length; i++) {
        var skills = this.skills[i];
        // String has already been escaped using format_string.
        html += '<option value="' + skills.id + '">' + skills.name + '</option>';
    }
    html += '</select></label> <label><span class="accesshide">' +
                M.util.get_string('label_choosetype', 'availability_skills') +
            ' </span><select class="custom-select" ' +
                            'name="ct" title="' + M.util.get_string('label_choosetype', 'availability_skills') + '">' +
            '<option value="0">' + M.util.get_string('type_notinlevel', 'availability_skills') + '</option>' +
            '<option value="1">' + M.util.get_string('type_exactlevel', 'availability_skills') + '</option>' +
            '<option value="2">' + M.util.get_string('type_selectlevelorhigher', 'availability_skills') + '</option>' +
            '<option value="3">' + M.util.get_string('type_selectlevelorlower', 'availability_skills') + '</option>' +
            '<option value="4">' + M.util.get_string('type_exactpoints', 'availability_skills') + '</option>' +
            '<option value="5">' + M.util.get_string('type_moreorequalpoints', 'availability_skills') + '</option>' +
            '<option value="6">' + M.util.get_string('type_lesspoints', 'availability_skills') + '</option>' +
            '</select></label></span>';

    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial values.
    if (json.skills !== undefined &&
        node.one('select[name=skills] > option[value=' + json.skills + ']')) {
        node.one('select[name=skills]').set('value', '' + json.skills);
    }
    if (json.ct !== undefined) {
        node.one('select[name=ct]').set('value', '' + json.ct);
    }

     // Add event handlers (first time only).
     if (!M.availability_skills.form.addedEvents) {
        M.availability_skills.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_skills select');
    }
}

M.availability_skills.form.fillValue = function(value, node) {
    value.skills = parseInt(node.one('select[name=skills]').get('value'), 10);
    value.ct = parseInt(node.one('select[name=ct]').get('value'), 10);
};

M.availability_skills.form.fillErrors = function(errors, node) {
    var skills = parseInt(node.one('select[name=skills]').get('value'), 10);
    if (skills === 0) {
        errors.push('availability_skills:error_selectskillsid');
    }
}