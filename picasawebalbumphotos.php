<?php
/**
 * @package picasawebalbumphotos
 * @version 1.0.0
 */
/*
Plugin Name: Picasa Web Album Photos
Plugin URL: http://someurl.me/blog/index.php/2011/01/20/wordpress-picasaweb-album-photos-gallery/
Version: 1.0.0
Description: To show your picasa album images on widget area in gallery mode with different effects. Admin can configure gallery with different options. Download to checkout more
Author: Someuser
Author URI: http://someurl.me/blog
*/

define("picasawebalbumphotos_TITLE","Someuser's");
define("picasawebalbumphotos_DEFAULTPROFILE","someuser");

function picasawebalbumphotos_widget_Init(){
  register_widget('picasawebalbumphotosWidget');
}
	
add_action("widgets_init", "picasawebalbumphotos_widget_Init");

class picasawebalbumphotosWidget extends WP_Widget {
     function picasawebalbumphotosWidget() {
       //Widget code
	   parent::WP_Widget(false,$name="Picasa Web Album Photos");
     }

     function widget($args, $instance) {
       //Widget output
	   
	    $options = $instance;
		$output = '
		<style type="text/css">
		.slideshow { height: '.$instance['picasawebalbumphotos_widget_pcsizeh'].'px; width: '.$instance['picasawebalbumphotos_widget_pcsizew'].'px; margin: auto }
		.slideshow img { padding: 15px; border: 1px solid #ccc; background-color: #eee; }
		</style>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js"></script>
		<script type="text/javascript" src="http://cloud.github.com/downloads/malsup/cycle/jquery.cycle.all.2.74.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
			$(".slideshow").cycle({
				fx: "'.$instance['picasawebalbumphotos_widget_galleryview'].'" // choose your transition type, ex: fade, scrollUp, shuffle, etc...
			});
		});
		</script>
		';
		$output.= '<b>'.$options['picasawebalbumphotos_widget_title'].':</b>';	
		
		
		$output.='<div class="slideshow">';
		$albumid = $instance['picasawebalbumphotos_widget_alb'];
		$userid = $instance['picasawebalbumphotos_widget_profile'];
		
		$albumfeedURL = "http://picasaweb.google.com/data/feed/api/user/$userid/albumid/$albumid";
		$xml_album = simplexml_load_file($albumfeedURL);
		$cnt=0;
		foreach( $xml_album->entry as $entry ){
			$cnt++;
			if($cnt<=$options['picasawebalbumphotos_widget_noofphotos']){
				$media = $entry->children('http://search.yahoo.com/mrss/');
				$thumbnail = $media->group->thumbnail[1];
				$thumbtouse = $thumbnail->attributes()->{'url'};
				
				$output.='<img src="'.$thumbtouse.'" width="'.($instance['picasawebalbumphotos_widget_pcsizew']-15).'" height="'.($instance['picasawebalbumphotos_widget_pcsizeh']-15).'" />';
			}
		}
		
			
		$output.='</div></span>';
		
		extract($args);	
		echo $before_widget; 
		echo $before_title . $title . $after_title;
		echo $output; 
		echo $after_widget;
     }

     function update($new_instance, $old_instance) {
       //Save widget options
		$instance = $old_instance;
		foreach($new_instance as $k=>$v){
			$instance[$k] = $new_instance[$k];
		}
		return $instance;
     }

     function form($instance) {
       //Output admin widget options form
		$instance = wp_parse_args( (array) $instance, array(
		'picasawebalbumphotos_widget_title'=>picasawebalbumphotos_TITLE,
		'picasawebalbumphotos_widget_profile'=>picasawebalbumphotos_DEFAULTPROFILE,
		'picasawebalbumphotos_widget_albums'=>'',
		'picasawebalbumphotos_widget_pcsizew'=>'200',
		'picasawebalbumphotos_widget_pcsizeh'=>'200',
		'picasawebalbumphotos_widget_noofphotos'=>'5'
		) );
		
	   $userid = picasawebalbumphotos_DEFAULTPROFILE."%40googlemail.com";    // picasa_user_id@gmail.com
	   $feedURL = "http://picasaweb.google.com/data/feed/api/user/$userid?kind=album";
	   $picasaURL = "http://picasaweb.google.com/$user/";
	   $picasaAlbumCache = dirname(__FILE__) . '/cache';
		
	   $xml = simplexml_load_file($feedURL);
		//echo picasawebalbumphotos_TITLE;
	   ?>
<script type="text/javascript">
function getAlbums(val){
	jQuery('#<?php echo $this->get_field_id('profile_id');?>').html("gettting albums..");
	jQuery('#<?php echo $this->get_field_id('profile_id');?>').show();
	jQuery.post("<?php echo get_option('siteurl');?>/wp-content/plugins/picasawebalbumphotos/getalbums.php", { profile:val, fname:'<?php echo  $this->get_field_name('picasawebalbumphotos_widget_alb');?>', fid:'<?php echo  $this->get_field_id('picasawebalbumphotos_widget_alb');?>'},
		function(data){
			jQuery('#<?php echo $this->get_field_id('profile_id');?>').html(data);
		}
	);
}
</script>  
    <p><label for="picasawebalbumphotos_widget_title"><?php _e('Album Title:'); ?> <input id="<?php echo  $this->get_field_id('picasawebalbumphotos_widget_title');?>" name="<?php echo  $this->get_field_name('picasawebalbumphotos_widget_title');?>" type="text" value="<?php echo $instance['picasawebalbumphotos_widget_title']; ?>" /></label></p>

    <p><label for="picasawebalbumphotos_widget_profile"><?php _e('Profile:'); ?> <input id="<?php echo  $this->get_field_id('picasawebalbumphotos_widget_profile');?>" name="<?php echo  $this->get_field_name('picasawebalbumphotos_widget_profile');?>" type="text" value="<?php echo $instance['picasawebalbumphotos_widget_profile']; ?>" onchange="return getAlbums(this.value);" /></label></p>

	<p><label for="picasawebalbumphotos_widget_alb"><?php _e('Select Albums:'); ?> 
    <span id="<?php echo $this->get_field_id('profile_id');?>">
    <select name="<?php echo  $this->get_field_name('picasawebalbumphotos_widget_alb');?>" id="<?php echo  $this->get_field_id('picasawebalbumphotos_widget_alb');?>">
    <?php foreach( $xml->entry as $entry ){
		$gphoto = $entry->children('http://schemas.google.com/photos/2007');
		$albumid = $gphoto->id
	?>
    <option value="<?php echo $albumid;?>" <?php if($albumid==$instance['picasawebalbumphotos_widget_alb']){echo "selected";}?>><?php echo $entry->title;?></option>
    <?php }?>
    </select></span>
    </label></p>
     
     <p><label for="picasawebalbumphotos_widget_galleryview"><?php _e('Gallery View:'); ?> 
     <select name="<?php echo  $this->get_field_name('picasawebalbumphotos_widget_galleryview');?>" id="<?php echo  $this->get_field_id('picasawebalbumphotos_widget_galleryview');?>">
     <option value="shuffle" <?php if($instance['picasawebalbumphotos_widget_galleryview']=='shuffle'){echo "selected";}?>>Shuffle</option>
     <option value="zoom" <?php if($instance['picasawebalbumphotos_widget_galleryview']=='zoom'){echo "selected";}?>>Zoom</option>
     <option value="fade" <?php if($instance['picasawebalbumphotos_widget_galleryview']=='fade'){echo "selected";}?>>Fade</option>
     <option value="turnDown" <?php if($instance['picasawebalbumphotos_widget_galleryview']=='turnDown'){echo "selected";}?>>Turn Down</option>
     <option value="curtainX" <?php if($instance['picasawebalbumphotos_widget_galleryview']=='curtainX'){echo "selected";}?>>CurtainX</option>
     <option value="scrollRight" <?php if($instance['picasawebalbumphotos_widget_galleryview']=='scrollRight'){echo "selected";}?>>ScrollRight(click)</option>
    
     </select>
     </label></p>
    
    <p><label for="placeyoutubevideo_widget_pcsizewh"><?php _e('Gallery Size:'); ?> <input  id="<?php echo  $this->get_field_id('picasawebalbumphotos_widget_pcsizew');?>" name="<?php echo  $this->get_field_name('picasawebalbumphotos_widget_pcsizew');?>" type="text" value="<?php echo $instance['picasawebalbumphotos_widget_pcsizew']; ?>"  style="width:50px;"/> X <input  id="<?php echo  $this->get_field_id('picasawebalbumphotos_widget_pcsizeh');?>" name="<?php echo  $this->get_field_name('picasawebalbumphotos_widget_pcsizeh');?>" type="text" value="<?php echo $instance['picasawebalbumphotos_widget_pcsizeh']; ?>" style="width:50px;"/></label></p>
    
    <p><label for="picasawebalbumphotos_widget_noofphotos"><?php _e('No of Photos:'); ?> <input id="<?php echo  $this->get_field_id('picasawebalbumphotos_widget_noofphotos');?>" name="<?php echo  $this->get_field_name('picasawebalbumphotos_widget_noofphotos');?>" type="text" value="<?php echo $instance['picasawebalbumphotos_widget_noofphotos']; ?>" /></label></p>
    
	   <?php
     }
	
}

?>
