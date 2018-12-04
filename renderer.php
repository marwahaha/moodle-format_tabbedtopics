<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renderer for outputting the tabbedtopics course format.
 *
 * @package format_tabbedtopics
 * @copyright 2012 Dan Poltawski / 2018 Matthias Opitz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

/**
 * Basic renderer for tabbedtopics format.
 * with added tab-ability
 *
 * @copyright 2012 Dan Poltawski / 2018 Matthias Opitz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_tabbedtopics_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_tabbedtopics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    function start_section_list() {
        global $PAGE;
        return html_writer::start_tag('ul', array('class' => 'tabbedtopics'));
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $CFG, $DB, $PAGE;

        // allow up to 5 user tabs if nothing else is set in the config file
        $max_tabs = (isset($CFG->max_tabs) ? $CFG->max_tabs : 5);
        $tabs = array();

        $this->page->requires->js_call_amd('format_tabbedtopics/tabs', 'init', array());

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $options = $DB->get_records('course_format_options', array('courseid' => $course->id));
        $format_options=array();
        foreach($options as $option) {
            $format_options[$option->name] =$option->value;
        }

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now on to the main stage..
        $numsections = course_get_format($course)->get_last_section_number();
        $sections = $modinfo->get_section_info_all();

        // display section-0 on top of tabs if option is checked
        if($format_options['section0_ontop']) {
            echo html_writer::start_tag('div', array('id' => 'ontop_area', 'class' => 'section0_ontop'));
            $section0 = $sections[0];
//            echo $this->start_section_list();
            if($format_options['single_section_tabs']) {
                echo html_writer::start_tag('ul', array('class' => 'tabbedtopics single_section_tabs'));
            } else {
                echo html_writer::start_tag('ul', array('class' => 'tabbedtopics'));
            }

            // 0-section is displayed a little different then the others
            if ($section0->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                echo $this->section0_ontop_header($section0, $course, false, 0);
                echo $this->courserenderer->course_section_cm_list($course, $section0, 0);
                echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                echo $this->section_footer();
            }
            echo $this->end_section_list();
        } else {
            echo html_writer::start_tag('div', array('id' => 'ontop_area'));
        }

        echo html_writer::end_tag('div');

        // the tab navigation
        $result = prepare_tabs($course, $format_options, $sections);
        $tabs = $result['tabs'];

        // rendering the tab navigation
        echo html_writer::start_tag('ul', array('class'=>'tabs nav nav-tabs row'));

        $tab_seq = array();
        if ($format_options['tab_seq']) {
            $tab_seq = explode(',',$format_options['tab_seq']);
        }

        // if a tab sequence is found use it to arrange the tabs otherwise show them in default order
        if(sizeof($tab_seq) > 0) {
            foreach ($tab_seq as $tabid) {
                $tab = $tabs[$tabid];
                render_tab($tab);
            }
        } else {
            foreach ($tabs as $tab) {
                render_tab($tab);
            }
        }
        echo html_writer::end_tag('ul');

        // the sections
//        echo $this->start_section_list();
        if($format_options['single_section_tabs']) {
            echo html_writer::start_tag('ul', array('class' => 'tabbedtopics single_section_tabs'));
        } else {
            echo html_writer::start_tag('ul', array('class' => 'tabbedtopics'));
        }

        foreach ($sections as $section => $thissection) {
            if ($section == 0) {
                echo html_writer::start_tag('div', array('id' => 'inline_area'));
                if($format_options['section0_ontop']){ // section-0 is already shown on top
                    echo html_writer::end_tag('div');
                    continue;
                }
                // 0-section is displayed a little different then the others
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                }
                echo html_writer::end_tag('div');
                continue;
            }
            if ($section > $numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available
            // but there is some available info text which explains the reason & should display,
            // OR it is hidden but the course has a setting to display hidden sections as unavilable.
            $showsection = $thissection->uservisible ||
                ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo)) ||
                (!$thissection->visible && !$course->hiddensections);
            if (!$showsection) {
                continue;
            }

            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }
                echo $this->section_footer();
            }
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();

            echo $this->change_number_sections($course, 0);
        } else {
            echo $this->end_section_list();
        }

    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $CFG, $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $max_tabs = (isset($CFG->max_tabs) ? $CFG->max_tabs : 5);
        $max_tabs = ($max_tabs < 10 ? $max_tabs : 9 ); // Restrict tabs to 10 max (0...9)
        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();

        // add move to/from top for section0 only
        if ($section->section === 0) {
            $controls['ontop'] = array(
                "icon" => 't/up',
                'name' => 'Show always on top',

                'attr' => array(
                    'tabnr' => 0,
                    'class' => 'ontop_mover',
                    'title' => 'Show always on top',
                    'data-action' => 'sectionzeroontop'
                )
            );
            $controls['inline'] = array(
                "icon" => 't/down',
                'name' => 'Show inline',

                'attr' => array(
                    'tabnr' => 0,
                    'class' => 'inline_mover',
                    'title' => 'Show inline',
                    'data-action' => 'sectionzeroinline'
                )
            );
        }

        // Insert tab moving menu items
        $controls['no_tab'] = array(
            "icon" => 't/left',
            'name' => 'Remove from Tabs',

            'attr' => array(
                'tabnr' => 0,
                'class' => 'tab_mover',
                'title' => 'Remove from Tabs',
                'data-action' => 'removefromtabs'
            )
        );

        $itemtitle = "Move to Tab ";
        $actions = array('movetotabzero', 'movetotabone', 'movetotabtwo','movetotabthree','movetotabfour','movetotabfive','movetotabsix','movetotabseven','movetotabeight','movetotabnine','movetotabten', 'sectionzeroontop', 'sectionzeroinline');
        for($i = 1; $i <= $max_tabs; $i++) {
            $tabname = 'tab'.$i.'_title';
            $itemname = 'To Tab "'.($course->$tabname ? $course->$tabname : $i).'"';

            $controls['to_tab'.$i] = array(
                "icon" => 't/right',
                'name' => $itemname,

                'attr' => array(
                    'tabnr' => $i,
                    'class' => 'tab_mover',
                    'title' => $itemtitle,
                    'data-action' => $actions[$i]
                )
            );
        }

        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic,
                                                   'data-action' => 'removemarker'));
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic,
                                                   'data-action' => 'setmarker'));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;

        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section, 'section-id' => $section->id,
            'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)));

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        $o.= $this->output->heading($sectionname, 3, 'sectionname' . $classes);

        $o .= $this->section_availability($section);

        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        if ($section->uservisible || $section->visible) {
            // Show summary if section is available or has availability restriction information.
            // Do not show summary if section is hidden but we still display it because of course setting
            // "Hidden sections are shown in collapsed form".
            $o .= $this->format_summary_text($section);
        }
        $o .= html_writer::end_tag('div');

        return $o;
    }

    protected function section0_ontop_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;

        $o = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section, 'section-id' => $section->id,
            'class' => 'section ontop main clearfix'.$sectionstyle, 'role'=>'region',
            'aria-label'=> get_section_name($course, $section)));

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        $o.= $this->output->heading($sectionname, 3, 'sectionname' . $classes);

//        $o .= $this->section_availability($section);

//        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        if ($section->uservisible || $section->visible) {
            // Show summary if section is available or has availability restriction information.
            // Do not show summary if section is hidden but we still display it because of course setting
            // "Hidden sections are shown in collapsed form".
            $o .= $this->format_summary_text($section);
        }
//        $o .= html_writer::end_tag('div');

        return $o;
    }

}

function prepare_tabs($course, $format_options, $sections) {
    global $CFG, $DB, $PAGE;

    // allow up to 5 user tabs if nothing else is set in the config file
    $max_tabs = (isset($CFG->max_tabs) ? $CFG->max_tabs : 5);
    $tabs = array();

    // preparing the tabs
    $count_tabs = 0;
    for ($i = 0; $i <= $max_tabs; $i++) {
        $tab_sections = str_replace(' ', '', $format_options['tab' . $i]);
        $tab_section_nums = str_replace(' ', '', $format_options['tab' . $i. '_sectionnums']);

        // check section IDs and section numbers for tabs other than tab0
        if($i > 0) {
            $section_ids = explode(',', $tab_sections);
            $section_nums = explode(',', $tab_section_nums);
            $tab_sections = check_section_ids($course->id, $sections, $section_ids, $section_nums, $tab_sections, $tab_section_nums,$i);
        }

        $tab = new stdClass();
        $tab->id = "tab" . $i;
        $tab->name = "tab" . $i;
        $tab->title = $format_options['tab' . $i . '_title'];
        $tab->sections = $tab_sections;
        $tab->section_nums = $tab_section_nums;
        $tabs[$tab->id] = $tab;
        if ($tab_sections != null) {
            $count_tabs++;
        }
    }
    return array('tabs' => $tabs, 'count_tabs' => $count_tabs);
}

function render_tab($tab) {
    global $DB, $PAGE, $OUTPUT;
    if($tab->sections == '') {
        echo html_writer::start_tag('li', array('class'=>'tabitem nav-item', 'style' => 'display:none;'));
    } else {
        echo html_writer::start_tag('li', array('class'=>'tabitem nav-item'));
    }

    $sections_array = explode(',', str_replace(' ', '', $tab->sections));
    if($sections_array[0]) {
        while ($sections_array[0] == "0") { // remove any occurences of section-0
            array_shift($sections_array);
        }
    }

    if($PAGE->user_is_editing()) {
        // get the format option record for the given tab - we need the id
        // if the record does not exist, create it first
        if(!$DB->record_exists('course_format_options', array('courseid' => $PAGE->course->id, 'name' => $tab->id.'_title'))) {
            $record = new stdClass();
            $record->courseid = $PAGE->course->id;
            $record->format = 'tabbedtopics';
            $record->section = 0;
            $record->name = $tab->id.'_title';
            $record->value = 'Tab '.substr($tab->id,3);
            $DB->insert_record('course_format_options', $record);
        }

        $format_option_tab = $DB->get_record('course_format_options', array('courseid' => $PAGE->course->id, 'name' => $tab->id.'_title'));
        $itemid = $format_option_tab->id;
    } else {
        $itemid = false;
    }

    if ($tab->id == 'tab0') {
        echo '<span data-toggle="tab" id="'.$tab->id.'" sections="'.$tab->sections.'" section_nums="'.$tab->section_nums.'" class="tablink nav-link " tab_title="'.$tab->title.'">';
    } else {
        echo '<span data-toggle="tab" id="'.$tab->id.'" sections="'.$tab->sections.'" section_nums="'.$tab->section_nums.'" class="tablink topictab nav-link " tab_title="'.$tab->title.'" style="'.($PAGE->user_is_editing() ? 'cursor: move;' : '').'">';
    }
    // render the tab name as inplace_editable
    $tmpl = new \core\output\inplace_editable('format_tabbedtopics', 'tabname', $itemid,
        $PAGE->user_is_editing(),
        format_string($tab->title), $tab->title, get_string('tabtitle_edithint', 'format_tabbedtopics'),  get_string('tabtitle_editlabel', 'format_tabbedtopics', format_string($tab->title)));
    echo $OUTPUT->render($tmpl);
    echo "</span>";
    echo html_writer::end_tag('li');
}

function check_section_ids($courseid, $sections, $section_ids, $section_nums, $tab_sections, $tab_section_nums, $i) {
    global $DB;
    // check section IDs are valid for this course - and repair them using section numbers if they are not
    $tab_format_record = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$i));
    $ids_have_changed = false;
    $new_section_nums = array();
    foreach($section_ids as $index => $section_id) {
        $section = $sections[$section_id];
        $new_section_nums[] = $section->section;
        if($section_id && !($section)) {
            $section = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $section_nums[$index]));
            $tab_sections = str_replace($section_id, $section->id, $tab_sections);
            $ids_have_changed = true;
        }
    }
    if($ids_have_changed) {
        $DB->update_record('course_format_options', array('id' => $tab_format_record->id, 'value' => $tab_sections));
    }
    else { // all IDs are good - so check stored section numbers and restore them with the real numbers in case they have changed
        $new_sectionnums = implode(',', $new_section_nums);
        if($tab_section_nums !== $new_sectionnums) { // the stored section numbers seems to be different
            if($DB->record_exists('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$i.'_sectionnums'))) {
                $tab_format_record = $DB->get_record('course_format_options', array('courseid' => $courseid, 'name' => 'tab'.$i.'_sectionnums'));
                $DB->update_record('course_format_options', array('id' => $tab_format_record->id, 'value' => $new_sectionnums));
            } else {
                $new_tab_format_record = new \stdClass();
                $new_tab_format_record->courseid = $courseid;
                $new_tab_format_record->format = 'tabbedtopics';
                $new_tab_format_record->sectionid = 0;
                $new_tab_format_record->name = 'tab'.$i.'_sectionnums';
                $new_tab_format_record->value = $new_sectionnums;
                $DB->insert_record('course_format_options', $new_tab_format_record);
            }
        }
    }
    return $tab_sections;
}