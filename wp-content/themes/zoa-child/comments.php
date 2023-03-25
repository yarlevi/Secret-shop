<?php if ( post_password_required() ) {
	return;
} ?>

<div id="comments" class="comments-area">
	<?php if ( have_comments() ) : /*comment list*/ ?>
		<h3 class="comments-title">
		<?php
			echo sprintf( _nx( 'Comment ( 1 )', 'Comments ( %s )', get_comments_number(), 'comment count', 'zoa' ), get_comments_number() );
		?>
		</h3>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :/*comment navigation*/ ?>
			<nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'zoa' ); ?></h2>
				<div class="cmt-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'zoa' ) ); ?></div>
				<div class="cmt-next"><?php next_comments_link( esc_html__( 'Newer Comments ', 'zoa' ) ); ?></div>
			</nav>
		<?php endif; ?>

		<div class="comment-list">
			<?php
				wp_list_comments(
					array(
						'callback' => 'zoa_comment_list',
					)
				);
			?>
		</div>

	<?php endif; ?>

	<?php if ( ! comments_open() ) : /*comment disable*/ ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'zoa' ); ?></p>
	<?php
		/*comment form*/
		else :
			$commenter = wp_get_current_commenter();

			$fields = array(
				'email'  => '<input id="email" type="email" name="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_attr__( 'Email Address', 'zoa' ) . '" required>',
				'author' => '<input id="author" type="text" name="author" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_attr__( 'Name', 'zoa' ) . '" required>',
			);

			$args = array(
				'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
				'title_reply'        => esc_html__( 'כתיבת תגובה', 'zoa-child' ),
				'title_reply_after'  => '</h3>',
				'fields'             => apply_filters( 'comment_form_default_fields', $fields ),
				'comment_field'      => '<textarea id="comment" name="comment" placeholder="' . esc_attr__( 'התגובה שלך...', 'zoa' ) . '" required>' . '</textarea>',
				'label_submit'       => esc_html__( 'שליחה', 'zoa' ),
			);

			comment_form( $args );

			/*remove novalidate on form */
			wp_add_inline_script(
				'zoa-custom',
				"document.addEventListener( 'DOMContentLoaded', function(){
                    var cmtForm = document.getElementById( 'commentform' );

                    if( ! cmtForm ) return;

                    cmtForm.removeAttribute( 'novalidate' );
                } );",
				'after'
			);
		endif;
	?>
</div>
