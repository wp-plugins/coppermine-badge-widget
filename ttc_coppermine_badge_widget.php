<?php
/*
Plugin Name: TTC Coppermine Badge Widget
Version: 1.0
Plugin URI: http://herselfswebtools.com/2007/12/coppermine-badge-widget-for-wp.html
Author: Linda MacPhee-Cobb
Author URI: http://timestocome.com
Description: Create a badge of your most recent Coppermine gallery photos in your sidebar
*/


// all functions go in this function this is like main
function ttc_coppermine_badge_init() {

	//gracefully fail if sidebar gets deactivated
	if ( !function_exists('register_sidebar_widget') )
	return;
	
	//output the widget here
	function ttc_coppermine_badge($args) 
	{
		//theme related things
		extract($args);
		
		$options = get_option ( 'ttc_coppermine_badge' );
		$thumbnails = $options['thumbnails'];
		$title = $options['title'];
		$random = $options ['random'] ? '1' : '0';          //latest photos or random photos
		
		//sanity check number of thumbnails
		if (( (int)$thumbnails < 1 ) || ( (int)$thumbnails > 20 )){
			$thumbnails = 5;
		}
		
		
		//get information this is our server, user name and other information
		include ( "wp_coppermine.php" );
	
		//connect to coppermine server
		if ( !($db = mysql_connect( $hostname, $login, $password ))){
			die ( "Can not connect to server" );
		}else{
			//select db
			if ( !(mysql_select_db("$database", $db ))){
				die ( "Can not select database" );
			}
		}
		
		
		//output to sidebar
		echo $before_widget; 
		echo $before_title;
		echo $title;
		echo $after_title;
		
		
		// one query for random selection another for most recent selection of images
		if ( $random ){
			$query = "select filepath, filename, ctime from cpg_pictures order by rand() desc limit $thumbnails;";
		}else {
			$query = "select filepath, filename, ctime from cpg_pictures order by ctime desc limit $thumbnails;";

		}
		
		//what the mysql server told us
		$result = mysql_query($query);	
		$count = mysql_numrows( $result );
	       
		
		//start badge html output
		$link =  "<center><a href=\"$url/$directory\">";
		$link .= "<table border=3><tr><td>";
	
		$i = 0;
		while ( $i < $count ){
		
			$path = mysql_result($result, $i, "filepath" );
			$name = mysql_result($result, $i, "filename" );
		        
			$link .= "\n<br><img src=\"$url/$directory/albums/$path" . "thumb_$name\">";
                          
			$i++;
		}
	
		$link .= "</td></tr></table>";
		$link .= "</a></center>";
		//end badge output
	    
		//now print badge output to webpage
		echo "<br> $link <br>";
		
		
		//clean up
		mysql_close();
	
	
		return $link;
		
	}

	//user options
	function ttc_coppermine_badge_control() 
	{
		$options = get_option ( 'ttc_coppermine_badge' );
		
		//set initial values if empty else fetch current
		if ( ! is_array($options) ){
			
			$options = array( 'title'=>"CoppermineBadge", 'thumbnails'=>"5", 'random'=>"0" );
		
		}else{	
		
			//fetch options
			$thumbnails = $options['thumbnails'];								// number of thumbnails to dispay 1-20 allowed we do a sanity check in main
			$title = $options['title'];											// title to show in sidebar 
			$random = $options ['random'] ? 'checked="checked"' : "";			// ? latest photos or ? random photos
		}
		
		
		//clean up and post
		if ( $_POST['ttc_coppermine_badge-submit'] ) {
		
			//title
			$options['title'] = strip_tags(stripslashes($_POST['ttc_coppermine_badge-title']));
			
			//number of thumbnails to display
			$options['thumbnails'] = (int) $_POST['ttc_coppermine_badge-thumbnails'];
			
			//random or most recent?
			$options['random'] = isset($_POST['ttc_coppermine_badge-random'] );
			
			//save user selections
			update_option('ttc_coppermine_badge', $options);
		}
		
		
		
		// This is the form where we collect the user preferences
		// Notice that we don't need a complete form. This will be embedded into the existing form.
		echo '<p style="text-align:right;"><label for="ttc_coppermine_badge-title">' . __('Title:') . ' <input style="width: 200px;" id="ttc_coppermine_badge-title" name="ttc_coppermine_badge-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ttc_coppermine_badge-thumbnails">' . __('Number of thumbnails (Max = 20):') . ' <input style="width: 20px;" id="ttc_coppermine_badge-thumbnails" name="ttc_coppermine_badge-thumbnails" type="text" value="'.$thumbnails.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ttc_coppermine_badge-random">' . __('Check for random images') . ' <input style="width: 20px;" id="ttc_coppermine_badge-random" name="ttc_coppermine_badge-random" type="checkbox" value="'.$random.'" /></label></p>';
		
		//Input our form info if user presses save button
		echo '<input type="hidden" id="ttc_coppermine_badge-submit" name="ttc_coppermine_badge-submit" value="1" />';
	}

	//register widget so it is available to user in widget page
	register_widget_control(array('TTC Coppermine Badge', 'widgets'), 'ttc_coppermine_badge_control', 300, 150);

	//register control panel so use can see it and use it
	register_sidebar_widget(array('TTC Coppermine Badge','widgets'), 'ttc_coppermine_badge');
}

// go to main
add_action('widgets_init', 'ttc_coppermine_badge_init');
?>
