<?php
/**
 * Join a BigBlueButton room
 *
 * Authors:
 *    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 *    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)    
 * 
 * @package   mod_bigbluebuttonbn
 * @copyright 2010-2014 Blindside Networks Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // bigbluebuttonbn instance ID

if ($id) {
    $cm         = get_coursemodule_from_id('bigbluebuttonbn', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $bigbluebuttonbn  = $DB->get_record('bigbluebuttonbn', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $bigbluebuttonbn  = $DB->get_record('bigbluebuttonbn', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $bigbluebuttonbn->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('bigbluebuttonbn', $bigbluebuttonbn->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

if ( $CFG->version < '2013111800' ) {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    add_to_log($course->id, 'bigbluebuttonbn', 'view_end', "view.php?id=$cm->id", $bigbluebuttonbn->name, $cm->id);
} else {
    $context = context_module::instance($cm->id);
    $event = \mod_bigbluebuttonbn\event\bigbluebuttonbn_meeting_left::create(
            array(
                    'context' => $context,
                    'objectid' => $bigbluebuttonbn->id
                    )
            );
    $event->trigger();
}
$PAGE->set_context($context);

$PAGE->set_url('/mod/bigbluebuttonbn/view_end.php', array('id' => $cm->id));


if ( $bigbluebuttonbn->newwindow == 1 ){
    echo $OUTPUT->header();
    
    $PAGE->requires->js_init_call('M.mod_bigbluebuttonbn.viewend_CloseWindow');
    
    echo $OUTPUT->footer();
        
} else {
    if( has_capability('mod/bigbluebuttonbn:moderate', $context) )
        header('Location: '.$CFG->wwwroot.'/mod/bigbluebuttonbn/index.php?id='.$bigbluebuttonbn->course );
    else
        header('Location: '.$CFG->wwwroot.'/course/view.php?id='.$bigbluebuttonbn->course );
}
    
?>
