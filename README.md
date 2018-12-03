# moodle-format_tabtopics
This course format adds tab-abilties to the standard Moodle Topics format.

Initially the page rendering is identical to Topics - it is then only in edit mode that you will discover some changes.

Assigining Topics to Tabs
-------------------------
To assign a topic to a tab you need to be in edit mode. Then from the topic edit menu chose one of the options named "To Tab ...". The topic then will immediately been moved there.
If this is the 1st topic assigned to a tab it will appear, removing the last topic from a tab will have it removed again. Clicking on it will show the assigned topic(s).

Only tabs with assigned visible topics to them will be shown.<br>
When tabs are shown and a page is loaded the left-most visible tab is always made the active one.

The "Module Content" tab
------------------------
When the 1st tab for a module is shown another tab - called "Module Content" by default - will show as well. This tab is different from other tabs in two ways: 
- It always contains all those topics that are not assigned to any other tab 
- and it stays as the first tab (but may be invisible!)

Renaming and swapping tabs
------------------------
Tabs may be renamed. To do so click on a tab name in edit mode, edit the name and press ENTER to save the changes or ESC to discard.

Tabs may swap places. To do so in edit mode drag one tab onto the tab you want to swap it with.<br>
Remember: You cannot swap places with the "Module Content" tab - but you may rename it.
  
Hidden Tabs
-----------
If a tab only contains topics that are hidden from students the tab itself will be hidden from students as well while being marked accordingly for course admins.

Section 0
---------
Other than other topics Section-0 may be shown always on top of the tabs and other topics. Options for moving section 0 to show always on top or in line with other topics may be chosen from the topic edit menu.

Technical
---------
Almost all of the tab-ability is done using jQuery while the orignal rendering of the page remains identical to the one used by the non-tabbed course format.
(On slow client machines when loading a page you may see it being rendered normally before jQuery kicks in and hides page elements according to the current active tab.
This means that all other functionality of a course page remains intact: topics may be moved, renamed and edited as usual.
<h3>How does it work? </h3>
Tabs will have assigned the IDs of topics to them. When a tab is clicked ALL topics are first hidden and then all topics assigned to a tab will be shown.
For the "Module Content" tab the behavior is complementary: first all topics are shown and the all those assignet to any of the other tabs will be hidden again.

By default the format supports up to 5 tabs plus the "Module Content" tab (see below).
By setting $CFG->max_tabs in the config.php file this value may be changed up to a maximum of 10 tabs.

