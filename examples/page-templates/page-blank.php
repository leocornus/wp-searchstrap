<?php
/**
 * Template Name: Blank Page Template
 *
 * this is a simple page template only load the header, content and footer
 * for a page. 
 * The content will present the result of the shortcode.
 */

function coic_search_scripts() {
    wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
    wp_enqueue_style('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
}
add_action('wp_enqueue_scripts', 'coic_search_scripts');

get_header();?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
the_content();
endwhile; else: ?>
<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>

<?php get_footer();?>
