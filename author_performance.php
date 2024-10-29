<?php
/*
Plugin Name:Author Performance
Plugin URI: http://barisatasoy.com/author-performance
Description: Functions for determining how many post or words each author has written.
Author: Barış Atasoy
Author URI: http://barisatasoy.com/
*/
/*  Copyright 2009 Barış Atasoy  (email : b_atasoy@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function author_performance_activate() {
	global $wpdb;
  	$query = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'author_performance (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_title` text NOT NULL,
  `post_id` int(11) NOT NULL,
  `post_author_id` int(11) NOT NULL,
  `word_count` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;';

   $wpdb->query($query); 
}



function author_performance_top_authors_by_word_count ($count,$usermeta='yes') {
	global $wpdb;
	$rec1=$wpdb->get_results("select SUM(word_count) as wc,COUNT(id) as tc,post_author_id from $wpdb->prefix"."author_performance group by post_author_id order by wc desc limit $count");
  
	foreach ($rec1 as $rec2) {
		$a++;
	$top_authors[$a]['author_id']=$rec2->post_author_id;
	$top_authors[$a]['word_count']=$rec2->wc;
	$top_authors[$a]['post_count']=$rec2->tc;
	 
	 if ($usermeta=='yes')
	 	{
	 		$user_data=$wpdb->get_row("select * from $wpdb->users where ID=$rec2->post_author_id");
	 		$top_authors[$a]['display_name']=$user_data->display_name;
	 		$top_authors[$a]['login_name']=$user_data->user_login;
	 		}
	
	}
	return $top_authors;
}

// TOP AUTHORS BY TITLE COUNT

function author_performance_top_authors_by_post_count ($count,$usermeta='yes') {
	global $wpdb;
	$rec1=$wpdb->get_results("select SUM(word_count) as wc,COUNT(id) as tc,post_author_id from $wpdb->prefix"."author_performance group by post_author_id order by tc desc limit $count");
  
	foreach ($rec1 as $rec2) {
		$a++;
	$top_authors[$a]['author_id']=$rec2->post_author_id;
	$top_authors[$a]['word_count']=$rec2->wc;
	$top_authors[$a]['post_count']=$rec2->tc;
	 
	 if ($usermeta=='yes')
	 	{
	 		$user_data=$wpdb->get_row("select * from $wpdb->users where ID=$rec2->post_author_id");
	 		$top_authors[$a]['display_name']=$user_data->display_name;
	 		$top_authors[$a]['login_name']=$user_data->user_login;
	 		}
	
	}
	return $top_authors;
}

// LONGEST POSTS

function author_performance_longest_posts ($count,$usermeta='yes') {
	global $wpdb;
	$rec1=$wpdb->get_results("select * from $wpdb->prefix"."author_performance order by word_count desc limit $count");
  
	foreach ($rec1 as $rec2) {
		$a++;
	$top_authors[$a]['post_title']=$rec2->post_title;
	$top_authors[$a]['word_count']=$rec2->word_count;
	$top_authors[$a]['link']=$wpdb->get_var("select guid from $wpdb->posts where ID=$rec2->post_id");	 
	 if ($usermeta=='yes')
	 	{
	 		$user_data=$wpdb->get_row("select * from $wpdb->users where ID=$rec2->post_author_id");
	 		$top_authors[$a]['display_name']=$user_data->display_name;
	 		$top_authors[$a]['login_name']=$user_data->user_login;
	 		}
	
	}
	return $top_authors;
}

// LONGEST POST BY AUTHOR

function author_performance_longest_post_by_author ($authorid,$count) {
	global $wpdb;
	$rec1=$wpdb->get_results("select * from $wpdb->prefix"."author_performance where post_author_id=$authorid order by word_count desc limit $count");
  
	foreach ($rec1 as $rec2) {
		$a++;
	$top_authors[$a]['post_title']=$rec2->post_title;
	$top_authors[$a]['word_count']=$rec2->word_count;
	$top_authors[$a]['link']=$wpdb->get_var("select guid from $wpdb->posts where ID=$rec2->post_id");	 
	
	}
	return $top_authors;
}


function author_performance_update()
{
global $wpdb;

$recs=$wpdb->get_results("SELECT ID, post_title,post_content,post_author FROM $wpdb->prefix"."posts WHERE post_type='post' AND post_status='publish' ORDER BY ID DESC");

foreach ($recs as $record)
	{
		$word_count=count(str_word_count($record->post_content,1,'öçÖÇŞİşiIğĞüÜ'));
 		$yaz=$wpdb->query("INSERT INTO $wpdb->prefix"."author_performance (post_title,post_id,post_author_id,word_count) VALUES ('$record->post_title',$record->ID , $record->post_author, $word_count)");
		
		}
	echo "<p>Table updated. <a href='options-general.php?page=options_ap'>Click here.</a></p>";
	  }


function author_performance_update_table() {
	
	// DO I HAVE TO UPDATE?
	if (get_option("author_performance_count_on_save")=='1'){
		global $wpdb;
		$del=$wpdb->query("TRUNCATE $wpdb->prefix"."author_performance");
		//echo "update";
	}
	
}


function author_performance_options()
{
	global $wpdb;
	
	
	$rec_count=$wpdb->get_var("select count(id) from $wpdb->prefix"."author_performance");

	?>
	<h2><?php _e("Author Performance", "mcf"); ?></h2>
	<form id="mcf_form" method="post" action="options-general.php?page=options_ap">
    <?php

		
	if ($rec_count<1) {
			if (isset($_POST["action"]) && $_POST["action"] == "update") {
		author_performance_update();	
		} else
		{
		?>
	 
		<div class="wrap">

		<p>No records yet. Click the update button to count posts and generate authors table. This will take a while depending on post count and lenght.</p>	
	 	<p class="submit">
		<input type="hidden" name="action" value="update" />
		<input type="submit" name="update" value="<?php echo "Update"; ?>" />
		</p>
		</div> 	
		<?php
		}
	}
		else {
			$a=author_performance_top_authors_by_word_count(2,'yes');
			$b=author_performance_top_authors_by_post_count(2,'yes');
			$c=author_performance_longest_posts(3,'yes');
			$d=author_performance_longest_post_by_author('1','2');
		
	if ($_POST['submit-type'] == 'option_set')
	{

	$author_performance_count_on_save2=$_POST['author_performance_count_on_save']; if ($author_performance_count_on_save2=='1') { update_option('author_performance_count_on_save','1'); } else { update_option('author_performance_count_on_save','0'); }
	//echo $publish_posts;
	}
		?>
	
	<form method="post" >
	<fieldset>
	<p>
	<input type="checkbox" name="author_performance_count_on_save" id="author_performance_count_on_save" value="1" <?php if(get_option("author_performance_count_on_save")=='1'){ echo "checked=\"true\""; }?> />
	<label for="new_post"><?php echo "Re-update table when a post is saved or updated (Takes long time if you have hundreds of lenghty posts!)"; ?></label>
	</p>
	</fieldset>

	<input type="hidden" name="submit-type" value="option_set">
		<p class="submit"><input type="submit" name="submit" value="<?php echo "Save"; ?>" /></p>
		
		
		
		

  <table width="540" border="0" cellpadding="4">
  <tr>
    <td colspan="3" style="color: #757575;	font-weight: bold;font-family: Arial, Helvetica, sans-serif;text-align: center;background-color:#CCCC66;">TOP AUTHORS BY WORD COUNT</td>
  </tr>
  <tr>
    <td width="300"><strong>Author Name</strong></td>
    <td width="120"><strong>Word Count</strong></td>
    <td width="120"><strong>Post Count</strong></td>
  </tr>
 
 <?php
 foreach ($a as $tobwc) {
 ?>
 
  <tr>
    <td><?php echo $tobwc['display_name']; ?></td>
    <td><?php echo $tobwc['word_count']; ?></td>
    <td><?php echo $tobwc['post_count']; ?></td>
  </tr>

 <?php 
 }
 ?>
<tr>
<td style="padding-top:10px" colspan="3">Usage: $var=author_performance_top_authors_by_word_count(2,'yes'); This gives an array of two authors, sorted by word count in posts. If you omit the second parameter, author data will not be queried. You can use foreach loop to output data. For further usage, visit <a href="http://barisatasoy.com/author-performance">Author Performance page.</a> </td>
</tr>
</table>
	




  <table width="540" border="0" cellpadding="4">
  <tr>
    <td colspan="3" style="color: #757575;	font-weight: bold;font-family: Arial, Helvetica, sans-serif;text-align: center;background-color:#CCCC66;">TOP AUTHORS BY POST COUNT</td>
  </tr>
  <tr>
    <td width="300"><strong>Author Name</strong></td>
    <td width="120"><strong>Word Count</strong></td>
    <td width="120"><strong>Post Count</strong></td>
  </tr>
 
 <?php
 foreach ($b as $tobpc) {
 ?>
 
  <tr>
    <td><?php echo $tobpc['display_name']; ?></td>
    <td><?php echo $tobpc['word_count']; ?></td>
    <td><?php echo $tobpc['post_count']; ?></td>
  </tr>

 <?php 
 }
 ?>
<tr>
<td style="padding-top:10px" colspan="3">Usage: $var=author_performance_top_authors_by_post_count(2,'yes'); This example gives an array of two authors, sorted by post count. If you omit the second parameter, author data will not be queried. You can use foreach loop to output data. For further usage, visit <a href="http://barisatasoy.com/author-performance">Author Performance page.</a> </td>
</tr>
</table>





  <table width="540" border="0" cellpadding="4">
  <tr>
    <td colspan="3" style="color: #757575;	font-weight: bold;font-family: Arial, Helvetica, sans-serif;text-align: center;background-color:#CCCC66;">LONGEST POSTS</td>
  </tr>
  <tr>
    <td width="150"><strong>Author Name</strong></td>
    <td width="120"><strong>Word Count</strong></td>
    <td width="270"><strong>Post Title</strong></td>
  </tr>
 
 <?php
 foreach ($c as $tolp) {
 ?>
 
  <tr>
    <td><?php echo $tolp['display_name']; ?></td>
    <td><?php echo $tolp['word_count']; ?></td>
    <td><a href="<?php echo $tolp['link']; ?>"><?php echo $tolp['post_title']; ?></a></td>
  </tr>

 <?php 
 }
 ?>
<tr>
<td style="padding-top:10px" colspan="3">Usage: $var=author_performance_longest_posts(2,'yes'); This example gives an array of two longest posts, sorted by word count. If you omit the second parameter, author data will not be queried. You can use foreach loop to output data. For further usage, visit <a href="http://barisatasoy.com/author-performance">Author Performance page.</a> </td>
</tr>
</table>


 <table width="540" border="0" cellpadding="4">
  <tr>
    <td colspan="3" style="color: #757575;	font-weight: bold;font-family: Arial, Helvetica, sans-serif;text-align: center;background-color:#CCCC66;">LONGEST POSTS BY AUTHOR ID</td>
  </tr>
  <tr>
    <td width="150"><strong>Author Name</strong></td>
    <td width="120"><strong>Word Count</strong></td>
    <td width="270"><strong>Post Title</strong></td>
  </tr>
 
 <?php
 foreach ($d as $tolp) {
 ?>
 
  <tr>
    <td><?php echo $tolp['display_name']; ?></td>
    <td><?php echo $tolp['word_count']; ?></td>
    <td><a href="<?php echo $tolp['link']; ?>"><?php echo $tolp['post_title']; ?></a></td>
  </tr>

 <?php 
 }
 ?>
<tr>
<td style="padding-top:10px" colspan="3">Usage: $var=author_performance_longest_post_by_author(1,2); This example gives an array of two longest posts, sorted by word count, written by author having ID=2 (These ID's come from Wordpress users' database). You can use foreach loop to output data. For further usage, visit <a href="http://barisatasoy.com/author-performance">Author Performance page.</a> </td>
</tr>
</table>
			<?php
						
			}

}

function author_performance_dashboard_widget_function() {
		global $wpdb;
		$recs=$wpdb->get_row("select count(id) as postcount,sum(word_count) as wordcount, COUNT(DISTINCT post_author_id) as author_count  from $wpdb->prefix"."author_performance");

	if ($recs->wordcount>0)	{
	$a_count=$wpdb->get_var("select distinct count(post_author_id) from $wpdb->prefix"."author_performance");
	echo "<p>"."There are $recs->author_count author(s) who wrote $recs->wordcount words and $recs->postcount posts. "."<a href='options-general.php?page=options_ap'>Click for details.</a></p>";
	}
	else
	{
	echo "<p>You have to initialize database first. <a href='options-general.php?page=options_ap'>Click here to initialize and update database.</a></p>";	
		}
}
 

function author_performance_dashboard_widget() {
	wp_add_dashboard_widget( 'author_performance_dashboard_widget', __( 'Author Performance' ), 'author_performance_dashboard_widget_function' );
}

add_action('wp_dashboard_setup', 'author_performance_dashboard_widget');
add_action('admin_menu', 'add_ap_menu');
add_action ( 'save_post', 'author_performance_update_table');
function add_ap_menu() {
	add_options_page('Author Performance','Author Performance',  'administrator', 'options_ap', 'author_performance_options');
	}

register_activation_hook( __FILE__, 'author_performance_activate' );
?>