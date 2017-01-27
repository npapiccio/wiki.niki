<?php global $helpdesk; ?>
<header class="banner navbar navbar-default navbar-static-top" role="banner">
  <div class="container">

    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>

      <?php if (!empty($helpdesk['logo']['url'])) { ?>
        <a class="navbar-brand-img" title="<?php bloginfo('name'); ?>" href="<?php echo home_url(); ?>"><img src="<?php echo $helpdesk['logo']['url']; ?>" alt="<?php bloginfo('name'); ?>"/></a>
      <?php } else { ?>
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
      <?php } ?>

    </div>

    <nav class="collapse navbar-collapse navbar-left" role="navigation">
      <?php
        if (has_nav_menu('primary_navigation_left')) :
          wp_nav_menu(array('theme_location' => 'primary_navigation_left', 'menu_class' => 'nav navbar-nav'));
        endif;
      ?>
    </nav>

    <nav class="collapse navbar-collapse navbar-right" role="navigation">
      <?php
        if (has_nav_menu('primary_navigation_right')) :
          wp_nav_menu(array('theme_location' => 'primary_navigation_right', 'menu_class' => 'nav navbar-nav'));
        endif;
      ?>
    </nav>

  </div>
</header>
