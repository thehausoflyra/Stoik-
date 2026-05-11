(function ($) {
  function updateChecklist() {
    const seoTitle = $('.sbb-seo-title').val() || '';
    const description = $('.sbb-meta-description').val() || '';
    const keyword = $('.sbb-focus-keyword').val() || '';
    const featuredId = $('.sbb-featured-image-id').val() || '';
    const altText = $('.sbb-image-alt').val() || '';

    $('.sbb-title-count').text(seoTitle.length);
    $('.sbb-description-count').text(description.length);

    setCheck('title', seoTitle.length >= 50 && seoTitle.length <= 60);
    setCheck('description', description.length >= 120 && description.length <= 160);
    setCheck('keyword', keyword.trim().length > 0);
    setCheck('featured', featuredId.trim().length > 0);
    setCheck('alt', altText.trim().length > 0);
  }

  function setCheck(name, isComplete) {
    $('.sbb-checklist [data-check="' + name + '"]').toggleClass('is-complete', isComplete);
  }

  function setFeaturedImage(attachment) {
    $('.sbb-featured-image-id').val(attachment.id);
    $('.sbb-featured-preview').html('<img src="' + getPreviewUrl(attachment) + '" alt="" />');
    updateChecklist();
  }

  function setSupportingImages(attachments) {
    const ids = [];
    const images = [];

    attachments.each(function (attachment) {
      attachment = attachment.toJSON();
      ids.push(attachment.id);
      images.push('<img src="' + getPreviewUrl(attachment) + '" alt="" />');
    });

    $('.sbb-image-ids').val(ids.join(','));
    $('.sbb-images-preview').html(images.join(''));
  }

  function getPreviewUrl(attachment) {
    if (attachment.sizes && attachment.sizes.thumbnail) {
      return attachment.sizes.thumbnail.url;
    }

    if (attachment.sizes && attachment.sizes.medium) {
      return attachment.sizes.medium.url;
    }

    return attachment.url;
  }

  $(function () {
    let featuredFrame;
    let galleryFrame;

    $('.sbb-select-featured').on('click', function (event) {
      event.preventDefault();

      if (featuredFrame) {
        featuredFrame.open();
        return;
      }

      featuredFrame = wp.media({
        title: sbbAdmin.chooseFeatured,
        button: { text: sbbAdmin.useImage },
        multiple: false,
        library: { type: 'image' },
      });

      featuredFrame.on('select', function () {
        setFeaturedImage(featuredFrame.state().get('selection').first().toJSON());
      });

      featuredFrame.open();
    });

    $('.sbb-clear-featured').on('click', function (event) {
      event.preventDefault();
      $('.sbb-featured-image-id').val('');
      $('.sbb-featured-preview').empty();
      updateChecklist();
    });

    $('.sbb-select-images').on('click', function (event) {
      event.preventDefault();

      if (galleryFrame) {
        galleryFrame.open();
        return;
      }

      galleryFrame = wp.media({
        title: sbbAdmin.chooseImages,
        button: { text: sbbAdmin.useImages },
        multiple: true,
        library: { type: 'image' },
      });

      galleryFrame.on('select', function () {
        setSupportingImages(galleryFrame.state().get('selection'));
      });

      galleryFrame.open();
    });

    $('.sbb-clear-images').on('click', function (event) {
      event.preventDefault();
      $('.sbb-image-ids').val('');
      $('.sbb-images-preview').empty();
    });

    $('.sbb-seo-title, .sbb-meta-description, .sbb-focus-keyword, .sbb-image-alt').on(
      'input',
      updateChecklist
    );

    updateChecklist();
  });
})(jQuery);
