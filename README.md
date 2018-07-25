2018 / moodle 3+ note
---------------------

this tool was written for moodle 2+ branchs; **it is broken in moodle 3+ branches**; won't fix / maintain any longer

**If you are looking for a good alternatve to adding custom fields to objects in Moodle 3+, try the https://github.com/PoetOS/moodle-local_metadata project.**

----

moodle-course_meta
==================

A quick way of adding custom fields to courses. Based on work by Mark Nelson, Pukunui. I couldn't find a repository for the original. Interesting to note that Totara LMS, based also on Moodle, implements its own version of course metadata fields (https://github.com/moodlehq/totara). Their code is neater than mine, so look that up if you want to play. 

The [Master](https://github.com/frumbert/moodle-course_meta/tree/master) branch was designed with Moodle 2.3 in mind. Switch branches to grab the code for [Moodle 2.7](https://github.com/frumbert/moodle-course_meta/tree/Moodle2.7) or [Moodle 2.8.7](https://github.com/frumbert/moodle-course_meta/tree/Moodle2.8.7).

You would use this when you want custom fields to appear on course fields, not just users. This method uses a little-known feature of Moodle where you can override an edit form by using "customscripts". It's documented in config-dist.php in Moodle's install folder, but the gist is that if `$CFG->customscripts/course/view.php` exists then it will be used instead of `$CFG->wwwroot/course/view.php`

So this plugin overrides the course edit file. This does mean that its tied fairly to the Moodle version, since the edit.php and edit_form.php files are actually copies of the same out of `/course/`, with a few customisations added. To support newer Moodle versions, you could easily diff/merge these files from the version you want to support.

Example usage
-------------

Demo site will be along shortly, but I've included a FILTER that creates a course catalogue based on the metadata. (It's a filter because it's the most theme-independant way of customising the front page).

The purpose here is that Moodle builds course lists based on categories. Users can generally see everything, but that might not be the best way of representing the courses you want them to see. Or you want to show other information about a course, such as its cost or time in hours to complete, or icon... The catalogue provides a way to customise the presentation of courses, plus independantly choose which courses appear in the catalogue at all.

Installation of the filter first requires the course_meta local plugin to be installed, as it relies on the tables added by that plugin.

How to install
--------------

There are 3 folders and you need to install them in a particular way.

1. Copy `course_meta` and `customscripts` into `~/local/` (where `~` is your moodle root).
2. Add the following line to your config.php:

	`$CFG->customscripts = __DIR__.'/local/customscripts';`

3. As admin, go to site notifications and install the two plugins.
4. If required, add `coursecatalogue` to your `~/filters/` folder.
5. Enable the filter (or set it to Off but available) and enable it on the front page.
6. Create a filter template by looking at the Course Catalogue filter settings. Looking at this page updates a VIEW in the database which represents all the metadata columns available to courses. You also have to set a template in order for the next step to work.
7. In the default topic of the front page (or any course where you enabled the filter), add `[course-catalogue]` to the html of the page (using the standard editor is fine). This works like a shortcode in Wordpress - it replaces the key with the catalogue, and registers the scripts and styles on the page needed to make it work.

Installing the course_meta plugin will create 3 new tables in the database -

`mdl_course_meta_info_category` - categories of custom fields shown in edit screen
`mdl_course_meta_info_data` - the actual data stored by a custom field (in context of field / course)
`mdl_course_meta_info_field` - the definition of a custom field (like its name, field type, etc)

Installing the coursecatalogue filter will create a VIEW in the database

`mdl_vw_course_metadata` - a crosstab of the course id against all metadata fields defined for courses.

Default data
---------------

To play with a course catalogue, you need to create particular fields to start off with - a field named 'listed' and another named 'tab'. I haven't installed these as a script or default with the plugin because you might require something different. These are used to render lists in the catalogue. Here's a sql script I use for that (assumes you have a course with id 1).

	LOCK TABLES `mdl_course_meta_info_category` WRITE;
	ALTER TABLE `mdl_course_meta_info_category` DISABLE KEYS
	
	INSERT INTO `mdl_course_meta_info_category` (`id`, `name`, `sortorder`)
	VALUES
		(1,'Developer notes',2),
		(2,'Catalogue',1);
	
	ALTER TABLE `mdl_course_meta_info_category` ENABLE KEYS
	UNLOCK TABLES;
	
	LOCK TABLES `mdl_course_meta_info_field` WRITE;
	ALTER TABLE `mdl_course_meta_info_field` DISABLE KEYS
	
	INSERT INTO `mdl_course_meta_info_field` (`id`, `shortname`, `name`, `datatype`, `description`, `descriptionformat`, `categoryid`, `sortorder`, `defaultdata`, `defaultdataformat`, `param1`, `param2`, `param3`, `param4`, `param5`)
	VALUES
		(1,'tab','Tab','menukeys','Which tab the course appears under in the catalogue',1,2,2,'webinars',0,'online\nblended','Online courses\nBlended learning',NULL,NULL,NULL),
		(2,'listed','Listed in catalogue?','checkbox','If the course is shown in the catalogue / search',1,2,1,'1',0,NULL,NULL,NULL,NULL,NULL),
		(3,'keywords','Search keywords (comma separated)','text','Used in catalogue search alongside name, description',1,2,10,'',0,'60','2048','0','',''),
		(4,'description','Course description','textarea','Description for the catalogue; might be different than the course homepage.',1,2,11,NULL,0,NULL,NULL,NULL,NULL,NULL),
		(5,'notes','Developer notes','textarea','Things that might be important to write down about this particular course',1,1,1,'',1,NULL,NULL,NULL,NULL,NULL);
	
	ALTER TABLE `mdl_course_meta_info_field` ENABLE KEYS
	UNLOCK TABLES;
	
	LOCK TABLES `mdl_course_meta_info_data` WRITE;
	INSERT INTO `mdl_course_meta_info_data` (`courseid`, `fieldid`, `data`, `dataformat`)
	VALUES
		(1,1,'online',0),
		(1,2,1,0),
		(1,4,'Here is my awesome course for you to try.',0);
	UNLOCK TABLES;

You then need to create a template for the course to render each instance of a course in the catalogue. Here's something simple that could get you started:

	<div class='course-catalogue-instance'>
		<h3><a href='/course/view.php?id=[courseid]'>[fullname]</a></h3>
		<p>[description]</p>
		[#if keywords]<p class='keywords'>[keywords]</p>[/if keywords]
	</div>

With luck, you should get a tab containing the course listed above!

Guff
----

Hope this works for you, gets you out of trouble, is a starting point for your own custom field work. It's something sorely missing from the new Moodle releases. I expect to try getting a branch going for Moodle 2.5, 2.6 & 2.7 when I have some more spare time.
