<?php
/*=======================================================================
 Nuke-Evolution Basic: Enhanced PHP-Nuke Web Portal System
 =======================================================================*/

/***************************************************************************
 *                           usercp_viewprofile.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   Id: usercp_viewprofile.php,v 1.5.2.5 2005/07/19 20:01:16 acydburn Exp
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/*****[CHANGES]**********************************************************
-=[Base]=-
      Nuke Patched                             v3.0.0       06/07/2005
-=[Mod]=-
      Attachment Mod                           v2.4.1       07/20/2005
      Advanced Username Color                  v1.0.5       06/11/2005
      Default avatar                           v1.1.0       06/30/2005
      User Administration Link on Profile      v1.0.0       07/29/2005
      XData                                    v1.0.3       02/08/2007
      YA Merge                                 v1.0.0       08/10/2005
      View Sig                                 v1.0.0       08/11/2005
      Show Groups                              v1.0.1       09/02/2005
      Remote Avatar Resize                     v2.0.0       11/19/2005
      Online/Offline/Hidden                    v2.2.7       01/24/2006
      XData Date Conversion                    v0.1.1       05/04/2006
	  Member Country Flags                     v2.0.7
	  Multiple Ranks And Staff View            v2.0.3
	  Gender                                   v1.2.6
	  Birthdays                                v3.0.0
	  MSN Profile Image                        v1.0.0
	  Facebook Profile Image                   v1.0.0
	  Admin User Notes                         v1.0.0       05/28/2009
	  Arcade                                   v3.0.2       05/29/2009
	  Admin delete users & posts               v1.0.5       05/29/2009
 ************************************************************************/

if (!defined('IN_PHPBB'))
{
    die('Hacking attempt');
}

if ( empty($HTTP_GET_VARS[POST_USERS_URL]) || $HTTP_GET_VARS[POST_USERS_URL] == ANONYMOUS )
{
    message_die(GENERAL_MESSAGE, $lang['No_user_id_specified']);
}
$profiledata = get_userdata($HTTP_GET_VARS[POST_USERS_URL]);
/*****[BEGIN]******************************************
 [ Mod:     Show Groups                        v1.0.1 ]
 ******************************************************/
if (isset($profiledata['user_id']) && !empty($profiledata['user_id'])) {
    $sql = "SELECT group_name FROM ".GROUPS_TABLE." LEFT JOIN ".USER_GROUP_TABLE." on ".USER_GROUP_TABLE.".group_id=".GROUPS_TABLE.".group_id WHERE ".USER_GROUP_TABLE.".user_id=".$profiledata['user_id'];
    if ( !($result = $db->sql_query($sql)) ){
            $groups = "SQL Failed to obtain last visit";
    }
    else {
        if($db->sql_numrows($result) == 0){
            $groups = "None";
        } else {
            while($row = $db->sql_fetchrow($result)){
                $groups .= $row['group_name'] . "<br />";
            }
        }
        $db->sql_freeresult($result);
    }
}
/*****[END]********************************************
 [ Mod:     Show Groups                        v1.0.1 ]
 ******************************************************/

if (!$profiledata)
{
    message_die(GENERAL_MESSAGE, $lang['No_user_id_specified']);
}

/*****[BEGIN]******************************************
 [ Mod:    Multiple Ranks And Staff View       v2.0.3 ]
 ******************************************************/
require_once(NUKE_INCLUDE_DIR.'functions_mg_ranks.'.$phpEx);
$ranks_sql = query_ranks();
/*****[END]********************************************
 [ Mod:    Multiple Ranks And Staff View       v2.0.3 ]
 ******************************************************/

//
// Output page header and profile_view template
//
$template->set_filenames(array(
    'body' => 'profile_view_body.tpl')
);
if (is_active("Forums")) {
    make_jumpbox('viewforum.'.$phpEx);
}
//
// Calculate the number of days this user has been a member ($memberdays)
// Then calculate their posts per day
//
$regdate = $profiledata['user_regdate'];
$nukedate = strtotime($regdate);
$memberdays = max(1, round( ( time() - $nukedate ) / 86400 ));
$posts_per_day = $profiledata['user_posts'] / $memberdays;

// Get the users percentage of total posts
if ( $profiledata['user_posts'] != 0  )
{
    $total_posts = get_db_stat('postcount');
    $percentage = ( $total_posts ) ? min(100, ($profiledata['user_posts'] / $total_posts) * 100) : 0;
}
else
{
    $percentage = 0;
}

$avatar_img = '';
if ( $profiledata['user_avatar_type'] && $profiledata['user_allowavatar'] )
{
    switch( $profiledata['user_avatar_type'] )
    {
        case USER_AVATAR_UPLOAD:
            $avatar_img = ( $board_config['allow_avatar_upload'] ) ? '<img src="' . $board_config['avatar_path'] . '/' . $profiledata['user_avatar'] . '" alt="" border="0" />' : '';
            break;
/*****[BEGIN]******************************************
 [ Mod:     Remote Avatar Resize               v2.0.0 ]
 ******************************************************/
        case USER_AVATAR_REMOTE:
            $avatar_img = resize_avatar($profiledata['user_avatar']);
            break;
/*****[END]********************************************
 [ Mod:     Remote Avatar Resize               v2.0.0 ]
 ******************************************************/
        case USER_AVATAR_GALLERY:
            $avatar_img = ( $board_config['allow_avatar_local'] ) ? '<img src="' . $board_config['avatar_gallery_path'] . '/' . $profiledata['user_avatar'] . '" alt="" border="0" />' : '';
            break;
    }
}
/*****[BEGIN]******************************************
 [ Mod:     Default avatar                     v1.1.0 ]
 ******************************************************/
    if ((!$avatar_img) && (($board_config['default_avatar_set'] == 1) || ($board_config['default_avatar_set'] == 2)) && ($board_config['default_avatar_users_url'])){
        $avatar_img = '<img src="' . $board_config['default_avatar_users_url'] . '" alt="" border="0" />';
    }
/*****[END]********************************************
 [ Mod:     Default avatar                     v1.1.0 ]
 ******************************************************/

/*****[BEGIN]******************************************
 [ Mod:    Multiple Ranks And Staff View       v2.0.3 ]
 ******************************************************/
	$user_ranks = generate_ranks($profiledata, $ranks_sql);

	$user_rank_01 = ($user_ranks['rank_01'] == '') ? '' : ($user_ranks['rank_01'] . '<br />');
	$user_rank_01_img = ($user_ranks['rank_01_img'] == '') ? '' : ($user_ranks['rank_01_img'] . '<br />');
	$user_rank_02 = ($user_ranks['rank_02'] == '') ? '' : ($user_ranks['rank_02'] . '<br />');
	$user_rank_02_img = ($user_ranks['rank_02_img'] == '') ? '' : ($user_ranks['rank_02_img'] . '<br />');
	$user_rank_03 = ($user_ranks['rank_03'] == '') ? '' : ($user_ranks['rank_03'] . '<br />');
	$user_rank_03_img = ($user_ranks['rank_03_img'] == '') ? '' : ($user_ranks['rank_03_img'] . '<br />');
	$user_rank_04 = ($user_ranks['rank_04'] == '') ? '' : ($user_ranks['rank_04'] . '<br />');
	$user_rank_04_img = ($user_ranks['rank_04_img'] == '') ? '' : ($user_ranks['rank_04_img'] . '<br />');
	$user_rank_05 = ($user_ranks['rank_05'] == '') ? '' : ($user_ranks['rank_05'] . '<br />');
	$user_rank_05_img = ($user_ranks['rank_05_img'] == '') ? '' : ($user_ranks['rank_05_img'] . '<br />');
/*****[END]********************************************
 [ Mod:    Multiple Ranks And Staff View       v2.0.3 ]
 ******************************************************/

$temp_url = append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=" . $profiledata['user_id']);
if (is_active("Private_Messages")) 
{
	$pm_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_pm'] . '" alt="' . $lang['Send_private_message'] . '" title="' . $lang['Send_private_message'] . '" border="0" /></a>';
	$pm = '<a href="' . $temp_url . '">' . $lang['Send_private_message'] . '</a>';
}

/*****[BEGIN]******************************************
 [ Mod:     Member Country Flags               v2.0.7 ]
 ******************************************************/
$location = ( !empty($profiledata['user_from_flag']) ) ? "<img src=\"images/flags/" . $profiledata['user_from_flag'] . "\" alt=\"" . $profiledata['user_from_flag'] . "\">&nbsp;" : "&nbsp;";
$location .= ( $profiledata['user_from'] ) ? $profiledata['user_from'] : '';
/*****[END]********************************************
 [ Mod:     Member Country Flags               v2.0.7 ]
 ******************************************************/

if ( !empty($profiledata['user_viewemail']) || ($profiledata['username'] == $userdata['username']) || $userdata['user_level'] == ADMIN )
{
    $email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL .'=' . $profiledata['user_id']) : 'mailto:' . $profiledata['user_email'];

    $email_img = '<a href="' . $email_uri . '"><img src="' . $images['icon_email'] . '" alt="' . $lang['Send_email'] . '" title="' . $lang['Send_email'] . '" border="0" /></a>';
    $email = '<a href="' . $email_uri . '">' . $lang['Send_email'] . '</a>';
}
else
{
    $email_img = '&nbsp;';
    $email = '&nbsp;';
}
if (isset($profiledata['user-website'])) 
{
    if (( $profiledata['user-website'] == "http:///") || ( $profiledata['user_website'] == "http://"))
	{
        $profiledata['user_website'] =  "";
    }
    if (($profiledata['user_website'] != "" ) && (substr($profiledata['user_website'],0, 7) != "http://")) 
	{
        $profiledata['user_website'] = "http://".$profiledata['user_website'];
    }
}

$www_img = ( $profiledata['user_website'] ) ? '<a href="' . $profiledata['user_website'] . '" target="_userwww"><img src="' . $images['icon_www'] . '" alt="' . $lang['Visit_website'] . '" title="' . $lang['Visit_website'] . '" border="0" /></a>' : '&nbsp;';
$www = ( $profiledata['user_website'] ) ? '<a href="' . $profiledata['user_website'] . '" target="_userwww">' . $profiledata['user_website'] . '</a>' : '&nbsp;';

/*****[BEGIN]******************************************
 [ Mod:    Birthdays                           v3.0.0 ]
 ******************************************************/
$birthday = '&nbsp;';
//if ( !empty($profiledata['user_birthday']) && $profiledata['birthday_display'] != BIRTHDAY_AGE && $profiledata['birthday_display'] != BIRTHDAY_NONE )
if ( !empty($profiledata['user_birthday']) && $profiledata['birthday_display'] <> 2 && $profiledata['birthday_display'] <> 3 ) // 
{
	preg_match('/(..)(..)(....)/', sprintf('%08d',$profiledata['user_birthday']), $bday_parts);
	$bday_month = $bday_parts[1];
	$bday_day = $bday_parts[2];
	//$bday_year = ( $profiledata['birthday_display'] != BIRTHDAY_DATE ) ? $bday_parts[3] : 0;
	$bday_year = ( $profiledata['birthday_display'] <> 1 ) ? $bday_parts[3] : 0;
	$birthday_format = ($bday_year != 0) ? str_replace(array('y','Y'), array($bday_year % 100,$bday_year), $lang['DATE_FORMAT']) : preg_replace('#[^djFmMnYy]*[Yy]#','',$lang['DATE_FORMAT']);
	$birthday = create_date($birthday_format, gmmktime(12,0,0,$bday_month,$bday_day,2000), 0);
}
elseif( $profiledata['birthday_display'] == 2 )
{
	$bday_month_day = floor($profiledata['user_birthday'] / 10000);
	$bday_year_age = ( $profiledata['birthday_display'] != BIRTHDAY_NONE && $profiledata['birthday_display'] != BIRTHDAY_DATE ) ? $profiledata['user_birthday'] - 10000*$bday_month_day : 0;
	$fudge = ( gmdate('md') < $bday_month_day ) ? 1 : 0;
	$birthday = ( $bday_year_age ) ? gmdate('Y')-$bday_year_age-$fudge : false;
}
/*****[END]********************************************
 [ Mod:    Birthdays                           v3.0.0 ]
 ******************************************************/

if ( !empty($profiledata['user_icq']) )
{
    $icq_status_img = '<a href="http://wwp.icq.com/' . $profiledata['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $profiledata['user_icq'] . '&amp;img=5" width="18" height="18" border="0" /></a>';
    $icq_img = '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $profiledata['user_icq'] . '"><img src="' . $images['icon_icq'] . '" alt="' . $lang['ICQ'] . '" title="' . $lang['ICQ'] . '" border="0" /></a>';
    $icq =  '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $profiledata['user_icq'] . '">' . $lang['ICQ'] . '</a>';
}
else
{
    $icq_status_img = '&nbsp;';
    $icq_img = '&nbsp;';
    $icq = '&nbsp;';
}

$aim_img = ( $profiledata['user_aim'] ) ? '<a href="aim:goim?screenname=' . $profiledata['user_aim'] . '&amp;message=Hello+Are+you+there?"><img src="' . $images['icon_aim'] . '" alt="' . $lang['AIM'] . '" title="' . $lang['AIM'] . '" border="0" /></a>' : '&nbsp;';
$aim = ( $profiledata['user_aim'] ) ? '<a href="aim:goim?screenname=' . $profiledata['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $lang['AIM'] . '</a>' : '&nbsp;';

$msn_img = ( $profiledata['user_msnm'] ) ? '<a href="http://members.msn.com/' . $profiledata['user_msnm'] . '" target="_blank"><img src="' . $images['icon_msnm'] . '" alt="' . $lang['MSNM'] . '" title="' . $lang['MSNM'] . '" border="0" /></a>' : '&nbsp;';

$msn = ( $profiledata['user_msnm'] ) ? '<a href="http://members.msn.com/' . $profiledata['user_msnm'] . '" target="_blank">' . $profiledata['user_msnm'] . '</a>' : '&nbsp;';


$yim_img = ( $profiledata['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $profiledata['user_yim'] . '&amp;.src=pg"><img src="' . $images['icon_yim'] . '" alt="' . $lang['YIM'] . '" title="' . $lang['YIM'] . '" border="0" /></a>' : '';
$yim = ( $profiledata['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $profiledata['user_yim'] . '&amp;.src=pg">' . $lang['YIM'] . '</a>' : '';

$facebook_img = ( $profiledata['user_facebook'] ) ? '<a href="http://www.facebook.com/profile.php?id=' . $profiledata['user_facebook'] . '" target="_userwww"><img src="' . $images['icon_facebook'] . '" alt="' . $lang['Visit_facebook'] . '" title="' . $lang['Visit_facebook'] . '" border="0" /></a>' : '&nbsp;';
$facebook = ( $profiledata['user_facebook'] ) ? '<a href="' . $temp_url . '" target="_userwww">' . $profiledata['user_facebook'] . '</a>' : '&nbsp;';

$myspace_img = ( $profiledata['user_myspace'] ) ? '<a href="' . $profiledata['user_myspace'] . '" target="_userwww"><img src="' . $images['icon_myspace'] . '" alt="' . $lang['Visit_myspace'] . '" title="' . $lang['Visit_myspace'] . '" border="0" /></a>' : '&nbsp;';
$myspace = ( $profiledata['user_myspace'] ) ? '<a href="' . $temp_url . '" target="_userwww">' . $profiledata['user_myspace'] . '</a>' : '&nbsp;';

$temp_url = append_sid("search.$phpEx?search_author=" . urlencode($profiledata['username']) . "&amp;showresults=posts");
$search_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_search'] . '" alt="' . sprintf($lang['Search_user_posts'], $profiledata['username']) . '" title="' . sprintf($lang['Search_user_posts'], $profiledata['username']) . '" border="0" /></a>';
$search = '<a href="' . $temp_url . '">' . sprintf($lang['Search_user_posts'], $profiledata['username']) . '</a>';

/*****[BEGIN]******************************************
 [ Mod:    Gender                              v1.2.6 ]
 ******************************************************/
if ( !empty($profiledata['user_gender'])) 
{ 
           switch ($profiledata['user_gender']) 
           { 
                      case 1: $gender=$lang['Male'];break; 
                      case 2: $gender=$lang['Female'];break; 
                      default:$gender=$lang['No_gender_specify']; 
           } 
} else $gender=$lang['No_gender_specify'];
/*****[END]********************************************
 [ Mod:    Gender                              v1.2.6 ]
 ******************************************************/

//
// Generate page
//
/*****[BEGIN]******************************************
 [ Mod:    User Administration Link on Profile v1.0.0 ]
 ******************************************************/
if($userdata['user_level'] == ADMIN)
{
         $template->assign_vars(array(
           "L_USER_ADMIN_FOR" => $lang['User_admin_for'],
           "U_ADMIN_PROFILE" => append_sid("modules/Forums/admin/admin_users.$phpEx?mode=edit&amp;u=" . $profiledata['user_id']))
         );
    $template->assign_block_vars("switch_user_admin", array());
}
/*****[END]********************************************
 [ Mod:    User Administration Link on Profile v1.0.0 ]
 ******************************************************/

/*****[BEGIN]******************************************
 [ Mod:   Admin delete user with all postings v.1.0.5 ]
 ******************************************************/
$user_id=$userdata['user_id'];
if ( $userdata['user_level'] == ADMIN )
{
	$userdelete = "<form action=\"modules.php?name=Forums&file=userdel\"" . " method=\"post\" name=\"del_user\">";
    {
	  $userdelete .= "<tr><td><select name=\"delete_mode\">
      <option value=\"1\">" . $lang['User_only'] ."</option>
      <option value=\"2\">" . $lang['User_and_topics'] . "</option>
      <option value=\"3\">" . $lang['User_and_all_postings'] . "</option>
      <option value=\"4\">" . $lang['User_stay_postings_delete'] . "</option>
      </select>";
			$userdelete .= "<input type=\"submit\" name=\"del_user\" class=\"mainoption\" value=\"" . $lang['Delete'] . "\" />";
			$userdelete .= "<input type=\"hidden\" name=\"user_deleted_id\" value=\"" . $profiledata['user_id'] . "\" />";
	}
	$userdelete .= "</form>";

	 $template->assign_block_vars("admin_delete_this_user", array(
          'DELETE_USER_FORM' => $userdelete
          ));
}
/*****[END]********************************************
 [ Mod:   Admin delete user with all postings v.1.0.5 ]
 ******************************************************/
$page_title = $lang['Viewing_profile'];
include("includes/page_header.php");
/*****[BEGIN]******************************************
 [ Mod:    Online/Offline/Hidden               v2.2.7 ]
 ******************************************************/
if ($profiledata['user_session_time'] >= (time()-$board_config['online_time']))
{
    if ($profiledata['user_allow_viewonline'])
    {
        $online_status_img = '<a href="' . append_sid("viewonline.$phpEx") . '"><img src="' . $images['icon_online'] . '" alt="' . sprintf($lang['is_online'], $profiledata['username']) . '" title="' . sprintf($lang['is_online'], $profiledata['username']) . '" /></a>';
        $online_status = '<strong><a href="' . append_sid("viewonline.$phpEx") . '" title="' . sprintf($lang['is_online'], $profiledata['username']) . '"' . $online_color . '>' . $lang['Online'] . '</a></strong>';
    }
    else if ($userdata['user_level'] == ADMIN || $userdata['user_id'] == $profiledata['user_id'])
    {
        $online_status_img = '<a href="' . append_sid("viewonline.$phpEx") . '"><img src="' . $images['icon_hidden'] . '" alt="' . sprintf($lang['is_hidden'], $profiledata['username']) . '" title="' . sprintf($lang['is_hidden'], $profiledata['username']) . '" /></a>';
        $online_status = '<strong><em><a href="' . append_sid("viewonline.$phpEx") . '" title="' . sprintf($lang['is_hidden'], $profiledata['username']) . '"' . $hidden_color . '>' . $lang['Hidden'] . '</a></em></strong>';
    }
    else
    {
        $online_status_img = '<img src="' . $images['icon_offline'] . '" alt="' . sprintf($lang['is_offline'], $profiledata['username']) . '" title="' . sprintf($lang['is_offline'], $profiledata['username']) . '" />';
        $online_status = '<span title="' . sprintf($lang['is_offline'], $profiledata['username']) . '"' . $offline_color . '><strong>' . $lang['Offline'] . '</strong></span>';
    }
}
else
{
    $online_status_img = '<img src="' . $images['icon_offline'] . '" alt="' . sprintf($lang['is_offline'], $profiledata['username']) . '" title="' . sprintf($lang['is_offline'], $profiledata['username']) . '" />';
    $online_status = '<span title="' . sprintf($lang['is_offline'], $profiledata['username']) . '"' . $offline_color . '><strong>' . $lang['Offline'] . '</strong></span>';
}
/*****[END]********************************************
 [ Mod:    Online/Offline/Hidden               v2.2.7 ]
 ******************************************************/

/*****[BEGIN]******************************************
 [ Mod:    Attachment Mod                      v2.4.1 ]
 ******************************************************/
display_upload_attach_box_limits($profiledata['user_id']);
/*****[END]********************************************
 [ Mod:    Attachment Mod                      v2.4.1 ]
 ******************************************************/
$profiledata['user_from'] = str_replace(".gif", "", $profiledata['user_from']);

/*****[BEGIN]******************************************
 [ Base:    Nuke Patched                       v3.0.0 ]
 ******************************************************/
if (function_exists('get_html_translation_table'))
{
   $u_search_author = urlencode(strtr($profiledata['username'], array_flip(get_html_translation_table(HTML_ENTITIES))));
}
else
{
   $u_search_author = urlencode(str_replace(array('&', '&_#039;', '"', '<', '>'), array('&', "'", '"', '<', '>'), $profiledata['username']));
}
/*****[END]********************************************
 [ Base:    Nuke Patched                       v3.0.0 ]
 ******************************************************/

if (function_exists('get_html_translation_table'))
{
    $u_search_author = urlencode(strtr($profiledata['username'], array_flip(get_html_translation_table(HTML_ENTITIES))));
}
else
{
    $u_search_author = urlencode(str_replace(array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;'), array('&', "'", '"', '<', '>'), $profiledata['username']));
}
/*****[BEGIN]******************************************
 [ Mod:     Users Reputations Systems          v1.0.0 ]
 ******************************************************/
$reputation = '';
if ($rep_config['rep_disable'] == 0)
{
  if ($profiledata['user_reputation'] == 0)
  {
    $reputation = $lang['Zero_reputation'];
  } else
  {
    if ($rep_config['graphic_version'] == 0)
    {
      // Text version
      if ($profiledata['user_reputation'] > 0)
      {
        $reputation .= "<strong><font color=\"green\">" . round($profiledata['user_reputation'],1) . "</font></strong>";
      } else {
        $reputation .= "<strong><font color=\"red\">" . round($profiledata['user_reputation'],1) . "</font></strong>";
      }
    } else {
      // Graphic version
      get_reputation_medals($profiledata['user_reputation']);
    }
  }
  $sql = "SELECT COUNT(user_id) AS count_reps
      FROM " . REPUTATION_TABLE . " AS r
      WHERE r.user_id = " . $profiledata['user_id'] . "
      GROUP BY user_id";
  if ( !($result = $db->sql_query($sql)) )
  {
    message_die(GENERAL_ERROR, "Could not obtain reputation stats for this user", '', __LINE__, __FILE__, $sql);
  }
  $row_rep = $db->sql_fetchrow($result);
  if ($row_rep)
  {
    $reputation .= " <br /><span class=\"gensmall\">(<a href=\""  . append_sid("reputation.$phpEx?a=stats&amp;" . POST_USERS_URL . "=" . $profiledata['user_id']) . "\" target=\"_blank\" onClick=\"popupWin = window.open(this.href, '" . $lang['Reputation'] . "', 'location,width=700,height=400,top=0,scrollbars=yes'); popupWin.focus(); return false;\">" . $lang['Votes'] . "</a>: " . $row_rep['count_reps'] . ")</span>";
  }
}
/*****[END]********************************************
 [ Mod:     Users Reputations System           v1.0.0 ]
 ******************************************************/
 
/*****[BEGIN]******************************************
 [ Mod:    View Sig                            v1.0.0 ]
 ******************************************************/
$user_sig = '';
if(!empty($profiledata['user_sig'])) {
    include_once('includes/bbcode.'.$phpEx);
    include_once('includes/functions_post.'.$phpEx);

    $html_on    = ( $profiledata['user_allowhtml'] && $board_config['allow_html'] ) ? 1 : 0 ;
    $bbcode_on  = ( $profiledata['user_allowbbcode'] && $board_config['allow_bbcode']  ) ? 1 : 0 ;
    $smilies_on = ( $profiledata['user_allowsmile'] && $board_config['allow_smilies']  ) ? 1 : 0 ;

    $signature_bbcode_uid = $profiledata['user_sig_bbcode_uid'];
    $signature = ( $signature_bbcode_uid != '' ) ? preg_replace("/:(([a-z0-9]+:)?)$signature_bbcode_uid\]/si", ']', $profiledata['user_sig']) : $profiledata['user_sig'];
    $bbcode_uid = $profiledata['user_sig_bbcode_uid'];
    $user_sig = prepare_message($profiledata['user_sig'], $html_on, $bbcode_on, $smilies_on, $bbcode_uid);

    if( $user_sig != '' )
    {

        if ( $bbcode_on  == 1 ) { $user_sig = bbencode_second_pass($user_sig, $bbcode_uid); }
        if ( $bbcode_on  == 1 ) { $user_sig = bbencode_first_pass($user_sig, $bbcode_uid); }
        if ( $bbcode_on  == 1 ) { $user_sig = make_clickable($user_sig); }
        if ( $smilies_on == 1 ) { $user_sig = smilies_pass($user_sig); }

        $template->assign_block_vars('user_sig', array());

        $user_sig = nl2br($user_sig);
        $user_sig = html_entity_decode($user_sig);
    }
    else
    {
        $user_sig = '';
    }
}
/*****[END]********************************************
 [ Mod:    View Sig                            v1.0.0 ]
 ******************************************************/
/*****[BEGIN]******************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/
if($profiledata['bio']) {
    $template->assign_block_vars('user_extra', array());
}
/*****[END]********************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/

$template->assign_vars(array(
/*****[BEGIN]******************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
    'USERNAME' => UsernameColor($profiledata['username']),
/*****[END]********************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
    'JOINED' => $profiledata['user_regdate'],
/*****[BEGIN]******************************************
 [ Mod:    Show Groups                         v1.0.1 ]
 ******************************************************/
    'GROUPS' => $groups,
/*****[END]********************************************
 [ Mod:    Show Groups                         v1.0.1 ]
 ******************************************************/
/*****[BEGIN]******************************************
 [ Mod:    Multiple Ranks And Staff View       v2.0.3 ]
 ******************************************************/
	'USER_RANK_01' => $user_rank_01,
	'USER_RANK_01_IMG' => $user_rank_01_img,
	'USER_RANK_02' => $user_rank_02,
	'USER_RANK_02_IMG' => $user_rank_02_img,
	'USER_RANK_03' => $user_rank_03,
	'USER_RANK_03_IMG' => $user_rank_03_img,
	'USER_RANK_04' => $user_rank_04,
	'USER_RANK_04_IMG' => $user_rank_04_img,
	'USER_RANK_05' => $user_rank_05,
	'USER_RANK_05_IMG' => $user_rank_05_img,
/*****[END]********************************************
 [ Mod:    Multiple Ranks And Staff View       v2.0.3 ]
 ******************************************************/
    'POSTS_PER_DAY' => $posts_per_day,
    'POSTS' => $profiledata['user_posts'],
    'PERCENTAGE' => $percentage . '%',
    'POST_DAY_STATS' => sprintf($lang['User_post_day_stats'], $posts_per_day),
    'POST_PERCENT_STATS' => sprintf($lang['User_post_pct_stats'], $percentage),
/*****[BEGIN]******************************************
 [ Mod:     Users Reputations Systems          v1.0.0 ]
 ******************************************************/
    'REPUTATION' => $reputation,
/*****[END]********************************************
 [ Mod:     Users Reputations System           v1.0.0 ]
 ******************************************************/

 /*****[Begin]******************************************
 [ Mod:     UPLOAD Switchbox Repair              v1.0.1 ]
 *******************************************************/
        'LCAP_IMG' => $images['voting_lcap'],
        'MAINBAR_IMG' => $images['voting_graphic'][0],
        'RCAP_IMG' => $images['voting_rcap'],
 /*****[END]********************************************
 [ Mod:     UPLOAD Switchbox Repair             v1.0.1 ]
 *******************************************************/  
    'SEARCH_IMG' => $search_img,
    'SEARCH' => $search,
    'PM_IMG' => $pm_img,
    'PM' => $pm,
    'EMAIL_IMG' => $email_img,
    'EMAIL' => $email,
    'WWW_IMG' => $www_img,
    'WWW' => $www,
    'ICQ_STATUS_IMG' => $icq_status_img,
    'ICQ_IMG' => $icq_img,
    'ICQ' => $icq,
    'AIM_IMG' => $aim_img,
    'AIM' => $aim,
    'MSN_IMG' => $msn_img,
    'MSN' => $msn,
    'YIM_IMG' => $yim_img,
    'YIM' => $yim,
	'FACEBOOK_IMG' => $facebook_img,
	'FACEBOOK' => $facebook,
	'MYSPACE_IMG' => $myspace_img,
	'MYSPACE' => $myspace,

/*****[BEGIN]******************************************
 [ Mod:    Birthdays                           v3.0.0 ]
 ******************************************************/
	'BIRTHDAY' => $birthday,
/*****[END]********************************************
 [ Mod:    Birthdays                           v3.0.0 ]
 ******************************************************/
/*****[BEGIN]******************************************
 [ Mod:     Member Country Flags               v2.0.7 ]
 ******************************************************/
	'LOCATION' => $location,
/*****[END]********************************************
 [ Mod:     Member Country Flags               v2.0.7 ]
 ******************************************************/
    'OCCUPATION' => ( $profiledata['user_occ'] ) ? $profiledata['user_occ'] : '&nbsp;',
    'INTERESTS' => ( $profiledata['user_interests'] ) ? $profiledata['user_interests'] : '&nbsp;',
/*****[BEGIN]******************************************
 [ Mod:    Gender                              v1.2.6 ]
 ******************************************************/
    'GENDER' => $gender,
/*****[END]********************************************
 [ Mod:    Gender                              v1.2.6 ]
 ******************************************************/
/*****[BEGIN]******************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/
    'EXTRA_INFO' => ( $profiledata['bio'] ) ? $profiledata['bio'] : '&nbsp;',
/*****[END]********************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/
    'AVATAR_IMG' => $avatar_img,
/*****[BEGIN]******************************************
 [ Mod:    Admin User Notes                    v1.0.0 ]
 ******************************************************/
    'ADMIN_NOTES' => $profiledata['user_admin_notes'],
    'L_ADMIN_NOTES' => $lang['Admin_notes'],
/*****[END]********************************************
 [ Mod:    Admin User Notes                    v1.0.0 ]
 ******************************************************/	
/*****[BEGIN]******************************************
 [ Mod:    View Sig                            v1.0.0 ]
 ******************************************************/
    'USER_SIG' => $user_sig,
    'L_SIG' => $lang['Signature'],
/*****[END]********************************************
 [ Mod:    View Sig                            v1.0.0 ]
 ******************************************************/

/*****[BEGIN]******************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
    'L_VIEWING_PROFILE' => sprintf($lang['Viewing_user_profile'], UsernameColor($profiledata['username'])),
    'L_ABOUT_USER' => sprintf($lang['About_user'], UsernameColor($profiledata['username'])),
/*****[END]********************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
    'L_AVATAR' => $lang['Avatar'],
    'L_POSTER_RANK' => $lang['Poster_rank'],
    'L_JOINED' => $lang['Joined'],
/*****[BEGIN]******************************************
 [ Mod:    Show Groups                         v1.0.1 ]
 ******************************************************/
    'L_GROUPS' => $lang['Groups'],
/*****[END]********************************************
 [ Mod:    Show Groups                         v1.0.1 ]
 ******************************************************/
    'L_TOTAL_POSTS' => $lang['Total_posts'],
/*****[BEGIN]******************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
    'L_SEARCH_USER_POSTS' => sprintf($lang['Search_user_posts'], UsernameColor($profiledata['username'])),
/*****[END]********************************************
 [ Mod:    Advanced Username Color             v1.0.5 ]
 ******************************************************/
    'L_CONTACT' => $lang['Contact'],
    'L_EMAIL_ADDRESS' => $lang['Email_address'],
    'L_EMAIL' => $lang['Email'],
    'L_PM' => $lang['Private_Message'],
    'L_ICQ_NUMBER' => $lang['ICQ'],
    'L_YAHOO' => $lang['YIM'],
	'L_FACEBOOK_PROFILE' => $lang['FACEBOOK_PROFILE'],
	'L_VISIT_FACEBOOK' => $lang['Visit_facebook'],
	'L_MYSPACE' => $lang['MYSPACE'],
	'L_VISIT_MYSPACE' => $lang['Visit_myspace'],
    'L_AIM' => $lang['AIM'],
    'L_MESSENGER' => $lang['MSNM'],
    'L_WEBSITE' => $lang['Website'],
/*****[BEGIN]******************************************
 [ Mod:    Birthdays                           v3.0.0 ]
 ******************************************************/
	'L_BIRTHDAY' => $lang['Birthday'],
/*****[END]********************************************
 [ Mod:    Birthdays                           v3.0.0 ]
 ******************************************************/
    'L_LOCATION' => $lang['Location'],
    'L_OCCUPATION' => $lang['Occupation'],
    'L_INTERESTS' => $lang['Interests'],
/*****[BEGIN]******************************************
 [ Mod:    Gender                              v1.2.6 ]
 ******************************************************/
    'L_GENDER' => $lang['Gender'], 
/*****[END]********************************************
 [ Mod:    Gender                              v1.2.6 ]
 ******************************************************/
/*****[BEGIN]******************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/
    'L_EXTRA_INFO' => $lang['Extra_Info'],
/*****[END]********************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/

/*****[BEGIN]******************************************
 [ Mod:     Arcade                             v3.0.2 ]
 ******************************************************/
    'L_ARCADE' => $lang['lib_arcade'],
    'URL_STATS' => '<a class="genmed" href="' . append_sid("statarcade.$phpEx?uid=" . $profiledata['user_id'] ) . '">' . $lang['statuser'] . '</a>',
/*****[END]********************************************
 [ Mod:     Arcade                             v3.0.2 ]
 ******************************************************/

/*****[BEGIN]******************************************
 [ Mod:    Online/Offline/Hidden               v2.2.7 ]
 ******************************************************/
    'ONLINE_STATUS_IMG' => $online_status_img,
    'ONLINE_STATUS' => $online_status,
    'L_ONLINE_STATUS' => $lang['Online_status'],
/*****[END]********************************************
 [ Mod:    Online/Offline/Hidden               v2.2.7 ]
 ******************************************************/

    'U_SEARCH_USER' => append_sid("search.$phpEx?search_author=" . $u_search_author),

    'S_PROFILE_ACTION' => append_sid("profile.$phpEx"))
);

/*****[BEGIN]******************************************
 [ Mod:     XData                              v1.0.3 ]
 ******************************************************/
include_once('includes/bbcode.'.$phpEx);

$xd_meta = get_xd_metadata();
$xdata = get_user_xdata($HTTP_GET_VARS[POST_USERS_URL]);
while ( list($code_name, $info) = each($xd_meta) )
{
    $value = isset($xdata[$code_name]) ? $xdata[$code_name] : null;
/*****[ANFANG]*****************************************
 [ Mod:    XData Date Conversion               v0.1.1 ]
 ******************************************************/
		if ($info['field_type'] == 'date')
		{
				$value = create_date($userdata['user_dateformat'], $value, $userdata['user_timezone']);
		}
/*****[ENDE]*******************************************
 [ Mod:    XData Date Conversion               v0.1.1 ]
 ******************************************************/
    if ( !$info['allow_html'] )
    {
        $value = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $value);
    }

    if ( $info['allow_bbcode'] && $profiledata['user_sig_bbcode_uid'] != '')
    {
        $value = bbencode_second_pass($value, $profiledata['xdata_bbcode']);
    }

    if ($info['allow_bbcode'])
    {
        $value = make_clickable($value);
    }

    if ( $info['allow_smilies'] )
    {
        $value = smilies_pass($value);
    }

    $value = str_replace("\n", "\n<br />\n", $value);

    if ( $info['display_viewprofile'] == XD_DISPLAY_NORMAL )
    {
        if ( isset($xdata[$code_name]) )
        {
            $template->assign_block_vars('xdata', array(
                'NAME' => $info['field_name'],
                'VALUE' => $value
                )
            );
        }
    }
    elseif ( $info['display_viewprofile'] == XD_DISPLAY_ROOT )
    {
        if ( isset($xdata[$code_name]) )
        {
            $template->assign_vars( array( $code_name => $value ) );
            $template->assign_block_vars( "switch_$code_name", array() );
        }
        else
        {
            $template->assign_block_vars( "switch_no_$code_name", array() );
        }
    }
}
/*****[END]********************************************
 [ Mod:     XData                              v1.0.3 ]
 ******************************************************/

/*****[BEGIN]******************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/
global $cookie;
$r_uid = intval($cookie[0]);
if($profiledata['user_id'] == $r_uid) {
    get_lang("Your_Account");
    define_once('CNBYA',true);
    include_once(NUKE_MODULES_DIR."Your_Account/navbar.php");
    nav(1);
}
/*****[END]********************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/
 
/*****[BEGIN]******************************************
 [ Mod:    Admin User Notes                    v1.0.0 ]
 ******************************************************/
if ( $userdata['user_level'] == ADMIN )
{
  $template->assign_block_vars('switch_admin_notes', array());
}
/*****[END]********************************************
 [ Mod:    Admin User Notes                    v1.0.0 ]
 ******************************************************/
$template->pparse('body');

/*****[BEGIN]******************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/
global $admin;
if (is_admin()) {
    get_lang("Your_Account");
    echo "<center>";
    if($profiledata['user_lastvisit'] != 0){
        echo "<center>"._LASTVISIT." <strong>".date($board_config['default_dateformat'],$profiledata['user_lastvisit'])."</strong><br />";
    } else {
        echo "<center>"._LASTVISIT." <strong>"._LASTNA."</strong><br />";
    }
    if ($profiledata['last_ip'] != 0) {
        echo "<center>"._LASTIP." <strong>".$profiledata['last_ip']."</strong><br /><br />";
        echo "[ <a href='".$admin_file.".php?op=ABBlockedIPAdd&amp;tip=".$profiledata['last_ip']."'>"._BANTHIS."</a> ]<br />";
    }
    echo "[ <a href=\"modules.php?name=Your_Account&amp;file=admin&amp;op=suspendUser&amp;chng_uid=".$profiledata['user_id']."\">"._SUSPENDUSER."</a> ] ";
    echo "[ <a href=\"modules.php?name=Your_Account&amp;file=admin&amp;op=deleteUser&amp;chng_uid=".$profiledata['user_id']."\">"._DELETEUSER."</a> ]<br />";
    echo "</center>";
}
global $currentlang;
if(!isset($currentlang)) { $currentlang = $nuke_config['language']; }
if (file_exists(NUKE_MODULES_DIR.'Your_Account/language/lang-'.$currentlang.'.php')) {
  @include_once(NUKE_MODULES_DIR.'Your_Account/language/lang-'.$currentlang.'.php');
} else {
  @include_once(NUKE_MODULES_DIR.'Your_Account/language/lang-english.php');
}
define_once('CNBYA',true);
$username = $profiledata['username'];
$usrinfo = $profiledata;
$incsdir = dir(NUKE_MODULES_DIR."Your_Account/includes");
$incslist = '';
while($func=$incsdir->read()) {
    if(substr($func, 0, 3) == "ui-") {
        $incslist .= "$func ";
    }
}
closedir($incsdir->handle);
$incslist = explode(" ", $incslist);
sort($incslist);
for ($i=0; $i < count($incslist); $i++) {
    if($incslist[$i]!="") {
        $counter = 0;
        include($incsdir->path."/$incslist[$i]");
    }
}
/*****[END]********************************************
 [ Mod:    YA Merge                            v1.0.0 ]
 ******************************************************/

include("includes/page_tail.php");

?>