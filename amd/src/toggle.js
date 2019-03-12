define(['jquery', 'jqueryui'], function($) {
    /*eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {
// ---------------------------------------------------------------------------------------------------------------------
            // rumdidumdidum
            var toggleSection = function() { $(".toggler").on('click', function() {
                if ($(this).parent().parent().find('.summary').hasClass('hidden')) {
                    $(this).parent().parent().find('.summary').removeClass('hidden').show();
                    $(this).parent().parent().find('.toggler_closed').hide();
                    $(this).parent().parent().find('.toggler_open').show();
                } else {
                    $(this).parent().parent().find('.summary').addClass('hidden').hide();
                    $(this).parent().parent().find('.toggler_open').hide();
                    $(this).parent().parent().find('.toggler_closed').show();
                }

                // Now get the toggler status of each section
                console.log("no of sections = " + $('.section').length);
                var toggle_seq = '';
                $(".sectiondraggable").each(function() {
                    if ( $(this).find('.summary').hasClass('hidden')) {
                        toggle_seq = toggle_seq + '0';
                    } else {
                        toggle_seq = toggle_seq + '1';
                    }
                });
                console.log('toggle_seq = ' + toggle_seq);
            });};


// ---------------------------------------------------------------------------------------------------------------------
            var initFunctions = function() {
                // Load all required functions above
                toggleSection();
            };

// ---------------------------------------------------------------------------------------------------------------------
            $(document).ready(function() {

                console.log('=================< tabbedtopics/toggle.js >=================');
                initFunctions();

            });
        }
    };
});
