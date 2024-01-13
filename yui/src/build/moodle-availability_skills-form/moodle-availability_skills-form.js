YUI.add('moodle-availability_skills-form', function (Y, NAME) {

/**
 * JavaScript for form editing skills conditions.
 *
 * @module moodle-availability_skills-form
 */
M.availability_skills = M.availability_skills || {}; // eslint-disable-line

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
 * @param {Int} contextid Id of the course context.
 */
M.availability_skills.form.initInner = function(skills, contextid) {
    this.skills = skills;
    this.contextID = contextid;
};

/**
 * Get/create the condition node(s).
 *
 * @param {Object} json
 * @return {Object} node
 */
M.availability_skills.form.getNode = function(json) {

    /**
     * @param {string} identifier A string identifier
     * @param {string} module Component of the string
     *
     * @returns {string} A string from translations.
     */
    function getString(identifier, module) {
        module = module || 'availability_skills';
        return M.util.get_string(identifier, module);
    }

    /**
     * Update the level and points elements visibility based on the condition type.
     *
     * @param {string} value Selected condition type.
     *
     * @returns {void}
     */
    function updatePointsVisibility(value) {

        var pointConditions = ['4', '5', '6']; // Conditions that are based on the points.

        if (pointConditions.find(function(v) {
            return v == value;
        })) {
            node.one('select[name=level]').hide();
            node.one('input[name=points]').show();
        } else {
            node.one('input[name=points]').hide();
            node.one('select[name=level]').show();
        }
    }

    /**
     * Update the levels of the skills in the level condition selection element.
     *
     * @param {int} skill ID of the skill to fetch
     * @param {Element} selector
     * @param {int} contextID Context ID
     * @param {string} emptylevel Html of the empty level option.
     *
     * @returns {Promise}
     *
     */
    function updateSkillLevels(skill, selector, contextID, emptylevel) {

        return new Promise(function(resolve, reject) { // eslint-disable-line

            require(['core/fragment'], function(Fragment) {
                var params = {
                    skill: skill
                };

                var request = Fragment.loadFragment('availability_skills', 'load_skill_levels', contextID, params);

                request.then(function(html, js) {
                    selector.set('innerHTML', html || emptylevel);

                    resolve(true);

                }).catch(reject);

            });
        });
    }

    /**
     * Set the level value on initial for the modules already setup the availability with skills.
     *
     * @param {HTMLElement} selector Created node.
     * @param {int} initialValue Initial value of the level if already setup
     */
    function setInitialValue(selector, initialValue) {
        // Set the initial value for levels.
        if (initialValue !== undefined) {
            selector.one('select[name=level]').set('value', initialValue);
        }
    }

    var contextID = this.contextID;

    // Create HTML structure
    // Add to the choose skill in the html node.
    var html = '<span class="col-form-label pr-3"> ' + getString('title') + '</span>' +
               '<span class="availability-group form-group"><label> <span class="accesshide">' + getString('chooseskill') +
                '</span><select class="custom-select" name="skills" title="' + getString('chooseskill') + '">' +
                '<option value="0">' + getString('choosedots', 'moodle') + '</option>';
    Y.each(this.skills, function(skill) {
        html += '<option value="' + skill.id + '">' + skill.name + '</option>';
    });

    // Added the choose condition type to the html node.
    html += '</select></label> <label> <span class="accesshide">' + getString('choosetype') + " </span>" +
            '<select class="custom-select" ' + 'name="ct" title="' + getString('choosetype') + '">' +
            '<option value="0">' + getString('notinlevel') + '</option>' +
            '<option value="1">' + getString('exactlevel') + '</option>' +
            '<option value="2">' + getString('selectlevelorhigher') + '</option>' +
            '<option value="3">' + getString('selectlevelorlower') + '</option>' +
            '<option value="4">' + getString('exactpoints') + '</option>' +
            '<option value="5">' + getString('moreorequalpoints') + '</option>' +
            '<option value="6">' + getString('lesspoints') + '</option>' +
            '</select>';

    // Choose level option placeholder, used as empty value.
    var emptylevel = '<option value="0">' + getString('choosedots', 'moodle') + '</option>';

    // Add the level.
    html += '<span class="accesshide">' + getString('chooselevel') + '</span>'
        + '<select class="custom-select" name="level" title="' + getString('chooselevel') + '">' + emptylevel
        + '</select></label> <br><br>';

    // Add the points input html node.
    html += '<span class="accesshide">' + getString('points') + '</span>' +
            '<input name="points" type="text" class="form-control" style="width: 10em" title="' +
            getString('points') + '" placeholder="' + getString('points') + '" /></label></span>';

    // Created the html node.
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial skill value.
    if (json.skill !== undefined &&
        node.one('select[name=skills] > option[value=' + json.skill + ']')) {
        node.one('select[name=skills]').set('value', '' + json.skill);
    }
    // Set the initial value of the condition type.
    if (json.ct !== undefined) {
        node.one('select[name=ct]').set('value', '' + json.ct);
    }
    // Se the initial value for points.
    if (json.points !== undefined) {
        node.one('input[name=points]').set('value', json.points);
    }

    // Create a event listner on condition type changes and show/hide the points and level inputs based on the value.
    var conditionType = node.one('select[name=ct]');
    updatePointsVisibility(conditionType.get('value')); // Update the visiblity after creating elements.
    conditionType.on('change', function(e) {
        var value = e.target.get('value');
        updatePointsVisibility(value);
    });

    // Update the selected skills related levels to the level condition selector element.
    var select = node.one('select[name=skills]');
    var levelselect = node.one('select[name=level]');
    // Update the levels after the skill condition is selected, only for the first time.
    var skill = select.get('value');
    updateSkillLevels(skill, levelselect, contextID, emptylevel).then(function() {
        setInitialValue(node, json.level);
        return;
    }).catch();

    // Event listner to init the levels option element update
    select.on('change', function(e) {
        var value = e.target.get('value');
        updateSkillLevels(value, levelselect, contextID, emptylevel);
    });

    // Add event handlers (first time only).
    if (!M.availability_skills.form.addedEvents) {
        M.availability_skills.form.addedEvents = true;
        var root = Y.one('.availability-field');

        root.delegate('change', function() {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_skills select');

        root.delegate('valuechange', function() {
            // Whichever the input value changed, just update the form.
            M.core_availability.form.update();
       }, '.availability_skills input[name=points]');
    }

    return node;
};

/**
 * Fill or fetch a value
 *
 * @param {Object} value
 * @param {Object} node
 */
M.availability_skills.form.fillValue = function(value, node) {

    // Skill
    value.skill = parseInt(node.one('select[name=skills]').get('value'), 10);
    // Type
    value.ct = parseInt(node.one('select[name=ct]').get('value'), 10);
    // Points
    value.points = parseInt(node.one('input[name=points]').get('value'), 10);
    // Levels.
    value.level = parseInt(node.one('select[name=level]').get('value'), 0);
};

/**
 * Fill errors
 *
 * @param {Array} errors
 * @param {Object} node
 */
M.availability_skills.form.fillErrors = function(errors, node) {

    // Doesn't select the skill, then the error will display.
    var skill = parseInt(node.one('select[name=skills]').get('value'), 10);
    if (skill === 0) {
        errors.push('availability_skills:error_select_skill_id');
    }

};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
