<?php

/*
   Plugin Name: NiceCommerce
   Plugin URI: http://my-awesomeness-emporium.com
   Description: a E-commerce plugin to create a awesome store.
   Version: 1.0
   Author: Wellington Lorindo
   Author URI: http://lorindo.com
   License: GPL2
*/
  
// Add the custom icon 
function add_menu_icons_styles()
{
?> 
<style>
#adminmenu .menu-icon-events div.wp-menu-image:before {
  content: "\f118";
}
</style> 
<?php
}
add_action( 'admin_head', 'add_menu_icons_styles' );

  
// Add Post Thumbnail Theme Support
if (function_exists( 'add_theme_support')) {
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 200, 300, true );
	add_image_size( 'thumb_1', 80, 80, true );
	add_image_size( 'thumb_2', 200, 200, true );		
} 


// Cria a área de CLIENTES
// --- Registrando nova área
add_action('init', 'products_register');

function products_register() {

	$labels = array(
		'name' => __('NiceCommerce'),
		'singular_name' => __('Product'),
		'add_new' => __('New product'),
		'add_new_item' => __('Add product'),
		'edit_item' => __('Edit product'),
		'new_item' => __('New product'),
		'view_item' => __('View product'),
		'search_items' => __('Search product'),
		'not_found' =>  __('Not found'),
		'not_found_in_trash' => __('Not found'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 5,
		'menu_icon' => 'dashicons-admin-generic',
		'supports' => array('title','thumbnail', 'editor')
	  ); 

	register_post_type( 'products' , $args );	
	
}

add_filter('gettext','custom_enter_title');

function custom_enter_title($input) {

    global $post_type;

    if( is_admin() && ('Enter title here' == $input || 'Digite o título aqui' == $input) && 'products' == $post_type )
        return "Product's Name";

    return $input;
}

// --- Registrando taxonomia Tipos para PONTOS DE VENDA e PARCERIAS
function build_taxonomies() {
	/*$labels = array(
		'name' => __( 'Tipos'),
		'singular_name' => __( 'Tipo'),
		'search_items' =>  __( 'Buscar' ),
		'popular_items' => __( 'Mais usados' ),
		'all_items' => __( 'Todos os Tipos' ),
		'parent_item' => null,
		'parent_item_colon' => null,
		'edit_item' => __( 'Adicionar novo' ),
		'update_item' => __( 'Atualizar' ),
		'add_new_item' => __( 'Adicionar novo' ),
		'new_item_name' => __( 'New' )
		); */
	register_taxonomy('prod-category', array('products'),
		array(
			'hierarchical' => true,
			//'labels' => $labels,
			//'singular_label' => 'Tipo',
			//'all_items' => 'Todos os prod-category',
			'query_var' => true,
			'rewrite' => array( 'slug' => 'prod-category' ))
		);
}
add_action( 'admin_init', 'build_taxonomies', 0 );



/** ADICIONANDO OS METABOXES (campos personalizados)**/
add_action('admin_init','metaboxes'); 
function metaboxes(){ 
    add_meta_box( 'produtos_txts', 'Options' , 'products_call', 'products', 'normal', 'low' ); 
}  


function SalvaDeletaGuarda($id,$posts,$campos){
	$post = $posts;
	$campo = $campos;
	$pID = $id;
	if(isset($campo)){

		if(get_post_meta($pID,$post) == ""){
			add_post_meta($pID,$post,$campo);
		}elseif($campo != get_post_meta($pID,$post)){
			update_post_meta($pID, $post, $campo);
		}else{
			delete_post_meta($pID,$post,get_post_meta($post->ID,$post),true);
		}
	}

	return true;
}

/** Adicionando Campos personalizados ao HORTALIÇAS, LEGUMES, FRUTAS E OUTROS**/
function products_call(){
	global $post;
	$campos = get_post_custom($post->ID);
	$price = !empty($campos["price"][0]) ? $campos["price"][0] : "";
	$size = !empty($campos["size"][0]) ? $campos["size"][0] : "";
	$percentage = !empty($campos["percentage"][0]) ? $campos["percentage"][0] : "";
	
?>
<style type="text/css">
	
	.data_col {
		height: 85px
	}

	.form-field {
		display: inline;
		width: 30%;
		float: left;
		margin-right: 25px;
	}

	.form-field label {
		display: block;
	}

</style>
<div class="data_col">
	<p class="form-field">
		<label for="price">Price ($)</label>
		<input type="text" name="price" id="price" value="<?php echo $price;?>" placeholder=""> 
	</p>

	<p class="form-field">
		<label for="size">Size (m)</label>
		<input type="number" name="size" id="size" value="<?php echo $size;?>" placeholder=""> 
	</p>
	<p class="form-field">
		<label for="percentage">Percentage (%)</label>
		<input type="text" name="percentage" id="percentage" value="<?php echo $percentage;?>" placeholder=""> 
	</p>
</div>
<?php
	
}

add_action('save_post', 'salva_prods');

function salva_prods(){
	global $post;
	if (!empty($post)) {
		SalvaDeletaGuarda($post->ID, "price", !empty($_POST["price"]) ? $_POST["price"] : '');
		SalvaDeletaGuarda($post->ID, "size", !empty($_POST["size"]) ? $_POST["size"] : '');
		SalvaDeletaGuarda($post->ID, "percentage", !empty($_POST["percentage"]) ? $_POST["percentage"] : '');
	}
}

