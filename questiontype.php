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
 * Question type class for the short answer question type.
 *
 * @package    qtype_varnumunit
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use qtype_varnumunit\qtype_varnumunit_unit;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/varnumunit/calculator.php');
require_once($CFG->dirroot . '/question/type/varnumericset/questiontypebase.php');

/**
 * The variable numeric with units question type.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_varnumunit extends qtype_varnumeric_base {

    /**
     * The calculator for this question type.
     *
     * @var int
     */
    const SUPERSCRIPT_SCINOTATION_REQUIRED = 2;

    /**
     * Superscript notation options.
     *
     * @var int
     */
    const SUPERSCRIPT_ALLOWED = 1;

    /**
     * Superscript notation options.
     *
     * @var int
     */
    const SUPERSCRIPT_NONE = 0;

    /**
     * Space in unit options.
     *
     * @var int
     */
    const SPACEINUNIT_REMOVE_ALL_SPACE = 1;

    /**
     * Space in unit options.
     *
     * @var int
     */
    const SPACEINUNIT_PRESERVE_SPACE_NOT_REQUIRE = 0;

    /**
     * Space in unit options.
     *
     * @var int
     */
    const SPACEINUNIT_PRESERVE_SPACE_REQUIRE = 2;

    #[\Override]
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->requirescinotation = ($questiondata->options->requirescinotation == self::SUPERSCRIPT_SCINOTATION_REQUIRED);
        $question->usesupeditor = $questiondata->options->requirescinotation == self::SUPERSCRIPT_SCINOTATION_REQUIRED ||
                        $questiondata->options->requirescinotation == self::SUPERSCRIPT_ALLOWED;
        $question->units = $questiondata->options->units;
    }

    #[\Override]
    public function recalculate_every_time() {
        return false;
    }

    #[\Override]
    public function db_table_prefix() {
        return 'qtype_varnumunit';
    }

    #[\Override]
    public function extra_question_fields() {
        return [$this->db_table_prefix(), 'randomseed', 'requirescinotation', 'unitfraction'];
    }

    /**
     * Delete files in units for a question.
     *
     * @param int $questionid The question ID.
     * @param int $contextid The context ID.
     */
    protected function delete_files_in_units($questionid, $contextid) {
        global $DB;
        $fs = get_file_storage();

        $tablename = $this->db_table_prefix().'_units';
        $unitids = $DB->get_records_menu($tablename, ['questionid' => $questionid], 'id', 'id,1');
        foreach ($unitids as $unitid => $notused) {
            $fs->delete_area_files($contextid, $this->db_table_prefix(), 'unitsfeedback', $unitid);
        }
    }

    /**
     * Move files in units from old context to new context.
     *
     * @param int $questionid The question ID.
     * @param int $oldcontextid The old context ID.
     * @param int $newcontextid The new context ID.
     */
    protected function move_files_in_units($questionid, $oldcontextid, $newcontextid) {
        global $DB;
        $fs = get_file_storage();
        $tablename = $this->db_table_prefix().'_units';
        $unitids = $DB->get_records_menu($tablename, ['questionid' => $questionid], 'id', 'id,1');
        foreach ($unitids as $unitid => $notused) {
            $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, $this->db_table_prefix(), 'unitsfeedback', $unitid);
        }
    }

    #[\Override]
    public function delete_question($questionid, $contextid) {
        global $DB;
        $tablename = $this->db_table_prefix().'_units';
        $DB->delete_records($tablename, ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    /**
     * Save the units for a question.
     *
     * @param stdClass $formdata The form data containing the units and their feedback.
     */
    public function save_units($formdata) {
        global $DB;
        $context = $formdata->context;
        $table = $this->db_table_prefix().'_units';
        $oldunits = $DB->get_records($table, ['questionid' => $formdata->id], 'id ASC');
        if (empty($oldunits)) {
            $oldunits = [];
        }

        if (!empty($formdata->units)) {
            $numunits = max(array_keys($formdata->units)) + 1;
        } else {
            $numunits = 0;
        }

        for ($i = 0; $i < $numunits; $i += 1) {
            if (empty($formdata->units[$i])) {
                continue;
            }
            if (html_is_blank($formdata->unitsfeedback[$i]['text'])) {
                $formdata->unitsfeedback[$i]['text'] = '';
            }
            if (!isset($formdata->spacesfeedback[$i]['text']) || html_is_blank($formdata->spacesfeedback[$i]['text'])) {
                $formdata->spacesfeedback[$i]['text'] = '';
            }
            $this->save_unit($table,
                            $context,
                            $formdata->id,
                            $oldunits,
                            $formdata->units[$i],
                            $formdata->unitsfeedback[$i],
                            $formdata->unitsfraction[$i],
                            $formdata->spaceinunit[$i],
                            $formdata->spacesfeedback[$i],
                            !empty($formdata->replacedash[$i]));

        }

        if (!html_is_blank($formdata->otherunitfeedback['text'])) {
            $this->save_unit($table,
                            $context,
                            $formdata->id,
                            $oldunits,
                            '*',
                            $formdata->otherunitfeedback,
                            0,
                            false,
                            ['text' => '', 'format' => FORMAT_HTML],
                            false);
        }
        // Delete any remaining old units.
        $fs = get_file_storage();
        foreach ($oldunits as $oldunit) {
            $fs->delete_area_files($context->id, $this->db_table_prefix(), 'unitsfeedback', $oldunit->id);
            $fs->delete_area_files($context->id, $this->db_table_prefix(), 'spacesfeedback', $oldunit->id);
            $DB->delete_records($table, ['id' => $oldunit->id]);
        }
    }

    /**
     * Save a unit to the database.
     *
     * @param string $table The database table name.
     * @param context $context The context for the question.
     * @param int $questionid The question ID.
     * @param array $oldunits The old units to be replaced.
     * @param string $unit The unit text.
     * @param array $feedback The feedback for the unit.
     * @param float $fraction The fraction for the unit.
     * @param int $spaceinunit The space in unit option.
     * @param array $spacingfeedback The spacing feedback for the unit.
     * @param bool $replacedash Whether to replace dashes in the unit.
     */
    public function save_unit($table, $context, $questionid, &$oldunits, $unit, $feedback, $fraction, $spaceinunit,
                              $spacingfeedback, $replacedash) {
        global $DB;
        // Update an existing unit if possible.
        $oldunit = array_shift($oldunits);
        if ($oldunit === null) {
            $unitobj = new stdClass();
            $unitobj->questionid = $questionid;
            $unitobj->unit = '';
            $unitobj->feedback = '';
            $unitobj->spacingfeedback = '';
            $unitobj->id = $DB->insert_record($table, $unitobj);
        } else {
            $unitobj = new stdClass();
            $unitobj->questionid = $questionid;
            $unitobj->unit = '';
            $unitobj->feedback = '';
            $unitobj->spacingfeedback = '';
            $unitobj->id = $oldunit->id;
        }

        $unitobj->unit = $unit;
        $unitobj->spaceinunit = $spaceinunit;
        $unitobj->replacedash = $replacedash;
        $unitobj->fraction = $fraction;
        $unitobj->feedback =
                        $this->import_or_save_files($feedback, $context, $this->db_table_prefix(), 'unitsfeedback', $unitobj->id);
        $unitobj->feedbackformat = $feedback['format'];
        $unitobj->spacingfeedback = $this->import_or_save_files($spacingfeedback, $context, $this->db_table_prefix(),
                'spacesfeedback', $unitobj->id);
        $unitobj->spacingfeedbackformat = $spacingfeedback['format'];

        $DB->update_record($table, $unitobj);
    }

    #[\Override]
    public function save_defaults_for_new_questions(stdClass $fromform): void {
        $grandparent = new question_type();
        $grandparent->save_defaults_for_new_questions($fromform);
        $this->set_default_value('unitfraction', $fromform->unitfraction);
    }

    #[\Override]
    public function save_question_options($form) {
        $parentresult = parent::save_question_options($form);
        if ($parentresult !== null) {
            // Parent function returns null if all is OK.
            return $parentresult;
        }
        $this->save_units($form);
        return null;
    }

    #[\Override]
    public function get_question_options($question) {
        parent::get_question_options($question);
        $this->load_units($question);
    }

    /**
     * Load the question units, as part of {@see get_question_options}.
     *
     * @param question_definition $questiondata The question data object to populate with units.
     */
    public function load_units($questiondata) {
        global $DB;
        $questiondata->options->units = [];
        foreach ($DB->get_records($this->db_table_prefix() . '_units',
                ['questionid' => $questiondata->id], 'id ASC') as $unitid => $unit) {
            $questiondata->options->units[$unitid] = new qtype_varnumunit_unit(
                $unit->id,
                $unit->unit,
                $unit->spaceinunit,
                $unit->spacingfeedback,
                $unit->spacingfeedbackformat,
                $unit->replacedash,
                $unit->fraction,
                $unit->feedback,
                $unit->feedbackformat);
        }
    }

    #[\Override]
    public function get_possible_responses($questiondata) {
        $parentresponses = parent::get_possible_responses($questiondata);
        $numericresponses = $parentresponses[$questiondata->id];

        $matchall = false;
        $unitresponses = [];
        foreach ($questiondata->options->units as $unitid => $unit) {
            if ('*' === $unit->unit) {
                $matchall = true;
            }
            $unitresponses[$unit->unit] = new question_possible_response($unit->unit, $unit->fraction);
        }
        if (!$matchall) {
            $unitresponses[0] = new question_possible_response($unit->unit, $unit->fraction);
        }
        $unitresponses[null] = question_possible_response::no_response();

        return ["unitpart" => $unitresponses,
                     "numericpart" => $numericresponses];
    }

    #[\Override]
    public function get_random_guess_score($questiondata) {
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ('*' == trim($answer->answer)) {
                return (1 - $questiondata->options->unitfraction) * $answer->fraction;
            }
        }
        return 0;
    }

    /**
     * Imports question from the Moodle XML format
     *
     * Imports question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     *
     * @param array $data The question data in XML format.
     * @param question_definition $question The question object to populate.
     * @param qformat_xml $format The format to use for importing.
     * @param mixed $extra Additional data that may be needed for the import.
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        $qo = parent::import_from_xml($data, $question, $format, $extra);
        if (!$qo) {
            return false;
        }

        if (isset($data['#']['unit'])) {
            $units = $data['#']['unit'];
            $unitno = 0;
            foreach ($units as $unit) {
                $unitname = $format->getpath($unit, ['#', 'units', 0, '#'], '', true);
                if ('*' !== $unitname) {
                    $qo->units[$unitno] = $unitname;
                    $qo->unitsfeedback[$unitno] = $this->import_html($format, $unit['#']['unitsfeedback'][0],
                        $qo->questiontextformat);
                    // Check for removespace if import from version 2014111200.
                    $removespace = $format->getpath($unit, ['#', 'removespace', 0, '#'], false);
                    if ($removespace !== false) {
                        $qo->spaceinunit[$unitno] = $removespace;
                        $qo->spacesfeedback[$unitno] = $this->import_html($format, '',
                                FORMAT_HTML);
                    } else {
                        $qo->spaceinunit[$unitno] = $format->getpath($unit, ['#', 'spaceinunit', 0, '#'], false);
                        $qo->spacesfeedback[$unitno] = $this->import_html($format, $unit['#']['spacesfeedback'][0],
                                $qo->questiontextformat);
                    }
                    $qo->replacedash[$unitno] = $format->getpath($unit, ['#', 'replacedash', 0, '#'], false);
                    $qo->unitsfraction[$unitno] = $format->getpath($unit, ['#', 'unitsfraction', 0, '#'], 0, true);
                    $unitno++;
                } else {
                    $qo->otherunitfeedback = $this->import_html($format, $unit['#']['unitsfeedback'][0], $qo->questiontextformat);
                }
            }
            if (!isset($qo->otherunitfeedback)) {
                $qo->otherunitfeedback = ['text' => '', 'format' => $qo->questiontextformat];
            }
        }
        return $qo;
    }

    /**
     * Import HTML content from the XML format.
     *
     * @param qformat_xml $format The format to use for importing.
     * @param array $data The data to import.
     * @param string $defaultformat The default format to use if not specified.
     */
    protected function import_html(qformat_xml $format, $data, $defaultformat) {
        $text = [];
        $text['text'] = $format->getpath($data, ['#', 'text', 0, '#'], '', true);
        $text['format'] = $format->trans_format($format->getpath($data, ['@', 'format'],
                                                $format->get_format($defaultformat)));
        $text['files'] = $format->import_files($format->getpath($data, ['#', 'file'], [], false));

        return $text;
    }

    #[\Override]
    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        $expout = parent::export_to_xml($question, $format, $extra);
        $units = $question->options->units;
        foreach ($units as $unit) {
            $expout .= "    <unit>\n";
            $fields = [
                'units'         => 'unit',
                'unitsfraction' => 'fraction',
                'spaceinunit'   => 'spaceinunit',
                'replacedash'   => 'replacedash',
            ];
            foreach ($fields as $xmlfield => $dbfield) {
                $exportedvalue = $format->xml_escape($unit->$dbfield);
                $expout .= "      <$xmlfield>{$exportedvalue}</$xmlfield>\n";
            }
            $expout .= $this->export_html($format, 'qtype_varnumunit', 'unitsfeedback', $question->contextid,
                                            $unit, 'feedback', 'unitsfeedback');
            $expout .= $this->export_html($format, 'qtype_varnumunit', 'spacesfeedback', $question->contextid,
                                            $unit, 'spacingfeedback', 'spacesfeedback');
            $expout .= "    </unit>\n";
        }
        return $expout;
    }

    /**
     * Export a field to XML format.
     *
     * @param qformat_xml $format The format to use for exporting.
     * @param string $component The component name.
     * @param string $filearea The file area name.
     * @param int $contextid The context ID.
     * @param stdClass $rec The record containing the data to export.
     * @param string $dbfield The database field name.
     * @param string $xmlfield The XML field name.
     */
    public function export_html($format, $component, $filearea, $contextid, $rec, $dbfield, $xmlfield) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, $component, $filearea, $rec->id);
        $formatfield = $dbfield.'format';

        $output = '';
        $output .= "    <{$xmlfield} {$format->format($rec->$formatfield)}>\n";
        $output .= '      '.$format->writetext($rec->$dbfield);
        $output .= $format->write_files($files);
        $output .= "    </{$xmlfield}>\n";
        return $output;
    }

    /**
     * Get spaceinunit options.
     *
     * @return array
     */
    public static function spaceinunit_options() {
        return [
            self::SPACEINUNIT_REMOVE_ALL_SPACE => get_string('removeallspace', 'qtype_varnumunit'),
            self::SPACEINUNIT_PRESERVE_SPACE_NOT_REQUIRE => get_string('preservespacenotrequire', 'qtype_varnumunit'),
            self::SPACEINUNIT_PRESERVE_SPACE_REQUIRE => get_string('preservespacerequire', 'qtype_varnumunit'),
        ];
    }
}
