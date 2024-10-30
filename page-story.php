<?php
/**
* Template Name: Story Page
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<div id="primary" class="site-content">
    <div id="story_content story-block" role="main">
        <?php
            $paged = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
            query_posts( 
                array ( 
                    'post_type' => 'story',
                    'post_status' => 'publish',
                    'posts_per_page' => 10, 
                    'paged' => $paged 
                ) 
            );      

            while ( have_posts() ) : the_post(); 
        ?>
            <div class="story_item"
                class="<?php join( ' ', get_post_class() ); ?>" 
                id="<?php echo esc_attr('post-' . get_the_id()); ?>"
            >
                <div class="story_item_image">
                    <?php 
                        $post_id = get_the_id();
                        $post_type = get_post_type($post_id);
                        $post_permalink = get_post_permalink($post_id);                                            
                    ?>
                    <a href="<?php  echo esc_url($post_permalink); ?>">
                        <?php the_post_thumbnail( 'medium_large' ); ?>
                    </a>                   
                </div>
                <div class="story_item_content">
                    <div style="display:flex; justify-content: space-between;">
                        <div class="story_post_category">
                            <?php echo esc_html(get_the_category_list(','));?>
                        </div>	
                        <div class="story_min_read">
                            <?php echo (int) curatora_display_read_time(get_the_id()); ?>
                            <span class=""> Mins Read</span>
                        </div>								
                    </div>

                    <h2 class="story_post_title">								
                        <a rel="nofollow" href="<?php echo esc_url($post_permalink); ?>"><?php the_title(); ?></a>									
                    </h2>

                    <div>
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <div class="story_meta_content">
                        <div class="story_author" style="display:flex; justify-content: space-between;">
                            <div>
                                <span class="story_author_designation">
                                    Source:
                                    <span class="story_author_name">
                                        <?php 
                                            $post_source = get_post_meta( get_the_id(), 'post_source', true); 
                                            $parse = parse_url($post_source);
                                        ?>
                                        <?php 
                                            if(empty($post_source)) {
                                                the_author_posts_link();
                                            } else { ?>
                                                <a rel="nofollow" href="<?php echo esc_url($post_source); ?>" target="_blank">
                                                    <?php echo esc_html($parse['host']); ?>
                                                </a>
                                        <?php } ?>
                                        <br>
                                    </span>
                                </span>  
                                                                
                                <span class="story_author_designation">
                                    Curated By: <?php echo  esc_html(get_the_author_meta('display_name')); ?>
                                </span> 
                            </div>
                                                    
                            <div class="story_pipe">|</div>
                            <div class="story_meta_date">
                                <?php the_time('M j, Y'); ?>
                            </div>
                        </div>
                    </div>                                 
                </div>                    
            </div>
        <?php
            endwhile;

            the_posts_pagination();

            // Reset Query
            wp_reset_query();
        ?>
        <div style="clear:both;"></div>
    </div><!-- #content -->
  </div><!-- #primary -->

<?php get_footer(); ?>