<?php
/*
 * Template Name: respuesta de santander
 * 
 * Description: Plantilla en blanco para recibir la respuesta de santander.
 * Se recomienda colocar esta plantilla en /wp-content/themes/mitema donde mi tema es el nombre del tema activado
 * Crear una pagina respuesta con el siguiente "shortcode":
 * [santander_page_response]
 * Y seleccionar esta plantilla para procesar la respuesta del banco
 */
?>

<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'templates/content-no-title', get_post_format() ); ?>
	<?php endwhile; ?>
<?php else : ?>
	<?php get_template_part( 'templates/content', 'none' ); ?>
<?php endif; ?>


