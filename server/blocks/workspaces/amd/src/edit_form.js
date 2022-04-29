define([], function () {
    return {
        init: function () {
            // Bind the event + trigger for the default view/state
            var type_field = document.getElementById('id_config_block_type');
            var ratings_field = document.getElementById('id_config_ratings');
            var change = function () {
                var type = parseInt(type_field.value, 10);
                if (type === 2 || type === 3) {
                    // Course or workspace, the likes/ratings should be disabled
                    ratings_field.setAttribute('disabled', 'disabled');
                } else {
                    ratings_field.removeAttribute('disabled');
                }
            };
            type_field.addEventListener('change', change);

            // Trigger it on first load
            change();
        }
    };
});