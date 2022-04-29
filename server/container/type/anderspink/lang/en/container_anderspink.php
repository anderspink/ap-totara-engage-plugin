<?php

defined('MOODLE_INTERNAL') || die();

/** Plugin settings Strings */
$string['pluginname']    = 'Anders Pink Workspace';
$string['category_name'] = 'Anders Pink';

$string['workspace'] = 'Anders Pink Settings';
$string['settings']  = 'Settings';

/** Vue Components Strings */
$string['nav_menu_title']          = 'Anders Pink';
$string['anderspink_resources']    = 'Workspace Resources';
$string['anderspink_api_settings'] = 'API Settings';
$string['anderspink_briefings']    = 'Configure Workspace Briefings';

$string['table_header_team_name']      = 'Team Name';
$string['table_header_api_key']        = 'API Key';
$string['table_header_timecreated']    = 'Time Created';
$string['table_header_timemodified']   = 'Time Modified';
$string['table_header_actions']        = 'Actions';
$string['table_header_workspace_name'] = 'Workspace';
$string['table_header_folder_name']    = 'Briefing|Saved Folder Name';
$string['table_no_teams']              = 'It looks like you haven\'t added any Anders Pink teams yet. To add a new Anders Pink team, select the \'Add New Team\' button at the top of this page.';
$string['table_no_workspaces']         = 'You have not currently added any briefings to your workspaces. To add a briefing, press \'Add New Briefing\' at the top of this page.';

$string['modal_title_new_api']                = 'New API key';
$string['modal_title_edit_api']               = 'Edit API key';
$string['modal_title_new_workspace_briefing'] = 'Add a New Briefing or Saved Folder to a Workspace';
$string['team_name_label']                    = 'Team Name';
$string['api_key_label']                      = 'API Key';
$string['workspace_name_label']               = 'Workspace';
$string['folder_name_label']                  = 'Briefings or Saved folder';

$string['error:add_api']                = 'We were unable to save this team. Please check that the API key is valid, and has not already been added under a different team name';
$string['error:delete_api']             = 'Unable to delete api entry';
$string['error:edit_api']               = 'Unable to edit api entry';
$string['error:fetch_briefing_folder']  = 'Unable to fetch briefings and folders';
$string['error:error:fetch_articles']   = 'Unable to fetch articles';
$string['error:add_workspace_briefing'] = 'We were unable to save the content to the workspace. Please check if it does not exists already.';
$string['info:no_more_entries_to_load'] = 'No more articles to fetch';
$string['success:bridge_user_info']     = 'You have successfully added a new briefing to a workspace. Please allow a couple of minutes for these articles to display in the workspace they have been added to.';

$string['action_edit_btn']                  = 'Edit';
$string['action_delete_btn']                = 'Delete';
$string['submit']                           = 'Submit';
$string['load_more_btn']                    = 'Load More';
$string['add_to_workspace_btn']             = 'Add Article';
$string['add_to_briefing_to_workspace_btn'] = 'Add New Briefing';

$string['label_team_select']                         = 'Please select a team';
$string['label_briefing_folder_select']              = 'Please select a briefing or folder to add an article from';
$string['label_briefing_folder_to_workspace_select'] = 'Please select a briefing or saved folder to add to this workspace';
$string['label_article_select']                      = 'Please select an article to add to the workspace';
$string['briefing_select_category_label']            = 'Briefings';
$string['boards_select_category_label']              = 'Saved Folders';
$string['boards_select_category_label_no_records']   = 'No Records Found';
$string['no_cards']                                  = 'There are currently no articles in this briefing / saved folder. Please select a different one, or change the search rules for this briefing in your Anders Pink dashboard.';

$string['min_read'] = 'min read';

$string['article_tab'] = 'Articles';

/** Server Strings */
$string['visit_link']     = 'Go To Article';
$string['visit_resource'] = 'Go To Resource...';
$string['link_source']    = 'Source: {$a}';
$string['link_published'] = 'Published: {$a}';

/** Accessibility Strings */
$string['workspace_navigation_aria_label'] = 'Workspace Navigation';
$string['aria_label_add_api']              = 'Add New Team';

/** Permissions */
$string['anderspink:manage'] = 'Anders Pink Workspace Admin';
$string['anderspink:view']   = 'Anders Pink Workspace User';

/** Cron */
$string['crontask_fetch_briefings_and_boards']                 = "Sync Briefings and Boards";
$string['crontask_sync_briefings_and_boards']                  = 'Sync Workspaces content with Anderspink API';
$string['crontask_post_random_discussion']                     = 'Post random Workspace discusion article';
$string['crontask_sync_articles_items_with_shared_recipients'] = 'Sync Shared recipients with available articles';
$string['crontask_delete_old_discussions']                     = 'Clean up automated discussions posts in workspaces';

$string['bridge_created']               = 'Briefing|Saved Folder to Workspace created';
$string['bridge_created_event']         = 'Briefing & Saved Folder Event';
$string['placeholder_bridge_workspace'] = 'Please select a workspace to add a briefing to';
$string['placeholder_bridge_team']      = 'Please select an Anders Pink team to add a briefing from';
$string['placeholder_bridge_type']      = 'Please select a briefing or saved folder to add to this workspace';