<?php
/**
 * Partial template for just documentation main content
 *
 */
?>

	<div class="a8c-docs-layout__main__content__primary">

		<main>
			<?php do_action( 'a8c_docs_primary_start' ); ?>

			<?php get_template_part( 'template-parts/content/content' ); ?>
		</main>

		<?php get_template_part( 'template-parts/post/related-topics' ); ?>

		<?php get_template_part( 'template-parts/content/content-footer' ); ?>

	</div>
	<!-- .a8c-docs-layout__main__content__primary -->

	<div class="a8c-docs-layout__main__content__secondary">
		<?php do_action( 'a8c_docs_secondary_start' ); ?>

		<nav id="docs-toc-nav" class="a8c-docs-table-of-contents__sticky" aria-label="Table of Contents">
			<?php // Note: This content is generated via JS. ?>
		</nav>
	</div>
	<!-- .a8c-docs-layout__main__content__secondary -->
