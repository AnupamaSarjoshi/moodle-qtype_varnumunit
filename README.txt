Variable Numeric Set Question Type With Units
---------------------------------------------

The question type was created by Jamie Pratt (http://jamiep.org/) for
the Open University (http://www.open.ac.uk/).

This question type is compatible with Moodle 2.3+.

This question type requires the varnumericset question type to be installed. See:

    https://github.com/moodleou/moodle-qtype_varnumericset/

To install using git for a 2.3+ Moodle installation, type this command in the root of your Moodle
install :

    git clone git://github.com/moodleou/moodle-qtype_varnumericunit.git question/type/varnumericunit


Then add question/type/varnumericunit to your git ignore.

Alternatively, download the zip from
    Moodle 2.3+ - https://github.com/moodleou/moodle-qtype_varnumericunit/zipball/master
unzip it into the question/type folder, and then rename the new folder to varnumericunit.

You may want to install Tim's stripped down tinymce editor that only allows the
use of superscript and subscript see
https://github.com/moodleou/moodle-editor_supsub. To install this editor using
git, type this command in the root of your Moodle install:

    git clone git://github.com/moodleou/moodle-editor_supsub.git lib/editor/supsub

Then add lib/editor/supsub to your git ignore.

If the editor is not installed the question type can still be used but if it is
installed when  you make a question that requires scientific notation then this
editor will be shown and a student can either enter an answer with the notation
1x10^5 where the ^5 is expressed with super script.
