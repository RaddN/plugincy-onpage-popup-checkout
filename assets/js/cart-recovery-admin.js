document.addEventListener("DOMContentLoaded", function () {
  const modal = document.querySelector(".onepaqucpro-cr-modal");
  const modalContent = modal ? modal.querySelector(".onepaqucpro-cr-modal__content") : null;
  const body = document.body;

  function formatChartValue(value, format) {
    if (format === "currency") {
      return "$" + Number(value || 0).toFixed(2);
    }

    return String(Number(value || 0));
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getNiceMax(maxValue) {
    if (maxValue <= 1) {
      return 1;
    }

    const magnitude = 10 ** Math.floor(Math.log10(maxValue));
    const normalized = maxValue / magnitude;

    if (normalized <= 1) {
      return magnitude;
    }
    if (normalized <= 2) {
      return 2 * magnitude;
    }
    if (normalized <= 5) {
      return 5 * magnitude;
    }

    return 10 * magnitude;
  }

  function bindChartTooltip(container) {
    const tooltip = container.querySelector(".onepaqucpro-cr-chart-tooltip");
    const hits = container.querySelectorAll("[data-chart-label]");

    if (!tooltip || !hits.length) {
      return;
    }

    hits.forEach(function (hit) {
      hit.addEventListener("mouseenter", function () {
        tooltip.hidden = false;
        tooltip.innerHTML =
          "<strong>" +
          escapeHtml(hit.getAttribute("data-chart-label")) +
          "</strong>" +
          escapeHtml(hit.getAttribute("data-chart-value"));
      });

      hit.addEventListener("mousemove", function (event) {
        const bounds = container.getBoundingClientRect();
        tooltip.style.left = event.clientX - bounds.left + "px";
        tooltip.style.top = event.clientY - bounds.top + "px";
      });

      hit.addEventListener("mouseleave", function () {
        tooltip.hidden = true;
      });
    });
  }

  function renderLineChart(container, config) {
    const labels = Array.isArray(config.labels) ? config.labels : [];
    const values = Array.isArray(config.values) ? config.values : [];
    const width = 760;
    const height = 300;
    const padding = { top: 18, right: 18, bottom: 42, left: 44 };
    const safeValues = values.map(function (value) {
      return Number(value || 0);
    });
    const maxValue = getNiceMax(Math.max.apply(null, safeValues.concat([0])));
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const stepX = labels.length > 1 ? chartWidth / (labels.length - 1) : 0;
    const points = safeValues.map(function (value, index) {
      const x = padding.left + stepX * index;
      const ratio = maxValue ? value / maxValue : 0;
      const y = padding.top + chartHeight - chartHeight * ratio;

      return { x: x, y: y, value: value, label: labels[index] };
    });
    const linePath = points
      .map(function (point, index) {
        return (index === 0 ? "M" : "L") + point.x + " " + point.y;
      })
      .join(" ");
    const areaPath =
      points.length > 1
        ? linePath +
          " L " +
          (padding.left + stepX * (points.length - 1)) +
          " " +
          (padding.top + chartHeight) +
          " L " +
          padding.left +
          " " +
          (padding.top + chartHeight) +
          " Z"
        : "";

    let svg = '<svg viewBox="0 0 ' + width + " " + height + '" aria-hidden="true">';

    for (let tick = 0; tick <= 4; tick += 1) {
      const y = padding.top + (chartHeight / 4) * tick;
      const tickValue = maxValue - (maxValue / 4) * tick;
      svg +=
        '<line class="grid-line" x1="' +
        padding.left +
        '" y1="' +
        y +
        '" x2="' +
        (width - padding.right) +
        '" y2="' +
        y +
        '"></line>';
      svg +=
        '<text class="axis-label" x="' +
        (padding.left - 10) +
        '" y="' +
        (y + 4) +
        '" text-anchor="end">' +
        escapeHtml(formatChartValue(tickValue, config.format)) +
        "</text>";
    }

    labels.forEach(function (label, index) {
      const x = padding.left + stepX * index;
      svg +=
        '<text class="axis-label" x="' +
        x +
        '" y="' +
        (height - 14) +
        '" text-anchor="middle">' +
        escapeHtml(label) +
        "</text>";
    });

    if (areaPath) {
      svg +=
        '<path class="area-path" d="' +
        areaPath +
        '" fill="' +
        escapeHtml(config.color || "#5d87ff") +
        '"></path>';
    }

    svg +=
      '<path class="line-path" d="' +
      linePath +
      '" stroke="' +
      escapeHtml(config.color || "#5d87ff") +
      '"></path>';

    points.forEach(function (point) {
      svg +=
        '<circle class="line-point" cx="' +
        point.x +
        '" cy="' +
        point.y +
        '" r="5" fill="#fff" stroke="' +
        escapeHtml(config.color || "#5d87ff") +
        '" stroke-width="3"></circle>';
      svg +=
        '<circle class="line-hit" cx="' +
        point.x +
        '" cy="' +
        point.y +
        '" r="14" data-chart-label="' +
        escapeHtml(point.label) +
        '" data-chart-value="' +
        escapeHtml(formatChartValue(point.value, config.format)) +
        '"></circle>';
    });

    svg += "</svg>";

    container.innerHTML =
      svg + '<div class="onepaqucpro-cr-chart-tooltip" hidden></div>';
    bindChartTooltip(container);
  }

  function renderBarChart(container, config) {
    const labels = Array.isArray(config.labels) ? config.labels : [];
    const values = Array.isArray(config.values) ? config.values : [];
    const colors = Array.isArray(config.colors) ? config.colors : [];
    const width = 760;
    const height = 320;
    const padding = { top: 18, right: 18, bottom: 64, left: 44 };
    const safeValues = values.map(function (value) {
      return Number(value || 0);
    });
    const maxValue = getNiceMax(Math.max.apply(null, safeValues.concat([0])));
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const barGap = 18;
    const barWidth =
      labels.length > 0
        ? (chartWidth - Math.max(0, labels.length - 1) * barGap) / labels.length
        : 0;

    let svg = '<svg viewBox="0 0 ' + width + " " + height + '" aria-hidden="true">';

    for (let tick = 0; tick <= 4; tick += 1) {
      const y = padding.top + (chartHeight / 4) * tick;
      const tickValue = maxValue - (maxValue / 4) * tick;
      svg +=
        '<line class="grid-line" x1="' +
        padding.left +
        '" y1="' +
        y +
        '" x2="' +
        (width - padding.right) +
        '" y2="' +
        y +
        '"></line>';
      svg +=
        '<text class="axis-label" x="' +
        (padding.left - 10) +
        '" y="' +
        (y + 4) +
        '" text-anchor="end">' +
        escapeHtml(formatChartValue(tickValue, config.format)) +
        "</text>";
    }

    labels.forEach(function (label, index) {
      const value = safeValues[index] || 0;
      const ratio = maxValue ? value / maxValue : 0;
      const barHeight = chartHeight * ratio;
      const x = padding.left + index * (barWidth + barGap);
      const y = padding.top + chartHeight - barHeight;
      const color = colors[index] || "#5d87ff";

      svg +=
        '<rect x="' +
        x +
        '" y="' +
        y +
        '" width="' +
        barWidth +
        '" height="' +
        barHeight +
        '" rx="8" fill="' +
        escapeHtml(color) +
        '"></rect>';
      svg +=
        '<rect class="bar-hit" x="' +
        x +
        '" y="' +
        padding.top +
        '" width="' +
        barWidth +
        '" height="' +
        chartHeight +
        '" data-chart-label="' +
        escapeHtml(label) +
        '" data-chart-value="' +
        escapeHtml(formatChartValue(value, config.format)) +
        '"></rect>';
      svg +=
        '<text class="axis-label" x="' +
        (x + barWidth / 2) +
        '" y="' +
        (height - 14) +
        '" text-anchor="middle">' +
        escapeHtml(label) +
        "</text>";
    });

    svg += "</svg>";

    container.innerHTML =
      svg + '<div class="onepaqucpro-cr-chart-tooltip" hidden></div>';
    bindChartTooltip(container);
  }

  function renderCharts() {
    document.querySelectorAll(".onepaqucpro-cr-chart").forEach(function (chart) {
      const rawConfig = chart.getAttribute("data-chart-config");
      if (!rawConfig) {
        return;
      }

      try {
        const config = JSON.parse(rawConfig);
        if (config.type === "bar") {
          renderBarChart(chart, config);
        } else {
          renderLineChart(chart, config);
        }
      } catch (error) {
        chart.innerHTML = "";
      }
    });
  }

  function initEnhancedSelects() {
    const $ = window.jQuery;
    if (!$ || !$.fn || !$.fn.select2) {
      return;
    }

    $("[data-cr-select2]").each(function () {
      const select = this;
      const $select = $(select);
      if ($select.data("select2")) {
        return;
      }

      const minimumInputLength = Number(select.getAttribute("data-minimum-input-length") || 0);
      const placeholder = select.getAttribute("data-placeholder") || "";
      const options = {
        width: "100%",
        placeholder: placeholder,
        allowClear: true,
        closeOnSelect: false,
        minimumInputLength: minimumInputLength,
        language: {
          inputTooShort: function (args) {
            const remaining = args.minimum - args.input.length;
            return "Type " + remaining + " more character" + (remaining === 1 ? "" : "s") + " to search.";
          },
          noResults: function () {
            return "No matches found.";
          },
          searching: function () {
            return "Searching...";
          },
        },
      };

      if (select.getAttribute("data-cr-select2") === "ajax") {
        options.ajax = {
          url: window.ajaxurl || "admin-ajax.php",
          dataType: "json",
          delay: 250,
          data: function (params) {
            return {
              action: select.getAttribute("data-cr-select2-action"),
              nonce: select.getAttribute("data-cr-select2-nonce"),
              term: params.term || "",
            };
          },
          processResults: function (data) {
            return data && Array.isArray(data.results) ? data : { results: [] };
          },
          cache: true,
        };
      }

      $select.select2(options);
    });
  }

  function activateTab(tabButton, shouldFocus) {
    const tabsRoot = tabButton ? tabButton.closest("[data-cr-tabs]") : null;
    if (!tabsRoot) {
      return;
    }

    const targetPanel = tabButton.getAttribute("data-cr-tab-button");
    const tabButtons = tabsRoot.querySelectorAll("[data-cr-tab-button]");
    const tabPanels = tabsRoot.querySelectorAll("[data-cr-tab-panel]");

    tabButtons.forEach(function (button) {
      const isActive = button === tabButton;
      button.classList.toggle("is-active", isActive);
      button.setAttribute("aria-selected", isActive ? "true" : "false");
      button.setAttribute("tabindex", isActive ? "0" : "-1");
    });

    tabPanels.forEach(function (panel) {
      const isActive = panel.getAttribute("data-cr-tab-panel") === targetPanel;
      panel.classList.toggle("is-active", isActive);
      panel.hidden = !isActive;
    });

    if (shouldFocus) {
      tabButton.focus();
    }
  }

  function initializeModalTabs(scope) {
    if (!scope) {
      return;
    }

    scope.querySelectorAll("[data-cr-tabs]").forEach(function (tabsRoot) {
      const activeTab =
        tabsRoot.querySelector('[data-cr-tab-button][aria-selected="true"]') ||
        tabsRoot.querySelector("[data-cr-tab-button]");

      if (activeTab) {
        activateTab(activeTab, false);
      }
    });
  }

  function openModal(templateId) {
    if (!modal || !modalContent) {
      return;
    }

    const template = document.getElementById(templateId);
    if (!template) {
      return;
    }

    modalContent.innerHTML = template.innerHTML;
    modalContent
      .querySelectorAll(".onepaqucpro-cr-detail__identity > div")
      .forEach(function (content) {
        content
          .querySelectorAll("p:not(.onepaqucpro-cr-detail__eyebrow):not(.onepaqucpro-cr-detail__subtitle)")
          .forEach(function (duplicateLine) {
            duplicateLine.remove();
          });
      });
    initializeModalTabs(modalContent);
    modal.hidden = false;
    const dialog = modal.querySelector(".onepaqucpro-cr-modal__dialog");
    if (dialog) {
      dialog.scrollTop = 0;
    }
    body.classList.add("onepaqucpro-cr-modal-open");
  }

  function closeModal() {
    if (!modal || !modalContent) {
      return;
    }

    modal.hidden = true;
    modalContent.innerHTML = "";
    body.classList.remove("onepaqucpro-cr-modal-open");
  }

  function toggleCheckAll(checkbox) {
    const form = checkbox.closest("form");
    if (!form) {
      return;
    }

    form.querySelectorAll('tbody input[type="checkbox"]').forEach(function (item) {
      if (item !== checkbox) {
        item.checked = checkbox.checked;
      }
    });
  }

  function cleanResponsiveText(value) {
    return String(value || "").replace(/\s+/g, " ").trim();
  }

  function getResponsiveCellText(cell) {
    const clone = cell.cloneNode(true);

    clone
      .querySelectorAll(
        "script, style, input[type='hidden'], .screen-reader-text, .onepaqucpro-cr-row-toggle, .toggle-row"
      )
      .forEach(function (node) {
        node.remove();
      });

    clone.querySelectorAll("input, select, textarea, button, a").forEach(function (control) {
      let replacement = "";

      if (control.matches("input[type='checkbox']")) {
        replacement = control.checked ? "Enabled" : "Disabled";
      } else if (control.matches("input, select, textarea")) {
        replacement = control.value || control.getAttribute("value") || "";
      } else {
        replacement =
          control.getAttribute("title") ||
          control.getAttribute("aria-label") ||
          control.textContent ||
          "";
      }

      control.replaceWith(document.createTextNode(" " + replacement + " "));
    });

    return cleanResponsiveText(clone.textContent);
  }

  function getResponsiveHeaderLabels(table) {
    const headerRow = table.querySelector("thead tr:last-child");

    if (!headerRow) {
      return [];
    }

    return Array.from(headerRow.children)
      .filter(function (cell) {
        return cell.matches("th, td");
      })
      .map(function (cell) {
        return cleanResponsiveText(cell.getAttribute("data-cr-label") || cell.textContent);
      });
  }

  function getResponsiveTableType(table) {
    const form = table.closest("form");

    if (table.classList.contains("onepaqucpro-cr-table--carts")) {
      return "carts";
    }

    if (table.classList.contains("onepaqucpro-cr-template-table--list")) {
      return "templates";
    }

    if (
      table.classList.contains("onepaqucpro-cr-table--email-activity") ||
      form &&
      form.querySelector(
        'input[name="action"][value="onepaqucpro_cart_recovery_export_activity"]'
      )
    ) {
      return "activity";
    }

    return "generic";
  }

  function getResponsivePrimaryIndex(cells) {
    let index = cells.findIndex(function (cell) {
      return (
        cell.classList.contains("column-primary") ||
        cell.classList.contains("onepaqucpro-cr-email-cell")
      );
    });

    if (index !== -1) {
      return index;
    }

    index = cells.findIndex(function (cell) {
      return !cell.classList.contains("check-column");
    });

    return index === -1 ? 0 : index;
  }

  function isResponsivePinnedCell(cell, index, primaryIndex) {
    if (index === primaryIndex || cell.classList.contains("check-column")) {
      return true;
    }

    return (
      cell.classList.contains("column-status") ||
      cell.classList.contains("onepaqucpro-cr-template-actions") ||
      Boolean(cell.querySelector(".switch"))
    );
  }

  function getResponsiveHideClass(tableType, rank, candidateCount) {
    const laptop = "onepaqucpro-cr-hide-laptop";
    const tablet = "onepaqucpro-cr-hide-tablet";
    const mobile = "onepaqucpro-cr-hide-mobile";

    if (tableType === "carts") {
      if (rank <= 2) {
        return mobile;
      }

      if (rank === 3) {
        return tablet;
      }

      return laptop;
    }

    if (tableType === "activity") {
      return [mobile, tablet, laptop, mobile, tablet][rank - 1] || laptop;
    }

    if (tableType === "templates") {
      if (rank === 1) {
        return tablet;
      }

      if (rank === 2) {
        return mobile;
      }

      return laptop;
    }

    if (candidateCount <= 1) {
      return mobile;
    }

    if (rank === 1) {
      return mobile;
    }

    if (rank === 2) {
      return tablet;
    }

    return laptop;
  }

  function addResponsiveAvailabilityClass(table, hideClass) {
    if (hideClass === "onepaqucpro-cr-hide-laptop") {
      table.classList.add("has-laptop-hidden");
      return;
    }

    if (hideClass === "onepaqucpro-cr-hide-tablet") {
      table.classList.add("has-tablet-hidden");
      return;
    }

    if (hideClass === "onepaqucpro-cr-hide-mobile") {
      table.classList.add("has-mobile-hidden");
    }
  }

  function setResponsiveToggleState(button, expanded) {
    const row = button.closest("tr");
    const detailRow = document.getElementById(button.getAttribute("aria-controls"));
    const label = button.querySelector(".onepaqucpro-cr-row-toggle__label");
    const closedLabel = button.getAttribute("data-cr-toggle-label") || "Details";
    const openLabel = button.getAttribute("data-cr-toggle-open-label") || "Hide details";

    button.setAttribute("aria-expanded", expanded ? "true" : "false");
    button.classList.toggle("is-expanded", expanded);

    if (label) {
      label.textContent = expanded ? openLabel : closedLabel;
    }

    if (row) {
      row.classList.toggle("is-expanded", expanded);
    }

    if (detailRow) {
      detailRow.hidden = !expanded;
      detailRow.classList.toggle("is-expanded", expanded);
    }
  }

  function toggleResponsiveRow(button) {
    setResponsiveToggleState(button, button.getAttribute("aria-expanded") !== "true");
  }

  function initResponsiveTable(table, tableIndex) {
    if (table.getAttribute("data-cr-responsive-ready") === "1") {
      return;
    }

    let labels = getResponsiveHeaderLabels(table);
    const rows = Array.from(table.querySelectorAll("tbody > tr"));
    const sampleRow = rows.find(function (row) {
      return !row.classList.contains("onepaqucpro-cr-responsive-details");
    });

    if (!sampleRow) {
      return;
    }

    const sampleCells = Array.from(sampleRow.children).filter(function (cell) {
      return cell.matches("th, td");
    });

    if (sampleCells.length <= 1 || sampleCells.some(function (cell) { return cell.colSpan > 1; })) {
      return;
    }

    if (!labels.length) {
      labels = sampleCells.map(function (cell, index) {
        return index === 0 ? "Item" : "Details";
      });
    }

    const tableType = getResponsiveTableType(table);
    const primaryIndex = getResponsivePrimaryIndex(sampleCells);
    const headers = Array.from(table.querySelectorAll("thead tr:last-child > th, thead tr:last-child > td"));
    const hiddenByIndex = {};
    const candidateIndexes = [];

    sampleCells.forEach(function (cell, index) {
      if (!isResponsivePinnedCell(cell, index, primaryIndex)) {
        candidateIndexes.push(index);
      }
    });

    candidateIndexes.forEach(function (index, candidateIndex) {
      const hideClass = getResponsiveHideClass(tableType, candidateIndex + 1, candidateIndexes.length);
      hiddenByIndex[index] = hideClass;
      addResponsiveAvailabilityClass(table, hideClass);

      if (headers[index]) {
        headers[index].classList.add(hideClass);
      }
    });

    if (!Object.keys(hiddenByIndex).length) {
      return;
    }

    table.classList.add("onepaqucpro-cr-responsive-table");
    table.classList.add("onepaqucpro-cr-responsive-table--" + tableType);
    table.setAttribute("data-cr-responsive-ready", "1");

    rows.forEach(function (row, rowIndex) {
      if (row.classList.contains("onepaqucpro-cr-responsive-details")) {
        return;
      }

      const cells = Array.from(row.children).filter(function (cell) {
        return cell.matches("th, td");
      });

      if (cells.length <= 1 || cells.some(function (cell) { return cell.colSpan > 1; })) {
        return;
      }

      const detailItems = [];
      const primaryCell = cells[primaryIndex] || cells[0];

      row.classList.add("onepaqucpro-cr-responsive-row");

      cells.forEach(function (cell, index) {
        const label = labels[index] || "";

        if (label) {
          cell.setAttribute("data-cr-label", label);
        }

        if (cell.classList.contains("check-column")) {
          row.classList.add("has-check-column");
        }

        if (index === primaryIndex) {
          cell.classList.add("onepaqucpro-cr-responsive-primary-cell");
        }

        if (isResponsivePinnedCell(cell, index, primaryIndex)) {
          cell.classList.add("onepaqucpro-cr-responsive-pin-cell");
          if (index !== primaryIndex && !cell.classList.contains("check-column")) {
            row.classList.add("has-pinned-cells");
          }
        }

        if (!hiddenByIndex[index]) {
          return;
        }

        cell.classList.add(hiddenByIndex[index]);

        const value = getResponsiveCellText(cell);

        if (!value) {
          return;
        }

        detailItems.push(
          '<div class="onepaqucpro-cr-responsive-detail-item ' +
            hiddenByIndex[index] +
            '"><dt>' +
            escapeHtml(label || "Details") +
            "</dt><dd>" +
            escapeHtml(value) +
            "</dd></div>"
        );
      });

      if (!detailItems.length || !primaryCell) {
        return;
      }

      const detailId = "onepaqucpro-cr-responsive-" + tableIndex + "-" + rowIndex;
      let toggle = primaryCell.querySelector("[data-cr-row-toggle]");

      if (!toggle) {
        toggle = document.createElement("button");
        toggle.type = "button";
        toggle.className = "button-link onepaqucpro-cr-row-toggle";
        toggle.setAttribute("data-cr-row-toggle", "");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-controls", detailId);
        toggle.setAttribute("data-cr-toggle-label", tableType === "templates" ? "Detail" : "Details");
        toggle.setAttribute(
          "data-cr-toggle-open-label",
          tableType === "templates" ? "Hide detail" : "Hide details"
        );
        toggle.innerHTML =
          '<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span><span class="onepaqucpro-cr-row-toggle__label">' +
          escapeHtml(tableType === "templates" ? "Detail" : "Details") +
          "</span>";
        primaryCell.appendChild(toggle);
      }

      const detailRow = document.createElement("tr");
      const detailCell = document.createElement("td");

      detailRow.id = detailId;
      detailRow.hidden = true;
      detailRow.className = "onepaqucpro-cr-responsive-details";
      detailCell.colSpan = cells.length;
      detailCell.innerHTML =
        '<dl class="onepaqucpro-cr-responsive-detail-list">' + detailItems.join("") + "</dl>";
      detailRow.appendChild(detailCell);
      row.insertAdjacentElement("afterend", detailRow);
    });
  }

  function initResponsiveTables() {
    document
      .querySelectorAll(".onepaqucpro-cr-table, .onepaqucpro-cr-template-table--list")
      .forEach(function (table, tableIndex) {
        initResponsiveTable(table, tableIndex);
      });
  }

  function addTemplateRow() {
    const wrapper = document.querySelector("[data-cr-template-rows]");
    const template = document.getElementById("onepaqucpro-cr-template-row-template");

    if (!wrapper || !template) {
      return;
    }

    const nextIndex = Number(wrapper.getAttribute("data-template-index") || wrapper.children.length);
    const markup = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
    wrapper.insertAdjacentHTML("beforeend", markup);
    wrapper.setAttribute("data-template-index", String(nextIndex + 1));
  }

  function showNoteForm(button) {
    const formId = button ? button.getAttribute("data-cr-note-edit") : "";
    if (!formId) {
      return;
    }

    const form = document.getElementById(formId);
    if (!form) {
      return;
    }

    form.classList.remove("is-hidden");
    const noteField = form.querySelector('textarea[name="cart_notes"]');
    if (noteField) {
      noteField.focus();
    }
  }

  function copyText(value) {
    if (!value) {
      return Promise.reject();
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(value);
    }

    const textarea = document.createElement("textarea");
    textarea.value = value;
    textarea.setAttribute("readonly", "readonly");
    textarea.style.position = "fixed";
    textarea.style.left = "-9999px";
    document.body.appendChild(textarea);
    textarea.select();

    try {
      document.execCommand("copy");
      document.body.removeChild(textarea);
      return Promise.resolve();
    } catch (error) {
      document.body.removeChild(textarea);
      return Promise.reject(error);
    }
  }

  function replaceTemplateTags(content, tags) {
    let rendered = String(content || "");
    Object.keys(tags || {}).forEach(function (tag) {
      rendered = rendered.split(tag).join(tags[tag]);
    });
    return rendered;
  }

  function getTemplateEditorContent(form) {
    const textarea = form ? form.querySelector('textarea[name="template[message]"]') : null;
    const editorId = textarea ? textarea.id : "onepaqucpro_cr_template_message";

    const editor = window.tinymce ? window.tinymce.get(editorId) : null;
    if (editor && (typeof editor.isHidden !== "function" || !editor.isHidden())) {
      return editor.getContent();
    }

    return textarea ? textarea.value : "";
  }

  function getSelectedCartItemsLayout(form) {
    const selected = form
      ? form.querySelector('input[name="template[cart_items_layout]"]:checked')
      : null;

    return selected ? selected.value : "table";
  }

  function updateLayoutOptionState(input) {
    const group = input ? input.closest(".onepaqucpro-cr-layout-options") : null;
    if (!group) {
      return;
    }

    group.querySelectorAll(".onepaqucpro-cr-layout-option").forEach(function (option) {
      const optionInput = option.querySelector('input[type="radio"]');
      option.classList.toggle("is-selected", !!optionInput && optionInput.checked);
    });
  }

  function getPreviewTagMap(template, layout) {
    const source = template && template.content
      ? template.content.querySelector("[data-cr-template-preview-tags]")
      : null;

    if (!source) {
      return {};
    }

    try {
      const maps = JSON.parse(source.textContent || "{}");
      return maps[layout] || maps.table || {};
    } catch (error) {
      return {};
    }
  }

  function normalizePreviewBody(content) {
    const rendered = String(content || "");

    if (/<(p|ul|ol|li|div|table|h[1-6]|blockquote|a)\b/i.test(rendered)) {
      return rendered;
    }

    return "<p>" + escapeHtml(rendered).replace(/\n{2,}/g, "</p><p>").replace(/\n/g, "<br>") + "</p>";
  }

  function updateBuilderPreview(button) {
    const templateId = button ? button.getAttribute("data-template") : "";
    const template = templateId ? document.getElementById(templateId) : null;
    const form = button ? button.closest("form") : null;

    if (!template || !template.content || !form) {
      return;
    }

    const layout = getSelectedCartItemsLayout(form);
    const tags = getPreviewTagMap(template, layout);
    const nameField = form.querySelector('input[name="template[name]"]');
    const subjectField = form.querySelector('input[name="template[subject]"]');
    const headingField = form.querySelector('input[name="template[heading]"]');
    const selectedLayout = form.querySelector('input[name="template[cart_items_layout]"]:checked');
    const selectedLayoutLabel = selectedLayout
      ? selectedLayout.closest(".onepaqucpro-cr-layout-option")
      : null;
    const name = nameField && nameField.value ? nameField.value : "Untitled Email";
    const subject = subjectField && subjectField.value ? subjectField.value : "Recovery email";
    const heading = headingField && headingField.value ? headingField.value : "We saved your cart";
    const content = getTemplateEditorContent(form);
    const renderedBody = normalizePreviewBody(replaceTemplateTags(content, tags));

    const nameTarget = template.content.querySelector("[data-cr-template-preview-name]");
    const subjectTarget = template.content.querySelector("[data-cr-template-preview-subject]");
    const headingTarget = template.content.querySelector("[data-cr-template-preview-heading]");
    const frameTarget = template.content.querySelector("[data-cr-template-preview-frame]");
    const layoutTarget = template.content.querySelector("[data-cr-template-preview-layout]");

    if (nameTarget) {
      nameTarget.textContent = name;
    }

    if (subjectTarget) {
      subjectTarget.textContent = replaceTemplateTags(subject, tags).replace(/<[^>]*>/g, "");
    }

    if (headingTarget) {
      headingTarget.textContent = "Heading: " + replaceTemplateTags(heading, tags).replace(/<[^>]*>/g, "");
    }

    if (frameTarget) {
      frameTarget.innerHTML = renderedBody || "No email body is available for this template.";
    }

    if (layoutTarget && selectedLayoutLabel) {
      const label = selectedLayoutLabel.querySelector("strong");
      layoutTarget.textContent = label ? label.textContent : layout;
    }
  }

  document.addEventListener("click", function (event) {
    const confirmTarget = event.target.closest("[data-cr-confirm]");
    if (confirmTarget && !window.confirm(confirmTarget.getAttribute("data-cr-confirm"))) {
      event.preventDefault();
      return;
    }

    const copyButton = event.target.closest("[data-cr-copy]");
    if (copyButton) {
      event.preventDefault();
      const label = copyButton.querySelector("[data-cr-copy-label]");
      const originalLabel = label ? label.textContent : "";
      copyText(copyButton.getAttribute("data-cr-copy"))
        .then(function () {
          if (label) {
            label.textContent = "Copied";
            window.setTimeout(function () {
              label.textContent = originalLabel;
            }, 1600);
          }
        })
        .catch(function () {
          if (label) {
            label.textContent = "Copy failed";
            window.setTimeout(function () {
              label.textContent = originalLabel;
            }, 1600);
          }
        });
      return;
    }

    const openButton = event.target.closest(".onepaqucpro-cr-open-modal");
    if (openButton) {
      event.preventDefault();
      if (openButton.hasAttribute("data-cr-template-builder-preview")) {
        updateBuilderPreview(openButton);
      }
      openModal(openButton.getAttribute("data-template"));
      return;
    }

    const responsiveToggle = event.target.closest("[data-cr-row-toggle]");
    if (responsiveToggle) {
      event.preventDefault();
      toggleResponsiveRow(responsiveToggle);
      return;
    }

    if (event.target.closest("[data-cr-modal-close]")) {
      event.preventDefault();
      closeModal();
      return;
    }

    if (event.target.closest("[data-cr-template-add-row]")) {
      event.preventDefault();
      addTemplateRow();
      return;
    }

    const tabButton = event.target.closest("[data-cr-tab-button]");
    if (tabButton) {
      event.preventDefault();
      activateTab(tabButton, true);
      return;
    }

    const noteEditButton = event.target.closest("[data-cr-note-edit]");
    if (noteEditButton) {
      event.preventDefault();
      showNoteForm(noteEditButton);
      return;
    }

    const removeButton = event.target.closest("[data-cr-template-remove]");
    if (removeButton) {
      event.preventDefault();
      const row = removeButton.closest("tr");
      if (row) {
        row.remove();
      }
    }
  });

  document.addEventListener("change", function (event) {
    if (event.target.matches('.onepaqucpro-cr-layout-option input[type="radio"]')) {
      updateLayoutOptionState(event.target);
      return;
    }

    if (event.target.matches("[data-cr-check-all]")) {
      toggleCheckAll(event.target);
      return;
    }

    if (event.target.matches("[data-cr-template-autosave]")) {
      const form = event.target.closest("form");
      if (form) {
        form.classList.add("is-autosaving");
        form.submit();
      }
    }
  });

  document.addEventListener("submit", function (event) {
    if (!event.target.matches("[data-cr-note-delete]")) {
      return;
    }

    if (!window.confirm("Delete this saved note? Tags will be kept.")) {
      event.preventDefault();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.target && event.target.matches("[data-cr-tab-button]")) {
      const tabsRoot = event.target.closest("[data-cr-tabs]");
      const tabButtons = tabsRoot ? Array.from(tabsRoot.querySelectorAll("[data-cr-tab-button]")) : [];
      const currentIndex = tabButtons.indexOf(event.target);

      if (tabButtons.length && ["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) {
        event.preventDefault();

        if (event.key === "Home") {
          activateTab(tabButtons[0], true);
          return;
        }

        if (event.key === "End") {
          activateTab(tabButtons[tabButtons.length - 1], true);
          return;
        }

        const direction = event.key === "ArrowLeft" ? -1 : 1;
        const nextIndex = (currentIndex + direction + tabButtons.length) % tabButtons.length;
        activateTab(tabButtons[nextIndex], true);
        return;
      }
    }

    if (event.key === "Escape") {
      closeModal();
    }
  });

  initEnhancedSelects();
  initResponsiveTables();
  renderCharts();
});
