<?php if (isset($smart_seo_schema_data) && !empty($smart_seo_schema_data)) { ?>
<script type="application/ld+json">
<?php echo $smart_seo_schema_data; ?>
</script>
<?php } ?>

<!-- Default Open Graph meta tags (not user-editable) -->

<meta property="og:type" content="product" />
<?php if (!empty($og_name)) { ?>
<meta property="og:title" content="<?php echo htmlspecialchars($og_name, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($og_description)) { ?>
<meta property="og:description" content="<?php echo htmlspecialchars($og_description, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($og_image)) { ?>
<meta property="og:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($og_brand)) { ?>
<meta property="product:brand" content="<?php echo htmlspecialchars($og_brand, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($og_sku)) { ?>
<meta property="product:sku" content="<?php echo htmlspecialchars($og_sku, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($og_mpn)) { ?>
<meta property="product:mpn" content="<?php echo htmlspecialchars($og_mpn, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($og_category)) { ?>
<meta property="product:category" content="<?php echo htmlspecialchars($og_category, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($og_url)) { ?>
<meta property="og:url" content="<?php echo htmlspecialchars($og_url, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<!-- Twitter Card meta tags -->
<?php if (!empty($twitter_card)) { ?>
<meta name="twitter:card" content="<?php echo htmlspecialchars($twitter_card, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($twitter_title)) { ?>
<meta name="twitter:title" content="<?php echo htmlspecialchars($twitter_title, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($twitter_description)) { ?>
<meta name="twitter:description" content="<?php echo htmlspecialchars($twitter_description, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>
<?php if (!empty($twitter_image)) { ?>
<meta name="twitter:image" content="<?php echo htmlspecialchars($twitter_image, ENT_QUOTES, 'UTF-8'); ?>" />
<?php } ?>