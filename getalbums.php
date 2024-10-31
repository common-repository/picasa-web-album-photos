<?php 

if(isset($_POST['profile'])){
	$profile = $_POST['profile'];
	$userid = $profile."%40googlemail.com";    // picasa_user_id@gmail.com
	$feedURL = "http://picasaweb.google.com/data/feed/api/user/$userid?kind=album";
	$xml = simplexml_load_file($feedURL);
	?>
	<select name="<?php echo  $_POST['fname'];?>" id="<?php echo $_POST['fid'];?>">
    <?php foreach( $xml->entry as $entry ){
		$gphoto = $entry->children('http://schemas.google.com/photos/2007');
		$albumid = $gphoto->id
	?>
    <option value="<?php echo $albumid;?>" <?php if($albumid==$instance['picasawebalbumphotos_widget_alb']){echo "selected";}?>><?php echo $entry->title;?></option>
    <?php }?>
    </select>
	<?php
}
?>