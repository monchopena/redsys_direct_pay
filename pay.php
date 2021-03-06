<?php
/*
Plugin Name: Redsys Pago Directo
Description: ¡Pago directo y sencillo con RedSys!
Version: 1.0
Author: Roi Facal y Moncho Pena
Author URI: https://codigo.co.uk
*/

/*
	
	TODO
	Nota: donde ponga "TODO", antes de subirlo hay que arreglarlo (¡No olvidarse de borrar todos estos comentarios!)
	
*/

/*
	Translations
*/

add_action('plugins_loaded', 'wan_load_textdomain');
function wan_load_textdomain() {
	load_plugin_textdomain( 'direct_pay', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

function custom_entries() {
  register_post_type( 'payment',
    array(
      'labels' => array(
        'name'                => __( 'Pays', 'direct_pay'),
        'singular_name'       => __( 'Pay', 'direct_pay' ),
        'menu_name'           => __( 'Pays', 'direct_pay' ),
        'all_items'           => __( 'All pays', 'direct_pay' ),
        'view_item'           => __( 'View pay', 'direct_pay' ),
        'add_new_item'        => __( 'Add new pay', 'direct_pay' ),
        'add_new'             => __( 'New Pay', 'direct_pay' ),
        'edit_item'           => __( 'Edit pay', 'direct_pay' ),
        'update_item'         => __( 'Update Pay', 'direct_pay' ),
        'search_items'        => __( 'search Pay', 'direct_pay' ),
        'not_found'           => __( 'Not found pays', 'direct_pay' ),
        'not_found_in_trash'  => __( 'No payments in bin', 'direct_pay' )
      ),
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'rewrite'         => array( 'slug' => 'pagos' ),
    'query_var' => true,
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title','editor','author','subscriber', 'thumbnail','excerpt','comments')
    
    )
  );
}

add_action( 'init', 'custom_entries' );

/*
	SHOW in Backend
*/

add_filter( 'manage_edit-payment_columns', 'my_edit_payment_columns' ) ;

function my_edit_payment_columns( $columns ) {

	$columns = array(
		'cb'=> __( 'Select', 'direct_pay' ),
		'title' => __( 'Title', 'direct_pay' ),
		'email' => __( 'Email', 'direct_pay' ),
		'import' => __( 'Amount', 'direct_pay' ),
        'author' =>  __( 'Author', 'direct_pay' ),
        'signature' =>  __( 'Signature', 'direct_pay' ),
 		'date' => __( 'Date', 'direct_pay' )
	);

	return $columns;
}

add_action( 'manage_payment_posts_custom_column', 'my_manage_payment_columns', 10, 2 );

function my_manage_payment_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		case 'email' :

			/* Get the post meta. */
			$email = get_post_meta( $post_id, 'direct_pay_email', true );

			/* If no duration is found, output a default message. */
			if ( empty( $email ) )
				echo __( 'Unknown', 'direct_pay' );

			/* If there is a duration, append 'minutes' to the text string. */
			else
				echo $email;

			break;

		case 'import' :

			/* Get the post meta. */
			
			$import = get_post_meta( $post_id, 'direct_pay_import', true );

			/* If no duration is found, output a default message. */
			if ( empty( $import) )
				echo __( 'Unknown', 'direct_pay' );

			/* If there is a duration, append 'minutes' to the text string. */
			else
				echo $import;

			break;
			
		case 'signature':
		
			/* Get the post meta. */
			$signature = get_post_meta( $post_id, 'direct_pay_signature', true );

			/* If no duration is found, output a default message. */
			if ( empty( $signature ) )
				echo __( 'Unknown', 'direct_pay' );

			/* If there is a duration, append 'minutes' to the text string. */
			else
				echo $signature;

			break;
		

		case 'author' :

			/* Get the post meta. */
		
			/* If no duration is found, output a default message. */
			if ( empty( $post->post_author ) )
				echo __( 'Unknown', 'direct_pay' );

			/* If there is a duration, append 'minutes' to the text string. */
			else
				$user_id=$post->post_author;
				echo get_the_author_meta( 'display_name', $user_id );
			break;
		
		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

/*
	Adding JS
*/

function my_scripts_method() {
    wp_enqueue_script( 'jquery_validate', plugins_url( '/js/jquery.validate.min.js' , __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'direct_pay', plugins_url( '/js/direct_pay.js' , __FILE__ ), array( 'jquery') );
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );

/*
		
*/

function build_direct_pay_page() {
		
	   ob_start();

	   $version="HMAC_SHA256_V1";
	   
	   $options = get_option( 'direct_pay_settings' );

	   
	   if ($options['direct_pay_select_environment']==2) {
		   $tpvurl="https://sis-t.redsys.es:25443/sis/realizarPago";
	   } else {
		   $tpvurl="https://sis.redsys.es/sis/realizarPago";
	   }
	   //
	   
       $descripcion_producto_tpv='Payment';
       $texto_submit= 'Payment';
       echo '<div class="invitados_texto_form"><p>Pagar</p></div><br>
'; 
 		/*
	 		BEGIN shortcode direct_pay_page
	 	*/
	 	
	 	 $url = plugins_url() . '/direct_pay/pay_ajax.php';
	 	 
	 	 $test_mode=0;
	 	 
	 	 $post_name='';
	 	 $submitters_email='';
	 	 $comentarios='';
	 	 $Ds_Merchant_Amount='';
	 	 
	 	 if ($test_mode==1) {
		 	 $post_name='Xoan Rodriguez';
		 	 $submitters_email='xoan@rodriguez.com';
		 	 $comentarios='No comments';
		 	 $Ds_Merchant_Amount=221;
	 	 }
	 	 
	 	 
	?>  
      
		<form id="formulariopago" name="frm" action="<?php echo $tpvurl; ?>" method="POST">

			<input class="required" type="text" id="thepost_name" name="post_name" size="75" value="<?php echo strip_tags(stripslashes($post_name)) ?>" placeholder="Name" /></br>
			<input class="required" type="text" id="thesubmitters_email" name="submitters_email" size="75" value="<?php echo strip_tags(stripslashes($submitters_email)) ?>" placeholder="Email" /></br>
			
			<textarea class="required" id="comentario" name="comentarios"rows="4" cols="50" placeholder="Coments"><?php echo strip_tags(stripslashes($comentarios)) ?></textarea></br>
			<input type="hidden" name="Ds_SignatureVersion" value="<?php echo $version; ?>"/>
			<input type="hidden" id="Ds_MerchantParameters" name="Ds_MerchantParameters" value=""/>
			<input type="hidden" id="Ds_Signature" name="Ds_Signature" value=""/>
			<input class="required" type="text" id="Ds_Merchant_Amount" name="Ds_Merchant_Amount" value="<?php echo strip_tags(stripslashes($Ds_Merchant_Amount)) ?>" placeholder="Import" /></br>

			<button style="float:left;" class="btn btn-primary" type="button" onclick="javascript:doFormFinal('<?echo $url ?>')" /> Send</button>

		</form>

	
	<?php	
		
		$out = ob_get_clean();
		return $out;

		
}		

add_shortcode('direct_pay_page', 'build_direct_pay_page');

function build_direct_pay_page_ko() {

 		/*
	 		BEGIN shortcode direct_pay_page_ko
	 	*/
	 	
	 	ob_start();
	 	
	 	$options = get_option( 'direct_pay_settings' );
	 	
	 	require_once ('apiRedsys.php');
	 	
	 	$miObj = new RedsysAPI;

	    $kc = $options['direct_pay_text_key_sha_256c'];
		$params=$_GET['Ds_MerchantParameters'];
		$new_signature = $miObj->createMerchantSignatureNotif($kc, $params);	
		$signature=$_GET['Ds_Signature'];
		
		$test_signature=0;
		if ($new_signature == $signature) {
			$test_signature=1;
		}
		
		$myOrder=$miObj->getParameter('Ds_Order');
		$myOrder=$myOrder-1000;
		
		$post_pendiente = array(
		  'ID'           => $myOrder,
		  'post_type'=> 'payment',
		  'post_status'   => 'Draft',
		);
		$post_id = wp_update_post($post_pendiente);
	 	
	?>  

       <?php
	     if  ($test_signature == 0) {
	    ?>
	    	<p>¡Fallo en la firma!</p>
	   <?php
	     } 
	   ?>
      
	   <p>El Pago con número <?php echo $myOrder; ?> ha fallado por favor inténtelo otra vez</p>
	   <p><a href="<?php echo get_permalink($options['direct_pay_select_pay_page']); ?>">Volver</a></p>
	
	<?php	
		
		$out = ob_get_clean();
		return $out;
		
}		

add_shortcode('direct_pay_page_ko', 'build_direct_pay_page_ko');


function build_direct_pay_page_ok() {

 		/*
	 		BEGIN shortcode direct_pay_page_ko
	 	*/
	 	
	 	ob_start();
	 	
	 	$options = get_option( 'direct_pay_settings' );
	 	
	 	require_once ('apiRedsys.php');
	 	
	 	$miObj = new RedsysAPI;

	    $kc = $options['direct_pay_text_key_sha_256c'];
		$params=$_GET['Ds_MerchantParameters'];
		$new_signature = $miObj->createMerchantSignatureNotif($kc, $params);	
		$signature=$_GET['Ds_Signature'];
		
		$test_signature=0;
		if ($new_signature == $signature) {
			$test_signature=1;
		}
		
		$myOrder=$miObj->getParameter('Ds_Order');
		$myOrder=$myOrder-1000;
		
		$post_pendiente = array(
		  'ID'           => $myOrder,
		  'post_type'=> 'payment',
		  'post_status'   => 'Publish',
		);
		$post_id = wp_update_post($post_pendiente);
	 	
	?>  

       <?php
	     if  ($test_signature == 0) {
	    ?>
	    	<p>¡Fallo en la firma!</p>
	   <?php
	     } 
	   ?>
      
	   <p>El Pago con número <?php echo $myOrder; ?> ha sido realizado correctamente</p>

	
	<?php	
		
		$out = ob_get_clean();
		return $out;
		
}		

add_shortcode('direct_pay_page_ok', 'build_direct_pay_page_ok');


/*
	SETTINGS Backend
*/

add_action( 'admin_menu', 'direct_pay_add_admin_menu' );
add_action( 'admin_init', 'direct_pay_settings_init' );
function direct_pay_add_admin_menu(  ) { 
	add_menu_page( 'Redsys Pago Directo', 'Redsys Pago Directo', 'manage_options', 'redsys_direct', 'direct_pay_options_page' );
}
function direct_pay_settings_init(  ) { 
	register_setting( 'pluginPage', 'direct_pay_settings' );
	add_settings_section(
		'direct_pay_pluginPage_section', 
		__( 'Settings', 'direct_pay' ), 
		'direct_pay_settings_section_callback', 
		'pluginPage'
	);
	add_settings_field( 
		'direct_pay_select_environment', 
		__( 'Entorno', 'direct_pay' ), 
		'direct_pay_select_environment_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
	add_settings_field( 
		'direct_pay_text_key_sha_256c', 
		__( 'Key SHA-256C', 'direct_pay' ), 
		'direct_pay_text_key_sha_256c_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
	add_settings_field( 
		'direct_pay_text_commerce_fuc', 
		__( 'Cod Comercio FUC', 'direct_pay' ), 
		'direct_pay_text_commerce_fuc_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
	add_settings_field( 
		'direct_pay_text_terminal', 
		__( 'Terminal', 'direct_pay' ), 
		'direct_pay_text_terminal_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
	add_settings_field( 
		'direct_pay_text_commerce_name', 
		__( 'Commerce name', 'direct_pay' ), 
		'direct_pay_text_commerce_name_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
	add_settings_field( 
		'direct_pay_select_pay_ok', 
		__( 'OK Page', 'direct_pay' ), 
		'direct_pay_select_pay_ok_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
	add_settings_field( 
		'direct_pay_select_pay_ko', 
		__( 'KO Page', 'direct_pay' ), 
		'direct_pay_select_pay_ko_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
	add_settings_field( 
		'direct_pay_select_pay_page', 
		__( 'Pay Page', 'direct_pay' ), 
		'direct_pay_select_pay_page_render', 
		'pluginPage', 
		'direct_pay_pluginPage_section' 
	);
}
function direct_pay_select_environment_render(  ) { 
	$options = get_option( 'direct_pay_settings' );
	?>
	<select name='direct_pay_settings[direct_pay_select_environment]'>
		<option value='1' <?php selected( $options['direct_pay_select_environment'], 1 ); ?>>Real</option>
		<option value='2' <?php selected( $options['direct_pay_select_environment'], 2 ); ?>>Pruebas</option>
	</select>

<?php
}
function direct_pay_text_key_sha_256c_render(  ) { 
	$options = get_option( 'direct_pay_settings' );
	?>
	<input type='text' name='direct_pay_settings[direct_pay_text_key_sha_256c]' value='<?php echo $options['direct_pay_text_key_sha_256c']; ?>'>
	<?php
}
function direct_pay_text_commerce_fuc_render(  ) { 
	$options = get_option( 'direct_pay_settings' );
	?>
	<input type='text' name='direct_pay_settings[direct_pay_text_commerce_fuc]' value='<?php echo $options['direct_pay_text_commerce_fuc']; ?>'>
	<?php
}
function direct_pay_text_terminal_render(  ) { 
	$options = get_option( 'direct_pay_settings' );
	?>
	<input type='text' name='direct_pay_settings[direct_pay_text_terminal]' value='<?php echo $options['direct_pay_text_terminal']; ?>'>
	<?php
}
function direct_pay_text_commerce_name_render(  ) { 
	$options = get_option( 'direct_pay_settings' );
	?>
	<input type='text' name='direct_pay_settings[direct_pay_text_commerce_name]' value='<?php echo $options['direct_pay_text_commerce_name']; ?>'>
	<?php
}
function direct_pay_select_pay_ok_render(  ) { 
	$options = get_option( 'direct_pay_settings' );

	$temp_option_id=$options['direct_pay_select_pay_ok'];
	$temp_option_value='';
	
	if ($temp_option_id>0) {
		$temp_option_value=get_the_title($temp_option_id);
	}
	
?>

<?php

	
?>

<select name='direct_pay_settings[direct_pay_select_pay_ok]'> 
<option value="<?php echo $temp_option_id; ?>">

<?php

	if ($temp_option_value!='') {
		echo $temp_option_value;
	} else {
		echo esc_attr( __( 'Select page', 'direct_pay' ) );
	}

?>

</option> 
<?php 
	$pages = get_pages(); 
	foreach ( $pages as $page ) {
		$option = '<option value="' . $page->ID . '">';
		$option .= $page->post_title;
		$option .= '</option>';
	echo $option;
}
?>
</select>

<?php
}
function direct_pay_select_pay_ko_render(  ) { 
	$options = get_option( 'direct_pay_settings' );

	
	$temp_option_id=$options['direct_pay_select_pay_ko'];
	$temp_option_value='';
	if ($temp_option_id>0) {
		$temp_option_value=get_the_title($temp_option_id);
	}

?>

<select name='direct_pay_settings[direct_pay_select_pay_ko]'> 

	<option value="<?php echo $temp_option_id; ?>">
	
	<?php
	
	if ($temp_option_value!='') {
		echo $temp_option_value;
	} else {
		echo esc_attr( __( 'Select page', 'direct_pay' ) );
	}
	?>
	
	</option> 
<?php 
	$pages = get_pages(); 
	foreach ( $pages as $page ) {
		$option = '<option value="' .$page->ID . '">';
		$option .= $page->post_title;
		$option .= '</option>';
		echo $option;
	}
?>
</select>

<?php
}

function direct_pay_select_pay_page_render(  ) { 
	$options = get_option( 'direct_pay_settings' );
	
	
    $temp_option_id=$options['direct_pay_select_pay_page'];
	$temp_option_value='';
	if ($temp_option_id>0) {
		$temp_option_value=get_the_title($temp_option_id);
	}
	?>
	
<select name='direct_pay_settings[direct_pay_select_pay_page]'> 
	<option value="<?php echo $temp_option_id; ?>">
	
	<?php
	
	if ($temp_option_value!='') {
		echo $temp_option_value;
	} else {
		echo esc_attr( __( 'Select page', 'direct_pay' ) );
	}

	?>
	
	</option> 

<?php
$pages = get_pages();
	foreach ( $pages as $page ) {
		$option = '<option value="' . $page->ID . '">';
		$option .= $page->post_title;
		$option .= '</option>';
		echo $option;
	}
?>
</select>

<?php
}
function direct_pay_settings_section_callback(  ) { 
	echo __( 'Plugin de Pago Directo para Redsys', 'direct_pay' );
}
function direct_pay_options_page(  ) { 
	$options = get_option( 'direct_pay_settings' );
	$url_pay = get_permalink($options['direct_pay_select_pay_page']);
	
	?>
	<form action='options.php' method='post'>
		<h2>Pago Directo</h2>
		
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
		
	</form>
	
	<?php
		if ($url_pay!='') {	
	?>
		<p><a href="<?echo $url_pay;?>">Go to Pay page</a></p>
	
	<?php
		}	
	?>
	
	<?php
}


// Do Pages

register_activation_hook( __FILE__, 'insert_pages' );

function insert_pages(){
	
	$page_ok = array(
      'post_title'    => 'Pago OK',
      'post_content'  => '[direct_pay_page_ok]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
    );
    // Insert the post into the database
    $id_page_ok=wp_insert_post( $page_ok, '' );
    
    $page_ko = array(
      'post_title'    => 'Pago KO',
      'post_content'  => '[direct_pay_page_ko]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
    );
    $id_page_ko=wp_insert_post( $page_ko, '' );
    
    $direct_pay = array(
      'post_title'    => 'Pago directo',
      'post_content'  => '[direct_pay_page]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
    );
    $id_page_pay=wp_insert_post( $direct_pay, '' );  
    
    $array_of_options = array(
    'direct_pay_select_pay_ok' => $id_page_ok,
    'direct_pay_select_pay_ko' => $id_page_ko,
    'direct_pay_select_pay_page' => $id_page_pay
	);
	
	update_option( 'direct_pay_settings', $array_of_options );
	
}

?>
