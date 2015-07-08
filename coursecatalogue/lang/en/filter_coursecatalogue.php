<?php

$string['filtername'] = 'Course catalogue';
$string['configure'] = 'Course-in-catalogue template';
$string['configure_desc'] = 'Template html to use to draw a course in the catalogue.

	Field names match the shortname definition of a course metadata field.
	Some fields are automatically included from the Course table:

	   [courseid]: The database row id of the course
	   [fullname]: The "Course full name" of the course
	   [shortname]: The "Course short name" of the course
	   [idnumber]: The "Course ID number" optional field from the course

	To write a field, put the shortname in square brackets. For instance [columnname].

	To conditionally show a section: [#if columnname] inner code [/if].
	To conditionally hide a section (opposite of IF): [#not columnname] inner code [/not].
	Note, can test more than one field using [#if colName1,colName2] inner code [/if colName1,colName2].

	To render a table (grid of td) from semicolon-seperated values: [#table columnname].

	To render an image: [#image columnname].

	To render a file hyperlink: [#link columnname].

Other normal HTML markup is valid. Styling should be applied in your themes CSS, but default styling and code is applied by default, unless overridden.';

$string['defaultstyles'] = 'Include default styles & script';
$string['defaultstyles_desc'] = 'If checked, the page will include the default CSS and javascript to make the catalogue work like simple Tabs';

$string['rebuildview'] = "Rebuild database view";
?>