<?php
/**
 * Template Name: Empty Page Template
 */
?>
<html>
<head>
  <!-- load WordPress core-->
  <?php wp_head();?>
</head>

<body>
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron" style="padding-bottom:0px">
      <div class="container">
        <h1>
          <span class="text-success">Acronyms</span>
        </h1>
        <p>The following is a partial list of acronyms used throughout the organization. This list is incomplete, and you can help!
        </p>
<ul class="nav nav-pills">
  <li role="presentation" class="active"><a href="#">Search</a></li>
  <li role="presentation"><a href="/wiki/">Search Tips</a></li>
  <li role="presentation"><a href="/wiki/Category:Acronyms">Create New</a></li>
</ul>
      </div>
    </div>

    <div class="container">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
the_content();
endwhile; else: ?>
<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>

    </div>

  <!-- load WordPress admin bar -->
  <?php
  // footer links for copyright, accessibility, terms of use
  $footerLinks = xxx_get_footer_links();
  wp_footer();
  ?>
  <div class="container container-fluid">
  <hr/>
  <footer>
    <a href="mailto:egovernment@ontario.ca">Contact Us</a> |
    <a href="<?php echo $footerLinks['accessibility']['url']; ?>"><?php echo $footerLinks['accessibility']['label']; ?></a> |
    <a class="last" href="<?php echo 'http://' . $_SERVER["SERVER_NAME"] ?>/wiki/index.php/Help:Contents">Help</a>
    <span class="pull-right">
      <a href="<?php echo $footerLinks['copyright']['url']; ?>"><?php echo $footerLinks['copyright']['label']; ?></a> |
      <a href="<?php echo $footerLinks['terms-of-use']['url']; ?>"><?php echo $footerLinks['terms-of-use']['label']; ?></a>
    </span>
  </footer>
  </div>
</body>
</html>

<?php
// FIXME: update this function with details.
function xxx_get_footer_links() {
    // TODO: add the links.
}
