<?php
	/*
		Plugin Name: FrontEnd ACF gallery
		Description: ACF frontend gallery extension
		Version: 1.5
		Authors: Radim Brounek, David Zoufalý
		License: MIT
		Text Domain: rbacfgallery
	*/

	wp_enqueue_style( 'rb-gallery', plugins_url( 'css/rb-gallery.css', __FILE__ ), array(), '20180729');
	wp_enqueue_style( 'rb-gallery-slick', plugins_url( 'css/slick.css', __FILE__ ), array(), '20180729');
	wp_enqueue_style( 'swipebox', plugins_url( 'css/swipebox.min.css', __FILE__ ), array(), '20180729');
	wp_enqueue_style( 'rb-gallery-icons', plugins_url( 'icomoon/style.css', __FILE__ ), array(), '20180729');

	wp_enqueue_script( 'swipebox', plugins_url( 'js/jquery.swipebox.min.js', __FILE__ ), array(), '20180729', true);
	wp_enqueue_script( 'rb-gallery-slick', plugins_url( 'js/slick.min.js', __FILE__ ), array(), '20180729', true);
	wp_enqueue_script( 'rb-gallery', plugins_url( 'js/rb-gallery.js', __FILE__ ), array(), '20180729', true);

	define( 'rbacfgallery_COLS', 5 );
	define( 'rbacfgallery_PERPAGE', 10 );


	Class rbacfgallery
	{
		function __construct(){
			add_shortcode( 'rbacfgallery', array($this, 'rbg_shortcode') );

			add_action( 'init', array($this, 'rbg_cpt_interpret') );
			add_action( 'add_meta_boxes_rbacfgallery', array($this, 'rbg_add_meta_boxes') );

			add_image_size('rbacfgallery', 520, 320, true);

			add_action( 'manage_rbacfgallery_posts_custom_column' , array($this, 'custom_rbacfgallery_column'), 10, 2 );
			add_filter( 'manage_rbacfgallery_posts_columns', array($this, 'set_custom_rbacfgallery_columns') );
		}


		function set_custom_rbacfgallery_columns($columns) {
            $columns['shortcode'] = __( 'Shortcode', 'rbacfgallery' );

            return $columns;
        }


        function custom_rbacfgallery_column( $column, $post_id ) {
            switch ( $column ) {

                case 'shortcode' :
                	echo "<input style='width: 100%' type='text' readonly name='shortcode' value='[rbacfgallery id=$post_id]' />";
                    // echo date( 'd.m.Y', strtotime(get_field('date', $post_id)) );
                    break;
            }
        }



		public function rbg_add_meta_boxes( $post ) {
			add_meta_box( 'shortcode_meta_box', __( 'Shortcode', 'rbacfgallery' ), array($this, 'shortcode_build_meta_box'), 'rbacfgallery', 'normal', 'low' );
		}



		public function shortcode_build_meta_box( $post ) {
			?>
				
			<p>
				<input style="width: 100%" type="text" readonly name="shortcode" value='[rbacfgallery id="<?php echo $post->ID ?>"]' /> 
			</p>

			<p><strong>Vlastní úprava:</strong></p>
			<ul>
				<li>Počet sloupců: cols="5"</li>
				<li>Počet fotek na stránce: perpage="10"</li>
			</ul>
			<p>Například: [rbacfgallery id="<?php echo $post->ID ?>" cols="5" perpage="10"]</p>
				
			<?php
		}



		public function rbg_shortcode( $atts ) {
			
			if( !isset( $atts['id'] ) ){
				$id = 0;
			} else {
				$id = $atts['id'];
			}

			// pokud není nastaven atribut perpage nebo je atribut perpage < 0
			if( !isset( $atts['perpage'] ) ){
				$perpage = rbacfgallery_PERPAGE;		
			} else {
				$perpage = $atts['perpage'];
			}

			if( isset( $atts['cols'] ) ){
				$cols = $atts['cols'];
			} else {
				$cols = rbacfgallery_COLS;		
			}

			$itemWidth = 100 / $cols;

			$images = get_field('gallery', $id);
			$view_desc = get_field('show_desc', $id);

			$pages = 0;

			ob_start();
			?>
				<div class="rbacfgallery">
					<div class="rbacfgallery__wrapper">

						<div class="rbacfgallery__page active">
							<?php $page_counter = 0; ?>
                            <?php if($images): ?>
                                <?php foreach( $images as $img ): ?>

                                    <?php if( $page_counter == $perpage): ?>
                                        <?php $pages++; ?>
                                        <?php $page_counter = 1; ?>
                                        </div><div class="rbacfgallery__page">
                                    <?php else: ?>
                                        <?php $page_counter++ ?>
                                    <?php endif; ?>

                                    <div class="rbacfgallery__item" style="width: <?php echo $itemWidth . '%' ?>">
                                        <a alt="<?php echo $img['title']?>" title="<?php echo $img['caption']?>" class="swipebox rbacfgallery__img" href="<?php echo $img['sizes']['large'] ?>">

                                            <span class="gallery__placeholder">
											<img class="gallery__placeholder__inner" src="<?php echo $img['sizes']['rbacfgallery'] ?>" alt="<?php echo $img['alt']?>">
                                            </span>

                                            <?php if( $view_desc && strlen($img['caption']) > 0 ): ?><span class="rbacfgallery__title"><?php echo mb_substr($img['caption'], 0, 40) ?><?php if(strlen($img['caption']) > 40): ?>...<?php endif;?></span><?php endif; ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif ?>
						</div>
					</div>

					<?php if( $pages ): ?>
						<div class="rbacfgallery__pagination js-rbacfgallery-pagination">
							<ul>
								<?php for( $i = 0; $i <= $pages; $i++ ): ?>
									<li><a class="<?php echo $i == 0 ? 'active' : '' ?>" href="#<?php echo $i; ?>"><?php echo $i + 1; ?></a></li>
								<?php endfor; ?>
							</ul>
						</div>
					<?php endif; ?>


                    <div class="rbacfgallery__slick js-rbacfgallery__slick">
                        <?php foreach( $images as $img ): ?>
                            <div class="rbacfgallery__mobile-item">
                                <a class="swipebox rbacfgallery__img" alt="<?php echo $img['title']?>" title="<?php echo $img['caption']?>" href="<?php echo $img['sizes']['large'] ?>">

                                                <span class="gallery__placeholder">
                                                    <img class="gallery__placeholder__inner" src="<?php echo $img['sizes']['rbacfgallery'] ?>" alt="<?php echo $img['alt']?>">
                                                </span>

                                    <?php if($view_desc && strlen($img['caption']) > 0) : ?><span class="rbacfgallery__title"><?php echo mb_substr($img['title'], 0, 60) ?><?php if(strlen($img['caption']) > 40): ?>...<?php endif;?></span><?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

				</div>
			<?php 
			
			$shortcode_content = ob_get_contents();
			ob_end_clean();

			return $shortcode_content;
		}

		public function rbg_cpt_interpret() {
		    $labels = array(
		        'name'                => _x( 'Galerie', 'Post Type General Name', 'text_domain' ),
		        'singular_name'       => _x( 'Galerie', 'Post Type Singular Name', 'text_domain' ),
		        'menu_name'           => __( 'Galerie', 'text_domain' ),
		        'name_admin_bar'      => __( 'Galerie', 'text_domain' ),
		        'parent_item_colon'   => __( 'Nadřazená galerie', 'text_domain' ),
		        'all_items'           => __( 'Všechny galerie', 'text_domain' ),
		        'add_new_item'        => __( 'Přidat novou galerii', 'text_domain' ),
		        'add_new'             => __( 'Nová galerie', 'text_domain' ),
		        'new_item'            => __( 'Nová galerie', 'text_domain' ),
		        'edit_item'           => __( 'Upravit', 'text_domain' ),
		        'update_item'         => __( 'Upravit', 'text_domain' ),
		        'view_item'           => __( 'Zobrazit', 'text_domain' ),
		        'search_items'        => __( 'Hledat', 'text_domain' ),
		        'not_found'           => __( 'Nenalezeno', 'text_domain' ),
		        'not_found_in_trash'  => __( 'Nenalezeno v koši', 'text_domain' ),
		    );

		    $args = array(
		        'label'               => __( 'Galerie', 'text_domain' ),
		        'description'         => __( 'Galerie', 'text_domain' ),
		        'labels'              => $labels,
		        'hierarchical'        => false,
		        'public'              => true,
		        'show_ui'             => true,
		        'show_in_menu'        => true,
		        'menu_position'       => 5,
		        'show_in_admin_bar'   => true,
		        'show_in_nav_menus'   => false,
		        'can_export'          => true,
		        'has_archive'         => false,
		        'exclude_from_search' => true,
		        'publicly_queryable'  => true,
		        'supports'            => array('title')
		    );
		    register_post_type( 'rbacfgallery', $args );
		}
	}

	$rbacfgallery = new rbacfgallery();