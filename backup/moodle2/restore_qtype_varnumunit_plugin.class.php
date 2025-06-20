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
 * Restore plugin class that provides the necessary information.
 *
 * @package    qtype_varnumunit
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore plugin class that provides the necessary information
 * needed to restore one varnumunit qtype plugin.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_varnumunit_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure() {

        $paths = [];

        // This qtype uses question_answers, add them.
        $this->add_question_question_answers($paths);

        $elements = ['qtype_varnumunit' => '/varnumunit',
                            'qtype_varnumunit_answer' => '/varnumunit_answers/varnumunit_answer',
                            'qtype_varnumunit_var' => '/vars/var',
                            'qtype_varnumunit_variant' => '/vars/var/variants/variant',
                            'qtype_varnumunit_unit' => '/units/unit'];
        foreach ($elements as $elename => $path) {
            $elepath = $this->get_pathfor($path);
            $paths[] = new restore_path_element($elename, $elepath);
        }

        return $paths; // And we return the interesting paths.
    }

    #[\Override]
    public static function convert_backup_to_questiondata(array $backupdata): \stdClass {
        $questiondata = parent::convert_backup_to_questiondata($backupdata);
        $qtype = $questiondata->qtype;
        if (isset($backupdata["plugin_qtype_{$qtype}_question"]['varnumunit'])) {
            $questiondata->options = (object) array_merge(
                (array) $questiondata->options,
                $backupdata["plugin_qtype_{$qtype}_question"]['varnumunit'][0],
            );
        }

        if (isset($backupdata["plugin_qtype_{$qtype}_question"]['varnumunit_answers']['varnumunit_answer'])) {
            $dataanswers = $backupdata["plugin_qtype_{$qtype}_question"]['varnumunit_answers']['varnumunit_answer'];
            foreach ($dataanswers as $varnumunitanswer) {
                foreach ($questiondata->options->answers as &$answer) {
                    if ($answer->id == $varnumunitanswer['answerid']) {
                        $answer->sigfigs = $varnumunitanswer['sigfigs'];
                        $answer->error = $varnumunitanswer['error'];
                        $answer->syserrorpenalty = $varnumunitanswer['syserrorpenalty'];
                        $answer->checknumerical = $varnumunitanswer['checknumerical'];
                        $answer->checkscinotation = $varnumunitanswer['checkscinotation'];
                        $answer->checkpowerof10 = $varnumunitanswer['checkpowerof10'];
                        $answer->checkrounding = $varnumunitanswer['checkrounding'];
                        $answer->checkscinotationformat = $varnumunitanswer['checkscinotationformat'];
                        continue 2;
                    }
                }
            }
        }

        if (isset($backupdata["plugin_qtype_{$qtype}_question"]['units']['unit'])) {
            $questiondata->options->units = [];
            foreach ($backupdata["plugin_qtype_{$qtype}_question"]['units']['unit'] as $varnumunit) {
                $questiondata->options->units[] = (object) $varnumunit;
            }
        }
        return $questiondata;
    }

    #[\Override]
    public static function remove_excluded_question_data(stdClass $questiondata, array $excludefields = []): stdClass {
        // Option recalculateeverytime default is null, we need to remove it completely.
        unset($questiondata->options->recalculateeverytime);
        return parent::remove_excluded_question_data($questiondata, $excludefields);
    }

    /**
     * Process the qtype/varnumunit element.
     *
     * @param array $data The data to process.
     */
    public function process_qtype_varnumunit($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        // If the question has been created by restore, we need to create its
        // question_varnumunit too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $newquestionid;

            // Insert record.
            $newitemid = $DB->insert_record('qtype_varnumunit', $data);
            // Create mapping.
            $this->set_mapping('qtype_varnumunit', $oldid, $newitemid);
        }
    }

    /**
     * Process the qtype/varnumunit_answer element.
     *
     * @param array $data The data to process.
     */
    public function process_qtype_varnumunit_answer($data) {
        global $DB;

        $data = (object)$data;

        $data->answerid = $this->get_mappingid('question_answer', $data->answerid);

        // Detect if the question is created.
        $oldquestionid   = $this->get_old_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;
        if ($questioncreated) {
            // Insert record.
            $newitemid = $DB->insert_record('qtype_varnumunit_answers', $data);
            // Create mapping.
            $this->set_mapping('qtype_varnumunit_answer', $data->id, $newitemid);
        }
    }

    /**
     * Process the qtype/varnumunit_var element.
     *
     * @param array $data The data to process.
     */
    public function process_qtype_varnumunit_var($data) {
        global $DB;

        $data = (object)$data;

        // Detect if the question is created.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;
        if ($questioncreated) {
            $data->questionid = $newquestionid;
            // Insert record.
            $newitemid = $DB->insert_record('qtype_varnumunit_vars', $data);
            // Create mapping.
            $this->set_mapping('qtype_varnumunit_var', $data->id, $newitemid);
        }
    }

    /**
     * Process the qtype/varnumunit_unit element.
     *
     * @param array $data The data to process.
     */
    public function process_qtype_varnumunit_unit($data) {
        global $DB;

        $data = (object)$data;

        // Detect if the question is created.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;
        if ($questioncreated) {
            $data->questionid = $newquestionid;
            // Check for value removespace if restore from legacy course.
            if (isset($data->removespace)) {
                $data->spaceinunit = $data->removespace;
                $data->spacingfeedback = '';
            }
            // Insert record.
            $newitemid = $DB->insert_record('qtype_varnumunit_units', $data);
            // Create mapping.
            $this->set_mapping('qtype_varnumunit_unit', $data->id, $newitemid);
        }
    }

    /**
     * Process the qtype/varnumunit_variant element.
     *
     * @param array $data The data to process.
     */
    public function process_qtype_varnumunit_variant($data) {
        global $DB;

        $data = (object)$data;

        $data->varid = $this->get_new_parentid('qtype_varnumunit_var');

        // Detect if the question is created.
        $oldquestionid   = $this->get_old_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;
        if ($questioncreated) {

            // Insert record.
            $newitemid = $DB->insert_record('qtype_varnumunit_variants', $data);
            // Create mapping.
            $this->set_mapping('qtype_varnumunit_variant', $data->id, $newitemid);
        }
    }
}
