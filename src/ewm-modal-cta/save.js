/**
 * The save function for EWM Modal CTA block.
 *
 * Since this is a dynamic block that uses server-side rendering via render.php,
 * we return null to let WordPress handle the rendering on the server.
 *
 * This approach ensures that:
 * 1. The block uses the same rendering engine as shortcodes
 * 2. Modal data is always fresh from the database
 * 3. No client-side/server-side content mismatch
 * 4. Better performance and SEO
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {null} Null for dynamic blocks - rendering handled by render.php
 */
export default function save() {
	// Return null for dynamic blocks
	// The actual rendering is handled by render.php on the server
	// using the same EWM_Render_Core engine as shortcodes
	return null;
}
