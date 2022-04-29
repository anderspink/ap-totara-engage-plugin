# Engage Anders Pink Plugin

----
### Permissions
####Anders Pink Container
  - Anders Pink Workspace Admin
    - `container/anderspink:manage` Allows user to manage container settings including API keys and bridged connections.
#### Anders Pink Engage 1
  - Create Anders Pink resource
    - `engage/anderspink:manage` - Allows user to create new Anders Pink resource coming from the API
#### Block: Workspaces
  - Permission to add the block to the dashboard.
    - `block/workspaces:myaddinstance` - Allows user to add block_workspace instance onto a dashboard.
### Roles
 - Workspace Discussion Author
   - The role is allowing a user to be an actor on discussion posts created from a cron job
### Workspace/Contianer information
A workspace comes with a settings page `container/type/anderspink/api_settings.php` which allows a user to define their workspace configuration and 
API settings.
### Local Plugin Definition 
A local plugin setting page `admin/settings.php?section=local_anderspink` will allow an admin to define main actor for posting random discussion (cron job).
Only if there is more than one person with the role `Workspace Discussion Author`. Otherwise, the plugin will use the single user that has the role against them. 
If role has no user it should default the actor to an admin instead.