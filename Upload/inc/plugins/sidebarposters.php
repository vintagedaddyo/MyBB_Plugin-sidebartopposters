<?php
/*
 * MyBB: Top Posters Sidebar
 *
 * File: sidebarposters.php
 * 
 * Authors: borbole & Vintagedaddyo
 *
 * MyBB Version: 1.8
 *
 * Plugin Version: 1.1
 * 
 */

//Trying to access directly the file, are we :D

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

//Hooking into index_start with our function


$plugins->add_hook("index_start", "top_posters_sidebar");

//Show some info about our mod

function sidebarposters_info()
{
    global $lang;

    $lang->load("sidebarposters");
    
    $lang->sidebarposters_Desc = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right;">' .
        '<input type="hidden" name="cmd" value="_s-xclick">' . 
        '<input type="hidden" name="hosted_button_id" value="AZE6ZNZPBPVUL">' .
        '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">' .
        '<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">' .
        '</form>' . $lang->sidebarposters_Desc;

    return Array(
        'name' => $lang->sidebarposters_Name,
        'description' => $lang->sidebarposters_Desc,
        'website' => $lang->sidebarposters_Web,
        'author' => $lang->sidebarposters_Auth,
        'authorsite' => $lang->sidebarposters_AuthSite,
        'version' => $lang->sidebarposters_Ver,
        'codename' => $lang->sidebarposters_CodeName,
        'compatibility' => $lang->sidebarposters_Compat
    );
}

//Activate it

function sidebarposters_activate()
{
	global $db, $lang;

    $lang->load("sidebarposters");

	//Insert the mod settings in the portal settinggroup

	$query = $db->simple_select("settinggroups", "gid", "name='forumhome'");
	$gid = $db->fetch_field($query, "gid");

	$setting = array(
		'name' => 'posters_nr',
		'title' => $lang->sidebarposters_option_1_Title,
		'description' => $lang->sidebarposters_option_1_Description,
		'optionscode' => 'text',
		'value' => '5',
		'disporder' => '99',
		'gid' => intval($gid)
	);
	
	$db->insert_query('settings',$setting);
	
	
	rebuild_settings();
	
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	
	find_replace_templatesets("index", '#'.preg_quote('{$forums}').'#', '<table width="100%"  border="0">
  <tr>
    <td width="75%" valign="top">{$forums}</td>
    <td width="25%" valign="top">{$top_posters}</td>
  </tr>
</table>');
}

//Don't want to use it anymore? Let 's deactivate it then and drop the settings and the custom template as well

function sidebarposters_deactivate()
{
	global $db;
	
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='posters_nr'");
	
	rebuild_settings();
	
	
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	
	find_replace_templatesets("index", '#'.preg_quote('<table width="100%"  border="0">
  <tr>
    <td width="75%" valign="top">{$forums}</td>
    <td width="25%" valign="top">{$top_posters}</td>
  </tr>
</table>').'#', '{$forums}',0);


}

function top_posters_sidebar()
{
	global $db, $mybb, $forums, $theme, $top_posters, $lang; 

	    $lang->load("sidebarposters");
	
	    $top_posters .= '
		<table width="50%" cellpadding="5" cellspacing="1" border="0" class="tborder">
        <tr>
             <td class="thead"><strong>'.$lang->sidebarposters_Title.'</strong></td>
        </tr>';
		
	     //Limit how many users to show

		$limit = intval($mybb->settings['posters_nr']);
		
	    //Query to get top posters

            $query = $db->query("
            SELECT username, postnum, uid, usergroup, displaygroup
            FROM ".TABLE_PREFIX."users
            ORDER BY postnum DESC
            LIMIT 0,{$limit}
        ;");
        
        while($row = $db->fetch_array($query))
        {
		   $top_posters .= '
		   <tr>'; 
		    $users = htmlspecialchars_uni($row['username']);
			
			//Trim the usernames if they are over 9 characters

		   if (strlen($users) > 9)
		   {
	          $users = substr($users, 0, 9) . "..."; 
		   }
		    //Get the usernames and make them pretty too with the group styling

			$user['username'] = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
			$user['profilelink'] = build_profile_link($user['username'], $row['uid']);
			
			$users =  $user['profilelink'];
			
		   //Get the post count

           $posts = "<a href=\"search.php?action=finduser&amp;uid={$row['uid']}\">{$row['postnum']}</a>";
		   
		  $top_posters .= '
		  <td class="trow1">' . $users . '
              <span style="float:right;">' . $posts . '</span>
          </td>
		  
         </tr>';
            
        }
		
		$top_posters .= "</table><br />";
		
}

?>