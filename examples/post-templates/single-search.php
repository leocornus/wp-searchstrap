<?php
/**
 * WP Post Template: Search Post Template
 *
 * TODO: This is a example post templte.
 * Developers will need do some minor tweak for the layout to
 * fit in the theme.
 */

function coic_search_scripts() {
    wp_enqueue_script('bootstrap-js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');
    wp_enqueue_style('bootstrap-css', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
}
add_action('wp_enqueue_scripts', 'coic_search_scripts');

get_header();?>

        <div id="primary" class="content-area">
                <main id="main" class="site-main" role="main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
the_content();
endwhile; else: ?>
<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>

                </main><!-- #main -->
        </div><!-- #primary -->

<?php radiate_sidebar_select(); ?>
<?php get_footer();?>

