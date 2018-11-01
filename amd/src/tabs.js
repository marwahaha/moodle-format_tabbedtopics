define(['jquery', 'jqueryui'], function($) {
    /*eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {
// ---------------------------------------------------------------------------------------------------------------------
            var change_tab = function(tab, target) {
                console.log('single section in tab - performing some magic...');
                // if the section is collapsed click it to un-collapse
                target.find('.toggle_closed').click();

                // turn the section head into a temp headline
                target.find('.sectionhead').addClass('temp-headline');
                $('.temp-headline').removeClass('sectionhead');
                $('.temp-headline').removeClass('toggle-switch');
                target.find('.the_toggle').addClass('the_toggle_disabled');
                $('.the_toggle_disabled').removeClass('the_toggle');

                // replace the tab name with the section title
                var orig_sectionname=target.find('.sectionname');
                if($("input[name='edit']").val() === 'off') { // 'off' actually means 'on' ?!?!
                    var old_title = tab.find("span[data-itemtype='tabname']");
                    old_title.addClass('d-inline-flex-disabled');
                    old_title.removeClass('d-inline-flex');
                    old_title.hide();
                    tab.append(orig_sectionname.html());
                    orig_sectionname.addClass('hidden_sectionname');
                    orig_sectionname.removeClass('sectionname');
                    orig_sectionname.hide();
                } else {
                    tab.html(orig_sectionname.html());
                    orig_sectionname.hide();
                }
            };

// ---------------------------------------------------------------------------------------------------------------------
            var restore_section = function(target) {
                // reveal the original sectionname
                var orig_sectionname=target.find('.hidden_sectionname');
                orig_sectionname.addClass('sectionname');
                orig_sectionname.removeClass('hidden_sectionname');
                orig_sectionname.show();

                // turn the temp headline back into a section head
                $('.temp-headline').addClass('sectionhead');
                $('.temp-headline').addClass('toggle-switch');
                $('.temp-headline').removeClass('temp-headline');
                $('.the_toggle_disabled').addClass('the_toggle');
                $('.the_toggle_disabled').removeClass('the_toggle_disabled');
                console.log('--> restoring section headline ');
//                console.log(target);
//                console.log(orig_sectionname);
            };

// ---------------------------------------------------------------------------------------------------------------------
            var restore_tab = function(tab) {
                // replace any section title as tab title with the real tab title again
                var old_title = tab.find("span[data-itemtype='tabname']");
                tab.find("span[data-itemtype='sectionname']").first().remove();
                old_title.removeClass('d-inline-flex-disabled');
                old_title.addClass('d-inline-flex');
                old_title.show();
            };

// ---------------------------------------------------------------------------------------------------------------------
            // react to a clicked tab
            var tabclick = function() {$(".tablink").on('click', function() {
                var tabid = $(this).attr('id');
                var sections = $(this).attr('sections');
                var section_array = sections.split(",");

                console.log('----');

                var clicked_tab_name;
                if($(this).find('.inplaceeditable-text')) {
                    clicked_tab_name = $(this).find('.inplaceeditable-text').attr('data-value');
                }
                if (typeof clicked_tab_name == 'undefined') {
                    clicked_tab_name = $(this).html();
                }
                console.log('Clicked tab "'+clicked_tab_name+'":');

                // hide the content of the assessment info block tab
                $('.assessment_info_content').hide();

                $(".active").removeClass("active");
                $(".modulecontent").addClass("active");

                if(tabid === 'tab0') { // Show all sections - then hide each section shown in other tabs
                    $("#changenumsections").show();
                    $("li.section").show();
                    $(".topictab").each(function(){
                        if($(this).attr('sections').length > 0) {
                            // if any split sections into an array, loop through it and hide sectoon with the found ID
                            $.each($(this).attr('sections').split(","), function(index, value){
                                var target = $(".section[section-id='"+value+"']");
                                target.hide();
                                console.log("hiding section " + value);
                            });
                        }
                    });
                } else { // Hide all sections - then show those found in section_array
                    $("#changenumsections").show();
                    $("li.section").hide();
                    $.each(section_array, function(index, value){
                        var target = $(".section[section-id='"+value+"']");
                        target.show();
                        console.log("showing section " + value);
                    });
                }

                if ($('.section0_ontop'.length > 0)) {
                    $('#section-0').removeClass('sectionxxx');
                }

                // show section-0 when it should be shown always
                $('.section0_ontop #section-0').show();

                var visible_sections=$('li.section:visible').length;
                var hidden_sections=$('li.section.hidden:visible').length;
                if ($('.section0_ontop').length > 0) {
                    visible_sections--;
                }
                console.log('number of visible sections: '+visible_sections);
                console.log('number of hidden sections: '+hidden_sections);

                if(visible_sections <= hidden_sections) {
                    $(this).addClass('tab-not-shown');
                    if($('#not-shown-hint-'+tabid).length === 0) {
                        var hint_text = "This tab contains only hidden sections and will not be shown to students";
                        $(this).append('<i id="not-shown-hint-'+tabid+'" class="fa fa-info" title="'+hint_text+'"></i>');
                    }
                } else {
                    $(this).removeClass('tab-not-shown');
                    $('#not-shown-hint-'+tabid).remove();
                }

                if(visible_sections < 1) {
                    console.log('tab with no visible sections or blocks - hiding it');
                    $(this).parent().hide();
                } else {
                    console.log('tab with visible sections or blocks - showing it');
                    $(this).parent().show();
                }

                if($('.tabtopics').length > 0) {
                    console.log('--> # of tabtopics: '+$('.tabtopics').length);
                }

                // if option is set when a tab shows a single section only perform some visual tricks
                if($('.single_section_tab').length  > 0) {
                    var target = $('li.section:visible').first();
                    var first_section_id = $('li.section:visible').first().attr('id');
                    if(visible_sections === 1 && first_section_id != 'section-0' &&
                        !$('li.section:visible').first().hasClass('hidden')&&
                        !$('li.section:visible').first().hasClass('stealth')) {
                        change_tab($(this), target);
                    } else if($("input[name='edit']").val() === 'off' && first_section_id != 'section-0') {
                        restore_tab($(this));
                        restore_section(target);
                    }
                }
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // moving a section to a tab by menu
            var tabmove = function () { $(".tab_mover").on('click', function(){
                var tabnum = $(this).attr('tabnr');  // this is the tab number where the section is moved to
                var sectionid = $(this).closest('li.section').attr('section-id');
                var sectionnum = $(this).parent().parent().parent().parent().parent().parent().parent().attr('id').substring(8);
                console.log('--> found section num: '+sectionnum);
                var active_tabid = $('.topictab.active').first().attr('id');
                var target = $(".section[section-id='"+sectionid+"']");

                if(typeof active_tabid == 'undefined') {
                    active_tabid = 'tab0';
                }
                console.log('----');
                console.log('moving section '+sectionid+' from tab "'+active_tabid+'" to tab nr '+tabnum);

                // remove the section id and section number from any tab
                $(".tablink").each(function(){
                    $(this).attr('sections',$(this).attr('sections').replace(","+sectionid,""));
                    $(this).attr('sections',$(this).attr('sections').replace(sectionid+",",""));
                    $(this).attr('sections',$(this).attr('sections').replace(sectionid,""));

                    $(this).attr('section_nums',$(this).attr('section_nums').replace(","+sectionnum,""));
                    $(this).attr('section_nums',$(this).attr('section_nums').replace(sectionnum+",",""));
                    $(this).attr('section_nums',$(this).attr('section_nums').replace(sectionnum,""));
                });
                // now add the sectionid to the new tab
                if(tabnum > 0) { // no need to store section ids for tab 0
                    if($("#tab"+tabnum).attr('sections').length === 0) {
                        $("#tab"+tabnum).attr('sections', $("#tab"+tabnum).attr('sections')+sectionid);
                    } else {
                        $("#tab"+tabnum).attr('sections', $("#tab"+tabnum).attr('sections')+","+sectionid);
                    }
                    if($("#tab"+tabnum).attr('section_nums').length === 0) {
                        $("#tab"+tabnum).attr('section_nums', $("#tab"+tabnum).attr('section_nums')+sectionnum);
                    } else {
                        $("#tab"+tabnum).attr('section_nums', $("#tab"+tabnum).attr('section_nums')+","+sectionnum);
                        console.log('---> section_nums: '+$("#tab"+tabnum).attr('section_nums'));
                    }
                    $("#tab"+tabnum).click();
                    $('#'+active_tabid).click();
                }

                //restore the section before moving it in case it was a single
                restore_tab($('#tab'+tabnum));
                restore_section(target);

                // when there is no visible tab hide tab0 and show/click the module content tab
                // and vice versa otherwise...
                var visible_tabs = $(".topictab:visible").length;
                console.log('visible tabs: '+visible_tabs);
                if($(".topictab:visible").length > 0) {
                    // if the last section of a tab was moved click the target tab
                    // otherwise click the active tab to refresh it
                    if($('li.section:visible').length > 1) {
                        console.log('staying with the current tab (id = '+active_tabid+
                            ') as there are still '+$('li.section:visible').length+' sections left');
                        $("#tab"+tabnum).click();
                        $('#'+active_tabid).click();
                    } else {
                        console.log('no section in active tab id '+
                            active_tabid+' left - hiding it and following section to new tab nr '+tabnum);
                        $("#tab"+tabnum).click();
                        $('#'+active_tabid).parent().hide();
                    }
                }
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // what to do if a tab has been dropped onto another
            var handleTabDropEvent = function ( event, ui ) {
                var dragged_tab = ui.draggable.find('.topictab').first();
                var target_tab = $(this).find('.topictab').first();
                var dragged_tab_id = ui.draggable.find('.topictab').first().attr('id');
                var target_tab_id = $(this).find('.topictab').first().attr('id');
//    alert('The tab with ID "' + dragged_tab_id + '" was dropped onto tab with the ID "' + target_tab_id + '"');
                console.log('The tab with ID "' + dragged_tab_id + '" was dropped onto tab with the ID "' + target_tab_id + '"');
                // swap both tabs
                var zwischenspeicher = dragged_tab.parent().html();
                dragged_tab.parent().html(target_tab.parent().html());
                target_tab.parent().html(zwischenspeicher);

                // re-instantiate the clickability for the just added DOM elements
                tabclick();
                tabmove();

                // get the new tab sequence and write it back to format options
                var tabSeq = '';
                // get the id of each tab according to their position (left to right)
                $('.tablink').each(function(){
//        if(!$(this).hasClass('tab-not-shown')) {
                    var tabid = $(this).attr('id');
                    if(tabid !== undefined) {
                        if(tabSeq === '') {
                            tabSeq = $(this).attr('id');
                        } else {
                            tabSeq = tabSeq.concat(',').concat($(this).attr('id'));
                        }
                    }
                });

                // get the first section id from the 1st visible tab - this will be used to determine the course ID
                var sectionid = $('.topictab:visible').first().attr('sections').split(',')[0];
                if(sectionid === 'block_assessment_information') {
                    sectionid = $('.topictab:visible:eq(1)').attr('sections').split(',')[0];
                }

                // finally call php to write the data
                $.ajax({
                    url: "format/tabtopics/ajax/update_tab_seq.php",
                    type: "POST",
                    data: {'sectionid':sectionid, 'tab_seq':tabSeq},
                    success: function(result){
                        console.log('the new tab sequence: '+result);
                    }});
            };

// ---------------------------------------------------------------------------------------------------------------------
            // a section name is clicked...
            $(".list-group-item").click(function(){
                var sectionname = $(this).find('.media-body').html();
                var sectionnum = $('.section[aria-label="'+sectionname+'"]').attr('section-id');
                // find the tab in which the section is
                var found_it = false;
                $('.tablink').each(function(){
                    if($(this).attr('sections').indexOf(sectionnum) > -1){
                        $(this).click();
                        found_it = true;
                        return false;
                    }
                });
                if(!found_it) {
                    $('#tab0').click();
                }
            });

// ---------------------------------------------------------------------------------------------------------------------
            $(document).ready(function() {
                tabclick();
                tabmove();

                //show the edit menu for section-0 - but not when it's shown above the tabs
                if($('.section0_ontop').length === 0) {
                    $("#section-0 .right.side").show();
                }

                // make tabs draggable when in edit mode (the pencil class is present)
                if($('.tablink .fa-pencil').length > 0) {

                    $('.topictab').parent().draggable({
//                        containment: '.tabs', // allow moving only within tabs
//                        helper: 'clone', // move a clone
                        cursor: 'move',
                        stack: '.tabitem', // make sure the dragged tab is always on top of others
//                        snap: '.tabitem',
                        revert: true,
                    });
                    $('.topictab').parent().droppable({
                        accept: '.tabitem',
                        hoverClass: 'hovered',
                        drop: handleTabDropEvent,
                    });
                }

                // if there is no visible tab show/click the module content tab
                if($(".topictab:visible").length > 0) {
                    //click all visible tablinks once to potentially reveal any section names as tab names
                    $('#tab0').click();
                    $('.tablink:visible').click();
                    // click the 1st visible tab by default
                    $('.tablink:visible').first().click();
                }
            });
        }
    };
});
