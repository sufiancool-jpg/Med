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
    $("[data-mp-publication-download-fields]").each(function () {
      const $group = $(this);
      const $toggle = $group.find("[data-mp-publication-download-toggle]").first();
      const $fields = $group.find("[data-mp-publication-download-fields-inner]").first();
      const showDownloadFields = !isPodcast && $toggle.is(":checked");

      $group.toggle(!isPodcast);
      $toggle.prop("disabled", isPodcast);
      $fields.toggle(showDownloadFields);
      $fields.find("input, textarea, select, button").prop("disabled", !showDownloadFields);
    });
  };

  const hideOutputTypePanel = () => {
    if (!$("[data-mp-output-type-select]").length) {
      return;
    }

    const editPostDispatch = window.wp?.data?.dispatch?.("core/edit-post");
    editPostDispatch?.removeEditorPanel?.("taxonomy-panel-mp_output_type");

    const fallbackPanel = document.querySelector(
      '[data-panel="taxonomy-panel-mp_output_type"]'
    );
    if (fallbackPanel instanceof HTMLElement) {
      fallbackPanel.remove();
    }
  };

  const bindOutputTypePanelCleanup = () => {
    if (!$("[data-mp-output-type-select]").length) {
      return;
    }

    hideOutputTypePanel();

    let attempts = 0;
    const timer = window.setInterval(() => {
      attempts += 1;
      hideOutputTypePanel();

      if (attempts >= 40) {
        window.clearInterval(timer);
      }
    }, 400);
  };

  const normalizeHexColor = (value) => {
    const normalized = (value || "").toString().trim();
    return /^#([0-9a-f]{6})$/i.test(normalized) ? normalized.toLowerCase() : "";
  };

  const syncProjectColorField = (field, source) => {
    const picker = field?.querySelector("[data-mp-color-picker]");
    const valueInput = field?.querySelector("[data-mp-color-value]");

    if (!(picker instanceof HTMLInputElement) || !(valueInput instanceof HTMLInputElement)) {
      return;
    }

    if (source === "picker") {
      valueInput.value = picker.value;
      return;
    }

    const normalized = normalizeHexColor(valueInput.value);
    if (normalized) {
      picker.value = normalized;
      valueInput.value = normalized;
    }
  };

  const syncProjectLeadOptions = () => {
    const teamSelect = document.querySelector("[data-mp-team-select]");
    const leadSelect = document.querySelector("[data-mp-lead-select]");

    if (!(teamSelect instanceof HTMLSelectElement) || !(leadSelect instanceof HTMLSelectElement)) {
      return;
    }

    const selectedTeamIds = new Set(
      Array.from(teamSelect.selectedOptions)
        .map((option) => option.value)
        .filter(Boolean)
    );

    Array.from(leadSelect.options).forEach((option) => {
      if (!option.value) {
        option.hidden = false;
        option.disabled = false;
        return;
      }

      const isAllowed =
        selectedTeamIds.size === 0 ||
        selectedTeamIds.has(option.value);

      option.hidden = !isAllowed;
      option.disabled = !isAllowed;

      if (!isAllowed && option.selected) {
        option.selected = false;
      }
    });
  };

  const syncProjectStageOptions = () => {
    const stageInput = document.querySelector("[data-mp-stage-points-input]");
    const stageSelect = document.querySelector("[data-mp-current-stage-select]");

    if (!(stageInput instanceof HTMLTextAreaElement) || !(stageSelect instanceof HTMLSelectElement)) {
      return;
    }

    const stages = stageInput.value
      .split(/\r?\n/)
      .map((value) => value.trim())
      .filter(Boolean);

    if (!stages.length) {
      return;
    }

    const currentValue = stageSelect.value;
    stageSelect.innerHTML = "";

    stages.forEach((stage) => {
      const option = document.createElement("option");
      option.value = stage;
      option.textContent = stage;
      stageSelect.append(option);
    });

    if (stages.includes(currentValue)) {
      stageSelect.value = currentValue;
      return;
    }

    stageSelect.value = stages[0];
  };

  const syncParentProjectOptions = () => {
    const toggle = document.querySelector("[data-mp-parent-project-toggle]");
    const fields = document.querySelector("[data-mp-parent-project-fields]");
    const select = document.querySelector("[data-mp-parent-project-select]");

    if (
      !(toggle instanceof HTMLInputElement) ||
      !(fields instanceof HTMLElement) ||
      !(select instanceof HTMLSelectElement)
    ) {
      return;
    }

    fields.hidden = !toggle.checked;
    select.disabled = !toggle.checked;
  };

  const syncAlignedProjectOptions = () => {
    const toggle = document.querySelector("[data-mp-aligned-project-toggle]");
    const fields = document.querySelector("[data-mp-aligned-project-fields]");
    const select = document.querySelector("[data-mp-aligned-project-select]");

    if (
      !(toggle instanceof HTMLInputElement) ||
      !(fields instanceof HTMLElement) ||
      !(select instanceof HTMLSelectElement)
    ) {
      return;
    }

    fields.hidden = !toggle.checked;
    select.disabled = !toggle.checked;
  };

  const syncCustomFocusAreaOptions = () => {
    document.querySelectorAll("[data-mp-custom-focus-area-card]").forEach((card) => {
      const toggle = card.querySelector("[data-mp-custom-focus-area-toggle]");
      const fields = card.querySelector("[data-mp-custom-focus-area-fields]");

      if (!(toggle instanceof HTMLInputElement) || !(fields instanceof HTMLElement)) {
        return;
      }

      fields.hidden = !toggle.checked;
      card.style.opacity = toggle.checked ? "1" : "0.72";
    });
  };

  const syncDonorPreview = ($row) => {
    const $input = $row.find(".mp-media-input").first();
    const $preview = $row.find(".mp-donor-preview").first();
    const $empty = $row.find(".mp-donor-preview-empty").first();
    const value = ($input.val() || "").toString().trim();

    if (value) {
      $preview.attr("src", value).show();
      $empty.hide();
      return;
    }

    $preview.attr("src", "").hide();
    $empty.show();
  };

  const addDonorRow = ($button) => {
    const targetSelector = $button.data("target");
    const $list = $(targetSelector);
    const template = document.querySelector("#mp-project-donor-template");

    if (!$list.length || !(template instanceof HTMLTemplateElement)) {
      return;
    }

    const fragment = template.content.cloneNode(true);
    $list.append(fragment);
    syncDonorPreview($list.find(".mp-donor-row").last());
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
    const maxSizeBytes = Number($(this).data("max-size-bytes") || 0);
    const maxSizeLabel = ($(this).data("max-size-label") || "")
      .toString()
      .trim();
    const frame = buildFrame(libraryType);

    frame.on("select", function () {
      const attachment = frame.state().get("selection").first()?.toJSON();
      const attachmentSize =
        Number(attachment?.filesizeInBytes || attachment?.filesize || 0) || 0;

      if (maxSizeBytes > 0 && attachmentSize > maxSizeBytes) {
        window.alert(
          `This file is too large. ${maxSizeLabel || "Please choose a smaller file."}`
        );
        return;
      }

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

  $(document).on("click", "[data-mp-donor-add]", function (event) {
    event.preventDefault();
    addDonorRow($(this));
  });

  $(document).on("click", ".mp-donor-remove", function (event) {
    event.preventDefault();
    const $row = $(this).closest(".mp-donor-row");
    const $list = $row.parent();

    if ($list.find(".mp-donor-row").length <= 1) {
      $row.find(".mp-media-input").first().val("").trigger("change");
      return;
    }

    $row.remove();
  });

  $(document).on("input", ".mp-selection-search", function () {
    filterSelectionList($(this));
  });

  $(document).on("change", ".mp-selection-checkbox", function () {
    updateSelectionCount($(this).closest(".mp-selection-panel"));
  });

  $(document).on("change", "[data-mp-output-type-select]", syncPublicationTypeUi);
  $(document).on("change", "[data-mp-publication-download-toggle]", syncPublicationTypeUi);
  $(document).on("change", "[data-mp-team-select]", syncProjectLeadOptions);
  $(document).on("input", "[data-mp-stage-points-input]", syncProjectStageOptions);
  $(document).on("change", "[data-mp-parent-project-toggle]", syncParentProjectOptions);
  $(document).on("change", "[data-mp-aligned-project-toggle]", syncAlignedProjectOptions);
  $(document).on("change", "[data-mp-custom-focus-area-toggle]", syncCustomFocusAreaOptions);
  $(document).on("change", "[data-mp-color-picker]", function () {
    syncProjectColorField(this.closest("[data-mp-color-field]"), "picker");
  });
  $(document).on("input", "[data-mp-color-value]", function () {
    syncProjectColorField(this.closest("[data-mp-color-field]"), "value");
  });
  $(document).on("change input", ".mp-donor-row .mp-media-input", function () {
    syncDonorPreview($(this).closest(".mp-donor-row"));
  });

  $(".mp-selection-panel").each(function () {
    const $panel = $(this);
    updateSelectionCount($panel);
    filterSelectionList($panel.find(".mp-selection-search").first());
  });

  $(".mp-donor-row").each(function () {
    syncDonorPreview($(this));
  });

  syncPublicationTypeUi();
  bindOutputTypePanelCleanup();
  syncProjectLeadOptions();
  syncProjectStageOptions();
  syncParentProjectOptions();
  syncAlignedProjectOptions();
  syncCustomFocusAreaOptions();
  document.querySelectorAll("[data-mp-color-field]").forEach((field) => {
    syncProjectColorField(field, "value");
  });
})(jQuery);
