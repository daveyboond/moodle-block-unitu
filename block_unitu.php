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
     * Show What You Write interface
     *
     * @package    block
     * @subpackage unitu
     * @copyright  2013 Steve Bond
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */

class block_unitu extends block_base {

    public function init() {
        $this->title = get_string('unitu', 'block_unitu');
    }
    
    function applicable_formats() {
        return array('site' => false, 'my' => false, 'course-view' => true);
    }
    
    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.
    public function get_content() {
        global $COURSE;
        
        $remoteurl = 'http://unitu.co.uk/api/moodle/3?code=';
        
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass;
        $this->content->items  = array();
        $this->content->icons  = array();
        $this->content->footer = '';

// Capability test here - decide which one is appropriate
/*        if (has_capability('moodle/site:viewreports', $PAGE->context)) { // Basic capability for listing of reports.
        } */
        
        // Get current course code or ID if set
        $coursecode = empty($COURSE->idnumber) ? $COURSE->shortname : $COURSE->idnumber;
        
        // Make the call to Unitu
        $ch = curl_init();
        $url = $remoteurl . strtoupper($coursecode);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $returnval = curl_exec($ch);
        $curlreport = curl_error($ch);
        curl_close($ch);
        
        // Check whether Unitu sent a response
        if (empty($curlreport)) {
            $response = json_decode($returnval, true);
        } else {
            $this->content->text = $curlreport;
            return $this->content;
        }
        
        // Check if course exists and information returned
        if (isset($response['Error'])) {
            //  Course does not exist        
            $this->content->text = $response['Error'];
        } else {
            // Output block content
            $this->content->text = '<a href="'.$response['Url']
                .'" target="_blank">'.$coursecode.'</a> is on Unitu.<br /><br />Users: <a href="'
                .$response['UsersUrl'].'" target="_blank">'.$response['Users']
                .'</a><br />Questions: '.$response['Questions'].'<br />Posts: '
                .$response['Posts'];
        }

        return $this->content;
    // }
    }
}   // Here's the closing bracket for the class definition.
