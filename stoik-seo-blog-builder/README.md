# Stoik SEO Blog Builder

Stoik SEO Blog Builder is a standalone WordPress plugin for creating SEO-ready blog posts from a simplified dashboard. It is designed for manual upload through WordPress Admin or Hostinger file manager.

## What it adds

- A new **SEO Blog Builder** admin menu.
- A guided post creation form for title, body copy, excerpt, categories, tags, status, and images.
- WordPress media library support for a featured image and supporting images.
- Plain-text formatting into paragraphs, H2/H3 headings, and lists.
- SEO title, meta description, focus keyword, reading-time, Open Graph, Twitter card, and JSON-LD article schema support.
- Compatibility meta fields for Yoast SEO and Rank Math.
- Admin-only access, nonce-protected publishing, image attachment validation, and creator audit metadata.
- A custom editorial blog homepage shortcode:

```text
[stoik_blog_home]
```

The shortcode also works as:

```text
[seo_blog_home]
```

## Manual install on Hostinger WordPress

1. Zip the `stoik-seo-blog-builder` folder.
2. Log in to your WordPress admin dashboard.
3. Go to **Plugins > Add New Plugin > Upload Plugin**.
4. Upload the zip file.
5. Click **Install Now**.
6. Click **Activate Plugin**.
7. Open the new **SEO Blog Builder** menu in WordPress.

If WordPress upload is blocked by file size or permissions:

1. Log in to Hostinger hPanel.
2. Open **Files > File Manager**.
3. Go to `public_html/wp-content/plugins/`.
4. Upload the zip file and extract it there, or upload the unzipped `stoik-seo-blog-builder` folder.
5. Go back to WordPress Admin > **Plugins** and activate **Stoik SEO Blog Builder**.

## Create your blog homepage

1. In WordPress, go to **Pages > Add New**.
2. Name the page **Blog** or **Journal**.
3. Add this shortcode:

```text
[stoik_blog_home]
```

4. Publish the page.
5. Add that page to your site navigation from **Appearance > Menus** or the Site Editor.

## Shortcode options

```text
[stoik_blog_home posts_per_page="9" title="Field Notes" eyebrow="Stoik Journal" intro="Practical essays, updates, and guides built for focused readers."]
```

Filter by category slug:

```text
[stoik_blog_home category="training"]
```

Filter by tag slug:

```text
[stoik_blog_home tag="nutrition"]
```

## SEO notes

The plugin stores SEO fields on the post and outputs meta tags when no major SEO plugin is active. If Yoast SEO or Rank Math is active, the plugin syncs the SEO title, meta description, and focus keyword into their common meta fields so those plugins can control front-end SEO output.

For best SEO results:

- Use a clear keyword-focused title.
- Keep SEO titles near 50-60 characters.
- Keep meta descriptions near 120-160 characters.
- Add descriptive alt text to every image.
- Choose one main category and a few relevant tags.
- Link from each post to related service pages or older articles when useful.

## Security notes

- The dashboard is restricted to WordPress administrators by requiring the `manage_options` capability.
- Form submissions are protected with a WordPress nonce.
- Publishing still respects the `publish_posts` capability; users without that capability are forced to pending review.
- Selected media IDs are validated as real image attachments that the current user can edit.
- Category and tag assignment respects WordPress taxonomy capabilities.
- Created posts store audit metadata for the creator user ID and creation timestamp.
- The plugin does not create custom database tables or make external API requests.
