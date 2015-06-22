<?php
// LAYOUT FOR THE SETTINGS/OPTIONS PAGE
?>

<style>
button {
 background: #8dc63f;
   background: -moz-linear-gradient(top,  #8dc63f 0%, #8dc63f 50%, #7fb239 51%, #7fb239 100%);
   background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#8dc63f), color-stop(50%,#8dc63f), color-stop(51%,#7fb239), color-stop(100%,#7fb239));
   background: -webkit-linear-gradient(top,  #8dc63f 0%,#8dc63f 50%,#7fb239 51%,#7fb239 100%);
   background: -o-linear-gradient(top,  #8dc63f 0%,#8dc63f 50%,#7fb239 51%,#7fb239 100%);
   background: -ms-linear-gradient(top,  #8dc63f 0%,#8dc63f 50%,#7fb239 51%,#7fb239 100%);
   background: linear-gradient(top,  #8dc63f 0%,#8dc63f 50%,#7fb239 51%,#7fb239 100%);
   margin: auto;
   cursor:pointer;
   color: #fff;
   text-shadow: 1px 0px 0 rgba(0,0,0,.4);
   border-radius: 5px;
   border: none;
   font-family: cabin,sans-serif;
   display: block;
   font-weight: bold;
   padding: 5px 15px;
}
.inf{
	font-weight:bold;
	font-size:15px;
}
</style>

<div class="wrap">
    <?php screen_icon(); ?>
    <form action="options.php" method="post" id=<?php echo $this->plugin_id; ?>"_options_form" name=<?php echo $this->plugin_id; ?>"_options_form">
    <?php settings_fields($this->plugin_id.'_options'); ?>
    <h2>Owned it &raquo; Settings</h2>
	<p class="inf"> Please enter your store ID, a store ID is generated for each store you add to Owned it and this can be found on Owned it Dashboard -> Account Settings -> Store Settings Tab.
		</p>
    <table width="550" border="0" cellpadding="5" cellspacing="5" style="margin-top:25px;margin-left:50px;"> 
	<tr>
        <td id="key-holder" width="366" style="padding:5px;"><input placeholder="Please enter your store ID" id="storeid" name="<?php echo $this->plugin_id; ?>[storeid]" type="text" value="<?php echo $options['storeid']; ?>" style="margin:0px;width:100%;" /></td>
		 <td > <input type="submit" name="submit" value="Save" class="button-primary"/></td>
    </tr>
    </table>
    </form>
</div>
