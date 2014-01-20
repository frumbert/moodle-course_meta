<?php
 
$string['filtername'] = 'Course catalogue';
$string['configure'] = 'Course-in-catalogue template';
$string['configure_desc'] = 'Template html to use to draw a course in the catalogue.

	Field names match the shortname definition of a course metadata field.
	Other values are "courseid" and "categoryid", which represent the
	course row id, and the category of the metdata field.

	To write a field, put the shortname in square brackets. For instance [columnname].
	
	To conditionally render a section: [#if columnname] inner code [/if columnname].
	
	To render a table from semicolon-seperated values: [#table columnname].
	
	To render an image: [#image columnname].

	To render a file hyperlink: [#link columnname].

Other normal HTML markup is valid. Styling should be applied in your themes CSS.';


$string['rebuildview'] = "Rebuild database view";
?>