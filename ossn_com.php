<?php
/**
 * Open Source Social Network
 *
 * @package   Open Source Social Network
 * @author    Open Social Website Core Team <info@openteknik.com>
 * @copyright 2021 OpenTeknik
 * @license   OPEN SOURCE SOCIAL NETWORK LICENSE 4.0
 * @link      http://www.opensource-socialnetwork.org/licence
 */
function group_posts_newsfeed_init(){
		ossn_unset_hook('wall', 'getPublicPosts', 'ossn_block_strip_posts');

		ossn_add_hook('wall', 'getPublicPosts', 'groups_posts_show_on_newsfeed');
		ossn_add_hook('wall', 'templates:item', 'group_posts_show_group_name', 90);
}
function group_posts_show_group_name($hook, $type, $return, $param){
		$context = ossn_get_context();
		if($return['post']->type == 'group' && isset($context) && $context == 'home'){
				$return['show_group'] = true;
		}
		return $return;
}
function groups_posts_show_on_newsfeed($hook, $type, $return, $params){
		$return['type'] = array(
				'user',
				'group',
		);
		$groups = new OssnGroup();
		$list   = $groups->getMyGroups(ossn_loggedin_user());
		if($list){
				$results = array();
				foreach ($list as $group){
						$results[] = $group->guid;
				}
				$usergroups = array_unique($results);
				$ugroups    = implode(',', $usergroups);
				if(!empty($ugroups)){
						$groups_query = "AND o.owner_guid IN($ugroups)";
				}
				unset($return['entities_pairs'][1]);
				$return['entities_pairs'][1] = array(
						'name'   => 'poster_guid',
						'value'  => true,
						'wheres' => "((emd0.value=2) OR (emd0.value=3 AND o.type='user' AND [this].value IN({$params['friends_guids']})) OR (emd0.value=1 AND o.type='group' {$groups_query}))",
				);
				if(isset($params['user']->guid) && !empty($results)){
						$return['wheres'][] = "((emd1.value NOT IN (SELECT DISTINCT relation_to FROM `ossn_relationships` WHERE relation_from={$params['user']->guid} AND type='userblock') AND emd1.value NOT IN (SELECT DISTINCT relation_from FROM `ossn_relationships` WHERE relation_to={$params['user']->guid} AND type='userblock')))";
				}
		}
		return $return;
}
ossn_register_callback('ossn', 'init', 'group_posts_newsfeed_init');
