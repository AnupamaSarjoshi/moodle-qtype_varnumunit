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

use qtype_varnumunit\qtype_varnumunit_unit;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/varnumunit/questiontype.php');

/**
 * Test helper class for the varnumunit question type.
 *
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_varnumunit_test_helper extends question_test_helper {
    public function get_test_questions() {
        return ['3_sig_figs_with_m_unit', '3_sig_figs_with_units_meters_per_second', 'simple_1_m',
            'require_space_between_number_n_unit', 'with_variables'];
    }

    /**
     * @return qtype_varnumunit_question
     */
    public function make_varnumunit_question_3_sig_figs_with_m_unit() {
        question_bank::load_question_definition_classes('varnumunit');
        $vu = new qtype_varnumunit_question();
        test_question_maker::initialise_a_question($vu);
        $vu->name = 'test question 1';
        $vu->questiontext = '<p>The correct answer is 12300.</p>';
        $vu->generalfeedback = '<p>General feedback 12300.</p>';
        $vu->penalty = 0.3333333;
        $vu->requirescinotation = false;
        $vu->usesupeditor = false;
        $vu->unitfraction = '0.2500000';
        $vu->qtype = question_bank::get_qtype('varnumunit');

        $vu->answers = [1 => new qtype_varnumericset_answer(
                                                 '1',     // Id.
                                                 '12345', // Answer.
                                                 '1',     // Fraction.
                                                 '<p>Your answer is correct.</p>', // Feedback.
                                                 FORMAT_HTML,  // Feedbackformat.
                                                 '3',     // Sigfigs.
                                                 '',      // Error.
                                                 '0.1',   // Syserrorpenalty.
                                                 '0',     // Checknumerical.
                                                 '0',     // Checkscinotation.
                                                 '4',     // Checkpowerof10.
                                                 '0',     // Checkrounding.
                                                 '0'),    // Checkscinotationformat.
                            2 => new qtype_varnumericset_answer(
                                                 '2',     // Id.
                                                 '*',     // Answer.
                                                 '0',     // Fraction.
                                                 '<p>Your answer is incorrect.</p>', // Feedback.
                                                 FORMAT_HTML,  // Feedbackformat.
                                                 '0',     // Sigfigs.
                                                 '',      // Error.
                                                 '0.1000000', // Syserrorpenalty.
                                                 '0',     // Checknumerical.
                                                 '0',     // Checkscinotation.
                                                 '0',     // Checkpowerof10.
                                                 '0',     // Checkrounding.
                                                 '0')];   // Checkscinotationformat.

        $vu->units = [
                                1 => new qtype_varnumunit_unit(
                                    '1',
                                    'match(m)',
                                    '1',
                                    '',
                                    '1',
                                    1,
                                    '1.0000000',
                                    '<p>Good!</p>',
                                    '1'),
                                2 => new qtype_varnumunit_unit(
                                    '2',
                                    '*',
                                    '1',
                                    '',
                                    '1',
                                    1,
                                    '0.0000000',
                                    '',
                                    '1')];
        $calculatorname = $vu->qtype->calculator_name();
        $vu->calculator = new $calculatorname();
        $vu->calculator->evaluate_variant(0);
        return $vu;
    }

    /**
     * @return qtype_varnumunit_question
     */
    public function make_varnumunit_question_3_sig_figs_with_units_meters_per_second() {
        question_bank::load_question_definition_classes('varnumunit');
        $vu = new qtype_varnumunit_question();
        test_question_maker::initialise_a_question($vu);
        $vu->name = 'test question 2';
        $vu->questiontext = 'The correct answer is 4000 m/s or 4e3 ms<sup>-1</sup> etc..';
        $vu->generalfeedback = 'General feedback, blah blah.';
        $vu->penalty = '0.2000000';
        $vu->requirescinotation = true;
        $vu->usesupeditor = true;
        $vu->unitfraction = '0.1000000';
        $vu->qtype = question_bank::get_qtype('varnumunit');
        $vu->answers = [1 => new qtype_varnumericset_answer(
                                '1',     // Id.
                                '4000',  // Answer.
                                '1',     // Fraction.
                                '<p>Your answer is correct.</p>', // Feedback.
                                FORMAT_HTML,  // Feedbackformat.
                                '4',     // Sigfigs.
                                '',      // Error.
                                '0.1000000', // Syserrorpenalty.
                                '0',     // Checknumerical.
                                '0',     // Checkscinotation.
                                '4',     // Checkpowerof10.
                                '0',     // Checkrounding.
                                '0'),    // Checkscinotationformat.
                             2 => new qtype_varnumericset_answer(
                                 '2',    // Id.
                                 '*',    // Answer.
                                 '0',    // Fraction.
                                 '<p>Your answer is incorrect.</p>', // Feedback.
                                 FORMAT_HTML, // Feedbackformat.
                                 '0',    // Sigfigs.
                                 '',     // Error.
                                 '0.1000000', // Syserrorpenalty.
                                 '0',    // Checknumerical.
                                 '0',    // Checkscinotation.
                                 '0',    // Checkpowerof10.
                                 '0',    // Checkrounding.
                                 '0')];  // Checkscinotationformat.

        $vu->units = [
            1 => new qtype_varnumunit_unit(
                '1',
                'match(ms<sup>-1</sup>)',
                '1',
                '',
                '1',
                1,
                '1.0000000',
                '<p>Good!</p>',
                '1'),
            2 => new qtype_varnumunit_unit(
                '1',
                'match(m/s)',
                '1',
                '',
                '1',
                1,
                '1.0000000',
                '<p>Good!</p>',
                '1'),
            3 => new qtype_varnumunit_unit(
                '2',
                '*',
                '1',
                '',
                '1',
                1,
                '0.0000000',
                '',
                '1')];
        $calculatorname = $vu->qtype->calculator_name();
        $vu->calculator = new $calculatorname();
        $vu->calculator->evaluate_variant(0);
        return $vu;
    }

    /**
     * @return qtype_varnumunit_question
     */
    public function make_varnumunit_question_simple_1_m() {
        question_bank::load_question_definition_classes('varnumunit');
        $vu = new qtype_varnumunit_question();
        test_question_maker::initialise_a_question($vu);
        $vu->name = 'test question 2';
        $vu->questiontext = 'The correct answer is 1 m';
        $vu->generalfeedback = 'General feedback, blah blah.';
        $vu->penalty = '0.2000000';
        $vu->requirescinotation = false;
        $vu->usesupeditor = false;
        $vu->unitfraction = '0.1000000';
        $vu->qtype = question_bank::get_qtype('varnumunit');
        $vu->answers = [1 => new qtype_varnumericset_answer(
            '1',    // Id.
            '1',    // Answer.
            '1',    // Fraction.
            '<p>Your answer is correct.</p>', // Feedback.
            FORMAT_HTML, // Feedbackformat.
            '0',    // Sigfigs.
            '',     // Error.
            '0.1000000', // Syserrorpenalty.
            '0',    // Checknumerical.
            '0',    // Checkscinotation.
            '0',    // Checkpowerof10.
            '0',    // Checkrounding.
            '0')];  // Checkscinotationformat.
        $vu->units = [
            1 => new qtype_varnumunit_unit(
                '1',
                'match(m)',
                '1',
                '',
                '1',
                '1',
                '1.0000000',
                '<p>Good!</p>',
                '1'),
            2 => new qtype_varnumunit_unit(
                '2',
                '*',
                '0',
                '',
                '0',
                '0',
                '0.0000000',
                '<p>That is not the right unit.</p>',
                '1'),
        ];
        $calculatorname = $vu->qtype->calculator_name();
        $vu->calculator = new $calculatorname();
        $vu->calculator->evaluate_variant(0);
        return $vu;
    }

    /**
     * @return qtype_varnumunit_question
     */
    public function make_varnumunit_question_require_space_between_number_n_unit() {
        question_bank::load_question_definition_classes('varnumunit');
        $vu = new qtype_varnumunit_question();
        test_question_maker::initialise_a_question($vu);
        $vu->name = 'test question 2';
        $vu->questiontext = 'The correct answer is 1 m';
        $vu->generalfeedback = 'General feedback, blah blah.';
        $vu->penalty = '0.2000000';
        $vu->requirescinotation = false;
        $vu->usesupeditor = false;
        $vu->unitfraction = '0.1000000';
        $vu->qtype = question_bank::get_qtype('varnumunit');
        $vu->answers = []; // Data create by dataprovider.

        $vu->units = []; // Data create by dataprovider.
        $calculatorname = $vu->qtype->calculator_name();
        $vu->calculator = new $calculatorname();
        $vu->calculator->evaluate_variant(0);
        return $vu;
    }
    public function make_varnumunit_question_with_variables() {
        $vu = $this->make_varnumericset_question_no_accepted_error();

        $vu->questiontext = '<p>What is [[a]] m + [[b]] m?</p>';
        $vu->requirescinotation = 1;
        $vu->answers[1]->answer = 'a + b';
        $vu->answers[1]->sigfigs = 1;
        $vu->answers[1]->checknumerical = 1;
        $vu->answers[1]->checkscinotation = 1;
        $vu->units = [];
        $vu->calculator->add_variable(0, 'a');
        $vu->calculator->add_variable(1, 'b');
        $vu->calculator->add_defined_variant(0, 0, 2);
        $vu->calculator->add_defined_variant(1, 0, 3);
        $vu->calculator->evaluate_variant(0);

        return $vu;
    }
    public function get_varnumunit_question_form_data_with_variables() {
        $form = new stdClass();
        $form->name = 'Pi to two d.p.';
        $form->questiontext = ['text' => '<p>What is [[a]] m + [[b]] m?</p>', 'format' => FORMAT_HTML];
        $form->defaultmark = 1;
        $form->requirescinotation = 0;
        $form->randomseed = '';
        $form->vartype = ['0' => 1, '1' => 1, '2' => 1]; // Set to 'Predefined variable'.
        $form->novars = 3;
        $form->noofvariants = 3;
        $form->varname[0] = 'a';
        $form->variant0[0] = 2;
        $form->variant1[0] = 3;
        $form->variant2[0] = 5;
        $form->varname[1] = 'b';
        $form->variant0[1] = 8;
        $form->variant1[1] = 5;
        $form->variant2[1] = 3;
        $form->varname[2] = 'c = a + b';
        $form->variant_last = ['0' => '', '1' => ''];
        $form->requirescinotation = 0;
        $form->answer = ['0' => 'c', '1' => '*'];
        $form->sigfigs = ['0' => 0, '1' => 0];
        $form->error = ['0' => '', '1' => ''];
        $form->checknumerical = ['0' => 0, '1' => 0, '2' => 0];
        $form->checkscinotation = ['0' => 0, '1' => 0, '2' => 0];
        $form->checkpowerof10 = ['0' => 0, '1' => 0, '2' => 0];
        $form->checkrounding = ['0' => 0, '1' => 0, '2' => 0];
        $form->checkscinotationformat = ['0' => 0, '1' => 0, '2' => 0];
        $form->syserrorpenalty = ['0' => 0.0, '1' => 0.0, '2' => 0.0];
        $form->fraction = ['0' => '1.0', '1' => '0.0', '2' => '0.0'];
        $form->feedback = [
                '0' => ['format' => FORMAT_HTML, 'text' => 'Well done!'],
                '1' => ['format' => FORMAT_HTML, 'text' => 'Sorry, no.'],
        ];
        $form->otherunitfeedback = ['text' => '', 'format' => FORMAT_HTML];
        $form->unit[0] = 'match(m)';
        $form->spaceinunit[0] = 1;
        $form->replacedash[0] = true;
        $form->unitsfraction[0] = '1.0';
        $form->spacingfeedback[0] = ['text' => '', 'format' => FORMAT_MOODLE];
        $form->unitsfeedback[0] = 'That is the right unit.';
        $form->unit[1] = '';
        $form->spaceinunit[1] = 1;
        $form->replacedash[1] = true;
        $form->unitsfraction[1] = '0.0';
        $form->unitsfeedback[1] = ['text' => 'That is the right unit 2.', 'format' => FORMAT_HTML];
        $form->spacingfeedback[1] = ['text' => '', 'format' => FORMAT_HTML];
        $form->penalty = '0.3333333';
        $form->hint = [
                ['text' => 'Please try again.', 'format' => FORMAT_HTML],
                ['text' => 'You may use a calculator if necessary.', 'format' => FORMAT_HTML],
        ];

        return $form;
    }
}
