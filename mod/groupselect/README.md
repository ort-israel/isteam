Group self-selection module for Moodle

* Copyright (C) 2017 HTWChur
* Copyright (C) 2014 Tampere University of Technology, Pirkka Pyykkönen (pirkka.pyykkonen ÄT tut.fi)
* Copyright (C) 2008-2011 Petr Skoda (http://skodak.org/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details:
http://www.gnu.org/copyleft/gpl.html

Lets students create and select groups. Features:

* Students can create groups, give them a description and set them password protected, if wanted
* Students can select and join groups
* Non-editing teachers may be assigned to groups
* Teacher can export course group list as a csv-file
* Full compatibility with basic Moodle groups: groups may be created by other means if needed, supports group assignment submissions etc.


Thanks to Pirkka Pyykkönen, Petr Skoda, Helen Foster, Daniel Neis Araujo and other
contributors, on whose earlier work this plugin is based on.

Project page:

* https://github.com/marinaglancy/moodle-mod_groupselect (moodle) by Marina Glancy
* https://github.com/BLC-HTWChur/moodle-mod_groupselect (version >=3.06) by HTWChur
* https://github.com/birrel/moodle-mod_groupselect (version <= 3.05 beta) by Pirkka Pyykkönen
* https://github.com/skodak/moodle-mod_groupselect (original <= 2.1 versions) by Petr Skoda

NOTABLE UPDATES:
* 2017.02.01: Default values for activity settings available
* 2016.09.07: Export only current grouping, if specified
* 2016.08.26: Enabled show description feature and tableview improvements for longer groupnames
* 2016.06.11: New option to disable notifications if the open until date is reached
* 2016.06.06: New option for students to define the group name; New option to avoid that students can define passwords;
Fixed validation messages for creating groups, fixed install.xml
* 2016.06.02: Fixed wrong instance_id in table groupselect_groups_teachers; Backup and restore works now;
Added new logging events for adding non-editign teachers and creating a downloadlink; Some small fixes.
* 2016.05.09: Removed general setting 'requiremodintro', not supported anymore
* 2015.03.25: Fixed: password was asked when joining group without
password (if upgraded from older versions), sql queries should now work
with oracle
* 2014.12.17: Migrated to new logging system
* 2014.12.15: Small fixes
* 2014.12.01: Fixed upgrade.php, project renamed as groupselect
* 2014.11.07: Non-editing teacher assignment, group description editing, improved csv-export, small optional features added
* 2014.09.11: Fixed mysql insertion related bug, added some notifications and small fixes
