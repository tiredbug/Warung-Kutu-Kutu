<?php
// begin array to create the new write areas
$new_meta_boxes =  
  array(  	  
	  "image" => array(
	  "name" => "image",  
	  "std" => "",  
	  "title" => "Product Image",  
	  "description" => "Enter full path to image here."
	  ),
	  "price" => array(
	  "name" => "price",  
	  "std" => "",  
	  "title" => "Product Price",  
	  "description" => "Only numbers and periods!  DO NOT ENTER \"$\"!"
	  ),
	  "size" => array(
	  "name" => "sizes",  
	  "std" => "",  
	  "title" => "Available Sizes",  
	  "description" => "Enter comma-separated values. (ex: \"Small, Medium, Large\")"
	  ),
	  "colors" => array(
	  "name" => "colors",  
	  "std" => "",  
	  "title" => "Available Colors",  
	  "description" => "Enter comma-separated values. (ex: \"Red, White, Blue\")"
	  ),
	  "catalog" => array(
	  "name" => "catalog",  
	  "std" => "",  
	  "title" => "Catalog Number",  
	  "description" => "Enter catalog number here."
	  ),
	  "cannotbuy" => array(
	  "name" => "cannotbuy",  
	  "std" => "",  
	  "title" => "Cannot Purchase Online",  
	  "description" => "If this is an item that is not allowed to be sold online, type in \"yes\" or \"y\"."
	  ),
	  "sale" => array(
	  "name" => "sale",  
	  "std" => "",  
	  "title" => "Sale Price",  
	  "description" => "Enter in a dollar amount or percentage off - be sure to include the \"$\" or \"%\" symbols."
	  ),
	  "quantity" => array(
	  "name" => "quantity",  
	  "std" => "",  
	  "title" => "Quantity in Stock",  
	  "description" => "Enter in a value for how many items you have in stock."
	  )
  );

// build HTML input fields from the arrays above
function new_meta_boxes() {  
  global $post, $new_meta_boxes;  
  
  foreach($new_meta_boxes as $meta_box) {  
	$meta_box_value = get_post_meta($post->ID, $meta_box['name'] . '_value', true);  
  
	if($meta_box_value == "")  
	$meta_box_value = $meta_box['std'];  
  
	echo '<input type="hidden" name="' . $meta_box['name'] . '_noncename" id="' . $meta_box['name'] .'_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';  
 	echo '<p style="font-size:1.1em; color:#999; margin-bottom:0; padding-bottom:0;">' . $meta_box['title'] . '</p>';     
	echo '<input type="text" name="' . $meta_box['name'] . '_value" value="' . $meta_box_value . '" /><br />';    
	echo '<p style="font-style:italic; color:#999; margin-top:0; padding-top:0;"><label for="' . $meta_box['name'] . '_value">' . $meta_box['description'] . '</label></p>';  
  }  
}

// create the meta boxes
function create_meta_box() {  
  global $theme_name;  
  if ( function_exists('add_meta_box') ) {  
	add_meta_box( 'new-meta-boxes', 'Extra Post Information', 'new_meta_boxes', 'post', 'normal', 'high' );  

  }  
}

// save the data entered 
function save_postdata( $post_id ) {  
  global $post, $new_meta_boxes;  

  foreach($new_meta_boxes as $meta_box) {  
    // Verify  
  	if ( !wp_verify_nonce( $_POST[$meta_box['name'] . '_noncename'], plugin_basename(__FILE__) )) {  
	  return $post_id;  
	}  

  	if ( 'page' == $_POST['post_type'] ) {  
	  if ( !current_user_can( 'edit_page', $post_id ))  
	  return $post_id;  
	} else {  
   	  if ( !current_user_can( 'edit_post', $post_id ))  
	  return $post_id;  
	}  

 	$data = $_POST[$meta_box['name'] . '_value'];  

	if(get_post_meta($post_id, $meta_box['name'] . '_value') == "")  
	  add_post_meta($post_id, $meta_box['name'] . '_value', $data, true);  
	elseif($data != get_post_meta($post_id, $meta_box['name'] . '_value', true))  
	  update_post_meta($post_id, $meta_box['name'] . '_value', $data);  
	elseif($data == "")  
	  delete_post_meta($post_id, $meta_box['name'] . '_value', get_post_meta($post_id, $meta_box['name'] . '_value', true));  
  }  
}

add_action('admin_menu', 'create_meta_box');  
add_action('save_post', 'save_postdata');

/*---------------------------------------------------------------------------------------
							 Custom Content sections
---------------------------------------------------------------------------------------*/

function product_display($type) {
	global $post;
	$malsURL = 'MALS ADD URL HERE';
	$malsviewURL = 'REVIEW URL HERE';
	$malsID = 'YOUR ID HERE';
	$returnURL = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$title = get_the_title();
	$catalog = get_post_meta($post->ID, 'catalog_value', true);
	$image = get_post_meta($post->ID, 'image_value', true);
	$price = get_post_meta($post->ID, 'price_value', true);
	$sizes = get_post_meta($post->ID, 'sizes_value', true);
	$colors = get_post_meta($post->ID, 'colors_value', true);
	$inperson = get_post_meta($post->ID, 'cannotbuy_value', true);
	$sale = get_post_meta($post->ID, 'sale_value', true);	
	$stock = get_post_meta($post->ID, 'quantity_value', true);
	
	// set values for price - if dollar amount off or percentage off for sale pricing
	if($sale != '') {
		if(stristr($sale,'%')) {
		  $percentage = str_replace('%','',$sale);
		  $percentprice = round($price - ($price * ($percentage/100)),2);
		  $saletag = $percentage . "% off!";
		  $sale = '<input type="hidden" name="price" value="' . $percentprice . '">';
		} else if(stristr($sale,'$')) {
		  $dollaroff = str_replace('$','',$sale);
		  $dollarprice = round($price - $dollaroff,2);	
		  $saletag = "$" . $dollaroff . " off!";	  
		  $sale = '<input type="hidden" name="price" value="' . $dollarprice . '">';
		}
	}
	
	// let's check the stock levels
	if($stock != '') {
		if($stock == '0') {
			$stocklevel = 'We\'re sorry.  We are currently Out of Stock on this product.  If you\'d like to place a back-order, or for more information, please call us at xxx-xxx-xxxx.';
		} else if($stock <= '5' && $stock > '0' ) {
			$stocklevel = 'Only '. $stock . ' of these items are left in stock. Grab \'em before they\'re gone!';
		}
	}
	
	// create option values if sizes and/or colors are set
	if(($sizes != "") && ($colors == "")) {
	  $optionValues .= '<select name="product[]">' . "\n";
	  $getallsizes = explode(", ",$sizes);
	  $count = count($getallsizes);
	  for($i=0;$i<$count;$i++) {
		$optionValues .= '<option value="' . $title . ' ' . $getallsizes[$i] . '">' . $title . ' ' . $getallsizes[$i] . '</option>' . "\n";
	  }	  
	  $optionValues .= '</select>' . "\n\n";
	}
	
	if(($colors != "") && ($sizes == "")) {
	  $optionValues .= '<select name="product1[]">' . "\n";
	  $getallcolors = explode(", ",$colors);
	  $count = count($getallcolors);
	  for($i=0;$i<$count;$i++) {
		$optionValues .= '<option value="' . $title . ' ' . $getallcolors[$i] . '">' . $title . ' ' . $getallcolors[$i] . '</option>' . "\n";
	  }	  
	  $optionValues .= '</select>' . "\n\n";
	}
	
	if(($colors != "") && ($sizes != "")) {
	  $optionValues .= '<select name="product1[]">' . "\n";
	  $getallcolors = explode(", ",$colors);
	  $getallsizes = explode(", ",$sizes);	  
	  $countsizes = count($getallsizes);
	  $countcolors = count($getallcolors);
	  $total = ($countcolors*$countsizes);

      if($countsizes>$countcolors) {
	    for($i=0;$i<$countcolors;$i++) {
	      foreach($getallsizes as $gs) {
	        $optionValues .= '<option value="' . $title . ' ' . $getallcolors[$i] . ' ' . $gs . '">' . $title . ' ' . $getallcolors[$i] . ' ' . $gs . '</option>' . "\n";
	      }
	    }
      } else if($countsizes<$countcolors) {
	    for($i=0;$i<$countsizes;$i++) {
	      foreach($getallcolors as $gc) {
	        $optionValues .= '<option value="' . $title . ' ' . $gc . ' ' . $getallsizes[$i] . '">' . $title . ' ' . $gc . ' ' . $getallsizes[$i] . '</option>' . "\n";
	      }
	    }
      }

	  $optionValues .= '</select>' . "\n\n";
	}
	
	
	if($type == 'full') { ?>
	<small>Catalog ID: <?php echo $catalog; ?></small>
	<h3>$<?php echo $price; ?></h3>
	<?php if($sale != '') { ?>
	  	  <p class="alert"><?php echo $saletag; ?></p>
	<?php } 
	      if($stock != '') { ?>
		  <p class="alert stock"><?php echo $stocklevel; ?></p>
	<?php } ?>
	<?php if($inperson != "") { ?>
	<p class="cantbuy">In-store purchase only.  For questions call xxx-xxx-xxxx.</p>
	<?php } ?>
	<div class="image">
	  <img src="<?php echo $image; ?>" alt="" />
	</div>
	<?php the_content(); ?>
	<hr class="clear" />
	<div class="buttons">
	<form id="buy_now" method="post" action="<?php echo $malsURL; ?>"> 
	  <input type="hidden" name="userid" value="<?php echo $malsID; ?>"> 
	  <input type="hidden" name="product" value="<?php echo $title; ?>">
	  <?php echo $optionValues; ?>
	  <input type="hidden" name="return" value="<?php echo $returnURL; ?>">
	  <?php if($sale != '') { 
	  	      echo $sale;
	  	    } else { ?>
	  <input type="hidden" name="price" value="<?php echo $price; ?>">
	  <?php } ?>
	  <input type="image" class="button" src="<?php bloginfo('template_directory') ?>/images/buy-now<?php if($stock == '0' || $inperson != '') { echo '-disabled'; }?>.gif" value="BUY NOW!"<?php if($stock == '0' || $inperson != '') { echo ' disabled'; }?>> 
	</form>
	
	<form id="view_cart" method="post" action="<?php echo $malsviewURL; ?>">
	  <input type="hidden" name="userid" value="<?php echo $malsID; ?>">
	  <input type="hidden" name="return" value="<?php echo $returnURL; ?>">
	  <input type="image" class="button" src="<?php bloginfo('template_directory') ?>/images/view-cart.gif" value="View Cart">
	</form>
	</div>
<?php } else if($type == 'summary') { ?>
    <p><a href="<?php echo $image; ?>"><img src="<?php echo $image; ?>" class="thumbnail" title="Click to View Larger Image" alt="" /></a></p>
    <p>$<?php echo $price; ?></p>
		
<?php }
}

?>
