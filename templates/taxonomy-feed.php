<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */
status_header( 200 );
header( 'Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option( 'blog_charset' ), true );
$more = 1;

/*echo feed_content_type('rss-http');*/

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>'; ?>

<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" <?php do_action( 'saumag-contact-taxonomy-feed-ns' ); ?>>

<channel>
	<title><?php bloginfo_rss( 'name' ); wp_title_rss(); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss( 'url' ) ?></link>
	<description><?php bloginfo_rss( 'description' ) ?></description>
	<lastBuildDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	<?php do_action( 'saumag-contact-taxonomy-feed-head' ); ?>
	<?php global $sau_feed_terms; foreach( $sau_feed_terms as $term ) : ?>
<?php 
$termlink = get_term_link( $term );
if ( is_wp_error( $termlink ) )
	continue;
$termlink = esc_url( $termlink );
if ( ! is_object( $term ) )
	continue;
?>
	<item>
		<title><?php echo $term->name ?></title>
		<link><?php echo $termlink ?></link>
		<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>

		<guid isPermaLink="false"><?php echo $termlink ?></guid>
		<description><![CDATA[<?php echo apply_filters( 'the_content_feed', wpautop( $term->description ) ) ?>]]></description>
		<content:encoded><![CDATA[<?php echo apply_filters( 'the_content_feed', wpautop( $term->description ) ) ?>]]></content:encoded>
		<wfw:commentRss><?php echo esc_url( trailingslashit( $termlink ) . 'feed/' ); ?></wfw:commentRss>
	<?php do_action('rss2_item'); ?>
	</item>
	<?php endforeach; ?>
</channel>
</rss>