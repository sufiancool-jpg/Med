(function ($) {
  const updateSelectionCount = ($panel) => {
    const $count = $panel.closest(".mp-selection-card").find(".mp-selection-count").first();

    if (!$count.length) {
      return;
    }

    const selectedCount = $panel.find(".mp-selection-checkbox:checked").length;
    const emptyLabel = $count.data("emptyLabel") || "No publications selected";
    const singularLabel =
      $count.data("singularLabel") || "publication selected";
    const pluralLabel =
      $count.data("pluralLabel") || "publications selected";

    if (!selectedCount) {
      $count.text(emptyLabel);
      return;
    }

    $count.text(
      `${selectedCount} ${
        selectedCount === 1 ? singularLabel : pluralLabel
      }`
    );
  };

  const filterSelectionList = ($input) => {
    const query = ($input.val() || "").toString().trim().toLowerCase();
    const targetSelector = $input.data("target");
    const $list = $(targetSelector);
    const $panel = $input.closest(".mp-selection-panel");
    const $items = $list.find(".mp-selection-item");
    let visibleCount = 0;

    $items.each(function () {
      const $item = $(this);
      const label = ($item.data("selectionLabel") || "")
        .toString()
        .toLowerCase();
      const isVisible = !query || label.includes(query);

      $item.toggle(isVisible);
      if (isVisible) {
        visibleCount += 1;
      }
    });

    $panel.find(".mp-selection-empty").prop("hidden", visibleCount !== 0);
  };

  const syncPublicationTypeUi = () => {
    const $select = $("[data-mp-output-type-select]").first();
    if (!$select.length) {
      return;
    }

    const isPodcast = ($select.val() || "").toString() === "pod-cast";
    $("[data-mp-podcast-fields]").toggle(isPodcast);
  };

  const buildFrame = (libraryType) =>
    wp.media({
      title: "Select media",
      button: { text: "Use this file" },
      multiple: false,
      library: libraryType ? { type: libraryType } : undefined,
    });

  $(document).on("click", ".mp-media-upload", function (event) {
    event.preventDefault();

    const $field = $(this).closest(".mp-media-field");
    const $input = $field.find(".mp-media-input").first();
    const libraryType = $(this).data("library-type");
    const frame = buildFrame(libraryType);

    frame.on("select", function () {
      const attachment = frame.state().get("selection").first()?.toJSON();
      if (attachment?.url) {
        $input.val(attachment.url).trigger("change");
      }
    });

    frame.open();
  });

  $(document).on("click", ".mp-media-clear", function (event) {
    event.preventDefault();
    const $field = $(this).closest(".mp-media-field");
    $field.find(".mp-media-input").first().val("").trigger("change");
  });

  $(document).on("input", ".mp-selection-search", function () {
    filterSelectionList($(this));
  });

  $(document).on("change", ".mp-selection-checkbox", function () {
    updateSelectionCount($(this).closest(".mp-selection-panel"));
  });

  $(document).on("change", "[data-mp-output-type-select]", syncPublicationTypeUi);

  $(".mp-selection-panel").each(function () {
    const $panel = $(this);
    updateSelectionCount($panel);
    filterSelectionList($panel.find(".mp-selection-search").first());
  });

  syncPublicationTypeUi();
})(jQuery);
