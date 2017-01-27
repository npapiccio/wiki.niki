<?php global $helpdesk; ?>
<?php if ( $helpdesk['headline_search'] == 2 ) { ?>
	<form class="navbar-form search-main" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
	  <div class="search-form-group">
	    <span class="icon-Magnifi-Glass2"></span>
	    <input type="search" value="<?php echo get_search_query(); ?>" id="live" class="searchajax search-query form-control" autocomplete="off" placeholder="<?php echo $helpdesk['search_placeholder']; ?>" name="s">
	  </div>
	</form>
	<script> _url = '<?php echo home_url(); ?>';</script>
<?php } else { ?>
	<form class="navbar-form search-main" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
	  <div class="search-form-group">
	    <span class="icon-Magnifi-Glass2"></span>
	    <input type="search" value="<?php echo get_search_query(); ?>" class="form-control" placeholder="<?php echo $helpdesk['search_placeholder']; ?>" name="s">
	  </div>
	</form>
<?php } ?>