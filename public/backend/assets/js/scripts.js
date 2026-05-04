(function($) {
    "use strict";

    if (!$) {
        return;
    }

    /*================================
    Preloader
    ==================================*/
    var preloader = $('#preloader');
    $(window).on('load', function() {
        setTimeout(function() {
            preloader.fadeOut('slow');
			$(".staff-menu").fadeIn();
        }, 300)
    });

    /*================================
    Sidebar collapsing
    ==================================*/
    if (window.innerWidth <= 1364) {
        $('.page-container').addClass('sbar_collapsed');
    }
    $('.nav-btn').on('click', function() {
    	$('.page-container').toggleClass('sbar_collapsed');
    });


	/*================================
    Active Sidebar Menu
    ==================================*/
    var currentPath = window.location.pathname;
    $("#menu li a").each(function () {
        var href = $(this).attr("href");
        if (!href || href === "#" || href.indexOf("javascript:") === 0) return;
        var linkPath;
        try {
            linkPath = href.indexOf("http") === 0 ? new URL(href).pathname : (href.split("?")[0] || "");
        } catch (e) {
            linkPath = href.split("?")[0] || "";
        }
        if (currentPath === linkPath) {
            $("#menu li").removeClass("active-nav");
            $(this).parent().addClass("active-nav");

            // Add 'active' class to all parent menu items to keep them expanded
            $(this).parents("li").addClass("active");

            // Also add 'in' class to parent ul elements to keep submenus visible
            $(this).parent().parent().addClass("in");
        }
    });

    /*================================
    Init Tooltip
    ==================================*/

    $('[data-toggle="tooltip"]').tooltip();

    /*================================
    User Profile Dropdown Toggle
    ==================================*/
    $('.user-name.dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).toggleClass('show');
        $(this).siblings('.dropdown-menu').toggleClass('show');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.user-profile').length) {
            $('.user-profile .dropdown-menu').removeClass('show');
            $('.user-name.dropdown-toggle').removeClass('show');
        }
    });

    // Prevent dropdown from closing when clicking inside
    $('.user-profile .dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });

    function getTabStateStorage() {
        try {
            return window.localStorage;
        } catch (e) {
            return null;
        }
    }

    function getTabStateKey($nav) {
        if (!$nav || !$nav.length) return '';
        return String($nav.data('tabStateKey') || '').trim();
    }

    function buildStoredTabKey($nav) {
        var navKey = getTabStateKey($nav);
        if (!navKey) return '';

        return 'cavic:tabs:' + window.location.pathname + ':' + navKey;
    }

    function storeActiveTab($trigger) {
        if (!$trigger || !$trigger.length) return;

        var storage = getTabStateStorage();
        if (!storage) return;

        var $nav = $trigger.closest('[data-tab-state-key]');
        var storageKey = buildStoredTabKey($nav);
        var target = $trigger.attr('href');

        if (!storageKey || !target || target.charAt(0) !== '#') return;

        storage.setItem(storageKey, target);
    }

    function getStoredTab($nav) {
        var storage = getTabStateStorage();
        var storageKey = buildStoredTabKey($nav);

        if (!storage || !storageKey) return '';

        return storage.getItem(storageKey) || '';
    }

    function syncTopStripTabs(target, $topStrip) {
        if (!target) return;

        var $tabs = $topStrip && $topStrip.length ? $topStrip.find('.admin-dashboard-top-tab') : $('.admin-dashboard-top-tab');
        if (!$tabs.length || $tabs.filter('[href="' + target + '"]').length === 0) return;

        $tabs.removeClass('active');
        $tabs.filter('[href="' + target + '"]').addClass('active');
    }

    function getTabTargetFromUrl() {
        var hash = window.location.hash || '';
        if (hash) return hash;

        try {
            var params = new URLSearchParams(window.location.search);
            var queryTab = params.get('tab');
            return queryTab ? ('#' + queryTab) : '';
        } catch (e) {
            return '';
        }
    }

    function buildTabUrl(target) {
        var nextUrl = window.location.pathname;

        try {
            var params = new URLSearchParams(window.location.search);
            params.delete('tab');
            var query = params.toString();
            nextUrl += query ? ('?' + query) : '';
        } catch (e) {
            nextUrl += window.location.search;
        }

        return nextUrl + target;
    }

    function updateTabUrl(target) {
        if (!target || target.charAt(0) !== '#') return;

        var nextUrl = buildTabUrl(target);
        var currentUrl = window.location.pathname + window.location.search + window.location.hash;
        if (currentUrl === nextUrl) return;

        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, '', nextUrl);
        } else {
            window.location.hash = target;
        }
    }

    function activateTabFromLocation() {
        var target = getTabTargetFromUrl();
        if (!target) return;

        var $topStrip = $('.admin-dashboard-top-tabs[data-tab-state-key]').first();
        var $topStripTrigger = $topStrip.find('a[data-toggle="tab"][href="' + target + '"]').first();
        var $trigger = $topStripTrigger.length ? $topStripTrigger : $('a[data-toggle="tab"][href="' + target + '"]').first();

        if ($trigger.length) {
            if (!$trigger.hasClass('active')) {
                $trigger.tab('show');
            } else {
                syncTopStripTabs(target, $trigger.closest('.admin-dashboard-top-tabs'));
            }
        } else {
            syncTopStripTabs(target, $topStrip);
        }
    }

    function activateStoredTabs() {
        $('[data-tab-state-key]').each(function() {
            var $nav = $(this);
            var targetFromUrl = getTabTargetFromUrl();
            var storedTarget = getStoredTab($nav);

            if (!storedTarget || storedTarget === targetFromUrl) {
                return;
            }

            if ($nav.find('[href="' + targetFromUrl + '"]').length > 0) {
                return;
            }

            var $trigger = $nav.find('a[data-toggle="tab"][href="' + storedTarget + '"]').first();
            if ($trigger.length && !$trigger.hasClass('active')) {
                $trigger.tab('show');
                return;
            }

            if ($nav.hasClass('admin-dashboard-top-tabs')) {
                syncTopStripTabs(storedTarget, $nav);
            }
        });
    }

    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function(e) {
        var $trigger = $(e.target);
        var target = $(e.target).attr('href');
        var $topStrip = $trigger.closest('.admin-dashboard-top-tabs');

        storeActiveTab($trigger);

        if ($topStrip.length) {
            syncTopStripTabs(target, $topStrip);
            updateTabUrl(target);
        } else if ($('.admin-dashboard-top-tabs').length === 0) {
            updateTabUrl(target);
        }

        syncFirstTabStats();
    });

    function syncFirstTabStats(instant) {
        $('.workspace-first-tab-stats').each(function() {
            var $stats = $(this);
            var target = $stats.data('tab');
            if (!target) return;

            var isActive = $('a[data-toggle="tab"].active[href="' + target + '"]').length > 0;

            if (instant) {
                $stats.stop(true, true).css('opacity', 1).toggle(isActive);
                return;
            }

            if (isActive) {
                if (!$stats.is(':visible')) {
                    $stats
                        .stop(true, true)
                        .css({ opacity: 0, display: 'none' })
                        .slideDown(180)
                        .animate({ opacity: 1 }, { duration: 180, queue: false });
                }
            } else if ($stats.is(':visible')) {
                $stats
                    .stop(true, true)
                    .animate({ opacity: 0 }, { duration: 140, queue: false })
                    .slideUp(180);
            }
        });
    }

    $(document).on('click', '.admin-dashboard-top-tab:not([data-toggle="tab"])', function(e) {
        var $trigger = $(this);
        var target = $trigger.attr('href');
        var $topStrip = $trigger.closest('.admin-dashboard-top-tabs');

        storeActiveTab($trigger);
        syncTopStripTabs(target, $topStrip);

        if (target && target.charAt(0) === '#' && $(target).hasClass('tab-pane')) {
            e.preventDefault();
            $trigger.tab('show');
            return;
        }

        updateTabUrl(target);
    });

    $(window).on('popstate hashchange', function() {
        activateTabFromLocation();
        activateStoredTabs();
        syncFirstTabStats();
    });

    activateTabFromLocation();
    activateStoredTabs();
    syncFirstTabStats(true);

    window.cavicAdminDataTable = function(selector, config) {
        if (typeof $.fn.DataTable === 'undefined') {
            return null;
        }

        var $table = $(selector);
        if (!$table.length) {
            return null;
        }

        var exportFilename = $table.data('exportFilename') || document.title || 'Export';
        var hasButtons = !!($.fn.DataTable.Buttons);
        var exportColumnSelector = function(idx, data, node) {
            return $(node).attr('data-no-export') !== '1';
        };

        var defaultButtons = hasButtons ? [
            {
                extend: 'colvis',
                text: '<i class="ti-layout-column2"></i><span>Columns</span>',
                className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                columns: exportColumnSelector
            },
            {
                extend: 'excel',
                text: '<i class="ti-download"></i><span>Excel</span>',
                className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                filename: exportFilename,
                title: exportFilename,
                exportOptions: { columns: exportColumnSelector }
            },
            {
                extend: 'csv',
                text: '<i class="ti-download"></i><span>CSV</span>',
                className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                filename: exportFilename,
                exportOptions: { columns: exportColumnSelector }
            },
            {
                extend: 'pdf',
                text: '<i class="ti-download"></i><span>PDF</span>',
                className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                filename: exportFilename,
                title: exportFilename,
                exportOptions: { columns: exportColumnSelector }
            },
            {
                extend: 'print',
                text: '<i class="ti-printer"></i><span>Print</span>',
                className: 'btn btn-xs admin-dt-btn admin-dt-btn-ghost',
                title: exportFilename,
                exportOptions: { columns: exportColumnSelector }
            }
        ] : [];

        var userDrawCallback = config && config.drawCallback;
        var userInitComplete = config && config.initComplete;

        var defaults = {
            responsive: true,
            bStateSave: true,
            bAutoWidth: false,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: hasButtons
                ? "<'admin-datatable-top d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3'<'admin-datatable-top-left d-flex flex-wrap align-items-center gap-2'B l><'admin-datatable-top-right d-flex flex-wrap align-items-center justify-content-xl-end gap-2'f>>" +
                  "<'admin-datatable-table-wrap't>" +
                  "<'admin-datatable-bottom d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3'<'admin-datatable-bottom-left'i><'admin-datatable-bottom-right'p>>"
                : "<'admin-datatable-top d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3'<'admin-datatable-top-left'l><'admin-datatable-top-right'f>>" +
                  "<'admin-datatable-table-wrap't>" +
                  "<'admin-datatable-bottom d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3'<'admin-datatable-bottom-left'i><'admin-datatable-bottom-right'p>>",
            buttons: defaultButtons,
            language: window.cavicDataTableLanguage || {},
            drawCallback: function() {
                var $wrapper = $table.closest('.dataTables_wrapper');
                $wrapper.addClass('admin-datatable-wrapper');
                $table.closest('.table-responsive').addClass('admin-datatable-responsive');
                $wrapper.find('.dataTables_paginate > .pagination').addClass('pagination-bordered');
                $wrapper.find('.dataTables_filter input').attr('placeholder', 'Search records');
                if (typeof TableExportTotals !== 'undefined') TableExportTotals.computeTotals();
                if (typeof userDrawCallback === 'function') {
                    userDrawCallback.apply(this, arguments);
                }
            },
            initComplete: function() {
                var $wrapper = $table.closest('.dataTables_wrapper');
                $wrapper.addClass('admin-datatable-wrapper');
                $table.closest('.table-responsive').addClass('admin-datatable-responsive');
                $wrapper.find('.dataTables_filter input').attr('placeholder', 'Search records');
                $wrapper.find('.dt-buttons').addClass('admin-dt-buttons-ready');
                if (typeof userInitComplete === 'function') {
                    userInitComplete.apply(this, arguments);
                }
            }
        };

        var finalConfig = $.extend(true, {}, defaults, config || {});

        if (config && Object.prototype.hasOwnProperty.call(config, 'buttons')) {
            finalConfig.buttons = config.buttons;
        }

        return $table.DataTable(finalConfig);
    };

	/*================================
    Hide Empty Menu
    ==================================*/
	$("#menu li").each(function(){
		var elem = $(this);
		if($(elem).has("ul").length > 0){
			if($(elem).find("ul").has("li").length === 0){
				$(elem).remove();
			}
		}
	});

    /*================================
    sidebar menu
    ==================================*/
    $("#menu").metisMenu({
        toggle: false  // Prevent automatic collapse of parent menus
    });

    /*================================
    slimscroll activation
    ==================================*/
	if(jQuery().slimscroll) {
		$('.nofity-list').slimScroll({
			height: '435px'
		});

		$('.timeline-area').slimScroll({
			height: '500px'
		});

		$('.recent-activity').slimScroll({
			height: 'calc(100vh - 114px)'
		});

		$('.settings-list').slimScroll({
			height: 'calc(100vh - 158px)'
		});

		$('.crm-scroll').slimscroll({
			railVisible: true,
			railColor: '#7f8c8d',
			height: '500px',
			alwaysVisible: true,
		});

		$('.card-scroll').slimscroll({
			railColor: '#7f8c8d',
			height: '400px',
		});

		$("#kanban-view .cards").slimscroll({
			railVisible: true,
			railColor: '#7f8c8d',
			height: '500px',
		});
	}

    /*================================
    stickey Header
    ==================================*/
    $(window).on('scroll', function() {
        var scroll = $(window).scrollTop(),
            mainHeader = $('#sticky-header'),
            mainHeaderHeight = mainHeader.innerHeight();

        // console.log(mainHeader.innerHeight());
        if (scroll > 1) {
            $("#sticky-header").addClass("sticky-menu");
        } else {
            $("#sticky-header").removeClass("sticky-menu");
        }
    });

    /*================================
    form bootstrap validation
    ==================================*/
    $('[data-toggle="popover"]').popover()

    /*------------- Start form Validation -------------*/
    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);


    /*================================
    			login form
    ==================================*/
    $('.form-gp input').on('focus', function() {
        $(this).parent('.form-gp').addClass('focused');
    });
    $('.form-gp input').on('focusout', function() {
        if ($(this).val().length === 0) {
            $(this).parent('.form-gp').removeClass('focused');
        }
    });

    /*================================
      slider-area background setting
    ==================================*/
    $('.settings-btn, .offset-close').on('click', function() {
        $('.offset-area').toggleClass('show_hide');
        $('.settings-btn').toggleClass('active');
    });

    /*================================
    		Fullscreen Page
    ==================================*/
	if ($('#full-view').length) {

		var requestFullscreen = function (ele) {
			if (ele.requestFullscreen) {
				ele.requestFullscreen();
			} else if (ele.webkitRequestFullscreen) {
				ele.webkitRequestFullscreen();
			} else if (ele.mozRequestFullScreen) {
				ele.mozRequestFullScreen();
			} else if (ele.msRequestFullscreen) {
				ele.msRequestFullscreen();
			} else {
				console.log('Fullscreen API is not supported.');
			}
		};

		var exitFullscreen = function () {
			if (document.exitFullscreen) {
				document.exitFullscreen();
			} else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			} else {
				console.log('Fullscreen API is not supported.');
			}
		};

		var fsDocButton = document.getElementById('full-view');
		var fsExitDocButton = document.getElementById('full-view-exit');

		fsDocButton.addEventListener('click', function (e) {
			e.preventDefault();
			requestFullscreen(document.documentElement);
			$('body').addClass('expanded');
		});

		fsExitDocButton.addEventListener('click', function (e) {
			e.preventDefault();
			exitFullscreen();
			$('body').removeClass('expanded');
		});
	}

	//App Js
	$(document).ajaxStart(function () {
		Pace.restart();
	});

	$(document).on('submit', 'form', function() {
	    $(this).find(":submit").prop('disabled', true);
	});

	$(document).on('click', '.btn-remove-2', function () {
		var link = $(this).attr('href');
		var message = $(this).data("message");
		$('.table-row-actions.show > [data-toggle="dropdown"]').dropdown('hide');
		//Sweet Alert for delete action
		Swal.fire({
			title: $lang_alert_title,
			text: message ?? $lang_alert_message,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: $lang_confirm_button_text,
			cancelButtonText: $lang_cancel_button_text
		}).then((result) => {
			if (result.value) {
				window.location.href = link;
			}
		});

		return false;
	});

	$(document).on('click', '.btn-remove', function () {
		if ($(this).closest('form').hasClass('ajax-remove')) {
			return true;
		}

		var message = $(this).data("message");
		$('.table-row-actions.show > [data-toggle="dropdown"]').dropdown('hide');
		//Sweet Alert for delete action
		Swal.fire({
			title: $lang_alert_title,
			text: message ?? $lang_alert_message,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: $lang_confirm_button_text,
			cancelButtonText: $lang_cancel_button_text
		}).then((result) => {
			if (result.value) {
				$(this).closest('form').submit();
			}
		});

		return false;
	});

	function positionFloatingTableActionMenu($dropdown) {
		var $menu = $dropdown.data('tableActionFloatingMenu') || $dropdown.children('.dropdown-menu').first();
		var $toggle = $dropdown.children('[data-toggle="dropdown"]').first();

		if (!$menu.length || !$toggle.length || !$toggle[0].getBoundingClientRect) {
			return;
		}

		var $placeholder = $dropdown.data('tableActionMenuPlaceholder');

		if (!$placeholder || !$placeholder.length) {
			$placeholder = $('<span class="table-action-menu-placeholder d-none"></span>');
			$menu.after($placeholder);
			$dropdown.data('tableActionMenuPlaceholder', $placeholder);
		}

		$dropdown.data('tableActionFloatingMenu', $menu);
		$menu
			.appendTo(document.body)
			.addClass('table-action-menu-floating show')
			.attr('data-floating-table-action', 'true');

		var toggleRect = $toggle[0].getBoundingClientRect();
		var viewportWidth = window.innerWidth || document.documentElement.clientWidth;
		var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
		var gap = 8;
		var edge = 12;
		var menuWidth = $menu.outerWidth();
		var menuHeight = $menu.outerHeight();
		var left = toggleRect.right - menuWidth;
		var top = toggleRect.bottom + gap;

		if (left < edge) {
			left = edge;
		}

		if (left + menuWidth > viewportWidth - edge) {
			left = Math.max(edge, viewportWidth - edge - menuWidth);
		}

		if (top + menuHeight > viewportHeight - edge && toggleRect.top - menuHeight - gap > edge) {
			top = toggleRect.top - menuHeight - gap;
		} else if (top + menuHeight > viewportHeight - edge) {
			top = Math.max(edge, viewportHeight - edge - menuHeight);
		}

		$menu.css({
			position: 'fixed',
			top: top + 'px',
			left: left + 'px',
			right: 'auto',
			bottom: 'auto',
			transform: 'none',
			zIndex: 6500
		});
	}

	function restoreFloatingTableActionMenu($dropdown) {
		var $menu = $dropdown.data('tableActionFloatingMenu');
		var $placeholder = $dropdown.data('tableActionMenuPlaceholder');

		if ($menu && $menu.length && $placeholder && $placeholder.length) {
			$placeholder.before($menu);
			$placeholder.remove();
		}

		if ($menu && $menu.length) {
			$menu
				.removeClass('table-action-menu-floating')
				.removeAttr('data-floating-table-action')
				.removeAttr('style');
		}

		$dropdown.removeData('tableActionFloatingMenu tableActionMenuPlaceholder');
	}

	$(document).on('show.bs.dropdown', '.table-row-actions', function () {
		$('.table-row-actions.show').not(this).children('[data-toggle="dropdown"]').dropdown('hide');

		$(this)
			.parents('.table-responsive, .admin-datatable-table-wrap')
			.addClass('table-dropdown-open');

		$(this)
			.closest('.card, .member-compact-card, .workspace-section-card, .dashboard-proof-datatable-card, .cavic-datatable-card')
			.addClass('table-dropdown-open-card');

		$(this)
			.closest('.modal-body, .modal-content')
			.addClass('table-dropdown-open-modal');
	});

	$(document).on('shown.bs.dropdown', '.table-row-actions', function () {
		positionFloatingTableActionMenu($(this));
	});

	$(window).on('scroll resize', function () {
		$('.table-row-actions.show').each(function () {
			positionFloatingTableActionMenu($(this));
		});
	});

	$(document).on('hidden.bs.dropdown', '.table-row-actions', function () {
		restoreFloatingTableActionMenu($(this));

		$(this)
			.parents('.table-responsive, .admin-datatable-table-wrap')
			.removeClass('table-dropdown-open');

		$(this)
			.closest('.card, .member-compact-card, .workspace-section-card, .dashboard-proof-datatable-card, .cavic-datatable-card')
			.removeClass('table-dropdown-open-card');

		$(this)
			.closest('.modal-body, .modal-content')
			.removeClass('table-dropdown-open-modal');
	});

	$(document).on('click','.toggle-optional-fields', function(e){
		e.preventDefault();

		$(".optional-field").toggleClass('show');
		var label = $(this).data('toggle-title');
		$(this).data('toggle-title', $(this).html());
		$(this).html(label);
	});

	if ($(".select2").length) {
        $(".select2").each(function (i, obj) {
            $(this).select2({
                placeholder: $(this).data('placeholder') ?? null,
            });
        });
    }


	/** Init Datepicker **/
	init_datepicker();


	$('.dropify').dropify();

	/** Init DateTimepicker **/
	$('.datetimepicker').daterangepicker({
		timePicker: true,
		timePicker24Hour: true,
		singleDatePicker: true,
		showDropdowns: true,
		locale: {
			format: 'YYYY-MM-DD HH:mm'
		}
	});

	/** Init Timepicker **/
	$('.timepicker').daterangepicker({
			timePicker : true,
			singleDatePicker:true,
			timePicker24Hour : true,
			timePickerIncrement : 1,
			timePickerSeconds : false,
			locale : {
				format : 'HH:mm'
			}
		}).on('show.daterangepicker', function(ev, picker) {
			picker.container.find(".calendar-table").hide();
	});


	//Form validation
	if ($('.validate').length) {
		$('.validate').parsley();
	}

	init_editor();

	$(".float-field").on('keypress', function (event) {
		if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
			(event.which < 48 || event.which > 57)) {
			event.preventDefault();
		}
	});

	$(".int-field").on('keypress', function (event) {
		if ((event.which < 48 || event.which > 57)) {
			event.preventDefault();
		}
	});

	$(document).on('click', '#modal-fullscreen', function () {
		$("#main_modal >.modal-dialog").toggleClass("fullscreen-modal");
	});

	$(document).on('click', '#close_alert', function () {
		$("#main_alert").fadeOut();
	});


	//File Upload Field
	$(".file-uploader").after("<input type='text' class='form-control filename' readOnly>"
		+ "<button type='button' class='btn btn-primary file-uploader-btn'>Browse</button>");

	$(".file-uploader").each(function () {
		if ($(this).data("placeholder")) {
			$(this).parent().find(".filename").prop('placeholder', $(this).data("placeholder"));
		}
		if ($(this).data("value")) {
			$(this).parent().find(".filename").val($(this).data("value"));
		}
		if ($(this).attr("required")) {
			$(this).parent().find(".filename").prop("required", true);
		}
	});

	$(document).on("click", ".file-uploader-btn", function () {
		$(this).parent().find("input[type=file]").click();
	});

	$(document).on('change', '.file-uploader', function () {
		readFileURL(this);
	});


	if (
		$("input:required, select:required, textarea:required")
		.closest(".form-group")
		.find(".required").length == 0
	) {
		// INITIALIZATION REQUIRED FIELDS SIGN
		$("input:required, select:required, textarea:required, file:required")
		.closest(".form-group, .row")
		.find("label.form-label, label.col-form-label, label.control-label")
		.append("<span class='required'> *</span>");
	}


	//Print Command
	$(document).on('click', '.print', function (event) {
		event.preventDefault();
		$("#preloader").css("display", "block");
		var div = "#" + $(this).data("print");
		$(div).print({
			timeout: 1000,
		});
	});

	//Ajax Select2
	if ($(".select2-ajax").length) {
		$('.select2-ajax').each(function (i, obj) {
			var display2 = "";
			var divider = "";
			var modal = "ajax-modal-2";

			if (typeof $(this).data('display2') !== "undefined") {
				display2 = "&display2=" + $(this).data('display2');
			}

			if (typeof $(this).data('divider') !== "undefined") {
				divider = "&divider=" + $(this).data('divider');
			}

			if(typeof $(this).data('modal') !== "undefined"){
				modal = $(this).data('modal');
			}

			$(this).select2({
				//theme: "classic",
				allowClear: true,
				placeholder: typeof $(this).data('placeholder') !== "undefined" ? $(this).data('placeholder') : $lang_select_one,
				ajax: {
					url: _tenant_url + '/ajax/get_table_data?table=' + $(this).data('table') + '&value=' + $(this).data('value') + '&display=' + $(this).data('display') + display2 + divider + '&where=' + $(this).data('where'),
					processResults: function (data) {
						return {
							results: data
						};
					}
				}
			}).on('select2:open', () => {
				target_select = $(this); // First Level
				if(typeof $(this).data('href') !== "undefined"){
					$(".select2-results:not(:has(a))").append('<p class="border-top m-0 p-2"><a class="'+ modal +'" href="'+ $(this).data('href') +'" data-title="'+ $(this).data('title') +'" data-reload="false"><i class="fas fa-plus-circle mr-1"></i>'+ $lang_add_new +'</a></p>');
				}
			});

		});
	}

	//Ajax Modal Function
	var previous_select;
	var target_select;
	$(document).on("click", ".ajax-modal", function () {
		var link = $(this).data("href");
		if (typeof link == 'undefined') {
			link = $(this).attr("href");
		}

		var title = $(this).data("title");
		var fullscreen = $(this).data("fullscreen");
		var size = $(this).data("size");
		var reload = $(this).data("reload");

		$.ajax({
			url: link,
			beforeSend: function () {
				$("#preloader").css("display", "block");
			}, success: function (data) {
				$("#preloader").css("display", "none");
				$('#main_modal .modal-title').html(title);
				$('#main_modal .modal-body').html(data);
				$("#main_modal .alert-primary").addClass('d-none');
				$("#main_modal .alert-danger").addClass('d-none');
				$('#main_modal').modal('show');

				if (fullscreen == true) {
					$("#main_modal >.modal-dialog").removeClass("modal-sm modal-lg modal-xl").addClass("fullscreen-modal");
				} else if (size) {
					$("#main_modal >.modal-dialog").removeClass("fullscreen-modal modal-sm modal-lg modal-xl").addClass("modal-" + size);
				} else {
					$("#main_modal >.modal-dialog").removeClass("fullscreen-modal");
				}

				if (reload == false) {
					target_select.select2('close');
					$("#main_modal .ajax-submit, #main_modal .ajax-screen-submit").attr('data-reload', false);
				}

				//init Essention jQuery Library
				if ($('#main_modal .ajax-submit').length) {
					$('#main_modal .ajax-submit').parsley();
				}

				if ($('#main_modal .ajax-screen-submit').length) {
					$('#main_modal .ajax-screen-submit').parsley();
				}

				init_editor();

				/** Init Datepicker **/
				init_datepicker('#main_modal');

				/** Init Colorpicker **/
				if($('#main_modal .color-picker').length){
					$('#main_modal .color-picker').colorpicker();
				}

				/** Init DateTimepicker **/
				$('#main_modal .datetimepicker').daterangepicker({
					timePicker: true,
					timePicker24Hour: true,
					singleDatePicker: true,
					showDropdowns: true,
					locale: {
						format: 'YYYY-MM-DD HH:mm'
					}
				});

				/** Init Timepicker **/
				$('#main_modal .timepicker').daterangepicker({
						timePicker : true,
						singleDatePicker:true,
						timePicker24Hour : true,
						timePickerIncrement : 1,
						timePickerSeconds : false,
						locale : {
							format : 'HH:mm'
						}
					}).on('show.daterangepicker', function(ev, picker) {
						picker.container.find(".calendar-table").hide();
				});


				$(".float-field").keypress(function (event) {
					if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
						(event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});

				$(".int-field").keypress(function (event) {
					if ((event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});

				//Select2
				$("#main_modal select.select2").select2({
					//theme: "classic",
					dropdownParent: $("#main_modal .modal-content")
				});

				//Ajax Select2
				if ($("#main_modal .select2-ajax").length) {
					$('#main_modal .select2-ajax').each(function (i, obj) {

						var display2 = "";
						var divider = "";
						if (typeof $(this).data('display2') !== "undefined") {
							display2 = "&display2=" + $(this).data('display2');
						}

						if (typeof $(this).data('divider') !== "undefined") {
							divider = "&divider=" + $(this).data('divider');
						}

						$(this).select2({
							//theme: "classic",
							placeholder: $lang_select_one,
							ajax: {
								url: _tenant_url + '/ajax/get_table_data?table=' + $(this).data('table') + '&value=' + $(this).data('value') + '&display=' + $(this).data('display') + display2 + divider + '&where=' + $(this).data('where'),
								processResults: function (data) {
									return {
										results: data
									};
								}
							},
							dropdownParent: $("#main_modal .modal-content")
						}).on('select2:open', () => {
							if(target_select != null && previous_select == null){
								previous_select = target_select;
							}
							target_select = $(this); // 2nd level

							$(".select2-results:not(:has(a))").append('<p class="border-top m-0 p-2"><a class="ajax-modal-2" href="'+ $(this).data('href') +'" data-title="'+ $(this).data('title') +'" data-reload="false"><i class="fas fa-plus-circle mr-1"></i>'+ $lang_add_new +'</a></p>');
						});;

					});
				}

				//Auto Selected
				if ($("#main_modal .auto-select").length) {
					$('#main_modal .auto-select').each(function (i, obj) {
						$(this).val($(this).data('selected')).trigger('change');
					})
				}

				$("#main_modal .dropify").dropify();

				// INITIALIZATION REQUIRED FIELDS SIGN
				$("#main_modal .ajax-submit input:required, #main_modal .ajax-submit select:required, #main_modal .ajax-submit textarea:required")
					.closest(".form-group")
					.find("label.form-label, label.col-form-label, label.control-label")
					.append("<span class='required'> *</span>");

				$("#main_modal .ajax-screen-submit input:required, #main_modal .ajax-screen-submit select:required, #main_modal .ajax-screen-submit textarea:required")
					.closest(".form-group")
					.find("label.form-label, label.col-form-label, label.control-label")
					.append("<span class='required'> *</span>");
			},
			error: function (request, status, error) {
				$("#preloader").css("display", "none");
				var message = request.responseJSON && request.responseJSON.message
					? request.responseJSON.message
					: (request.responseText ? request.responseText.replace(/(<([^>]+)>)/ig, "") : (error || 'Error'));

				$("#main_modal .alert-danger").html("<span>" + message + "</span>");
				$("#main_modal .alert-primary").addClass('d-none');
				$("#main_modal .alert-danger").removeClass('d-none');
				$.toast({
					text: message,
					showHideTransition: 'slide',
					icon: 'error',
					position: 'top-right'
				});
				console.log(request.responseText);
			}
		});

		return false;
	});

	$(document).on("click", ".ajax-action", function () {
		var link = $(this).data("href");
		if (typeof link == 'undefined') {
			link = $(this).attr("href");
		}

		var confirmMessage = $(this).data("confirm");
		if (confirmMessage && ! window.confirm(confirmMessage)) {
			return false;
		}

		$.ajax({
			url: link,
			beforeSend: function () {
				$("#preloader").css("display", "block");
			},
			success: function (data) {
				$("#preloader").css("display", "none");

				if (typeof data === "string") {
					try {
						data = JSON.parse(data);
					} catch (e) {
						window.location.reload();
						return;
					}
				}

				if (data.result == "success" || data.result == "info") {
					$.toast({
						text: data.message,
						showHideTransition: 'slide',
						icon: data.result == "success" ? 'success' : 'info',
						position: 'top-right'
					});
					window.setTimeout(function () { window.location.reload() }, 500);
				} else {
					$.toast({
						text: data.message || 'Error',
						showHideTransition: 'slide',
						icon: 'error',
						position: 'top-right'
					});
				}
			},
			error: function (request, status, error) {
				$("#preloader").css("display", "none");
				console.log(request.responseText);
			}
		});

		return false;
	});

	function syncMemberCreatePortalFields($form) {
		var enabled = $form.find('.member-create-client-login').is(':checked');
		$form.find('.member-create-client-login-card input, .member-create-client-login-card select')
			.prop('disabled', !enabled)
			.prop('required', enabled);
	}

	$(document).on('change', '.member-create-client-login', function () {
		syncMemberCreatePortalFields($(this).closest('form'));
	});

	function syncOpenModalState() {
		window.setTimeout(function () {
			var openModals = $('.modal.show');

			if (openModals.length) {
				$('body').addClass('modal-open');
				openModals.css('overflow-y', 'auto');
			}
		}, 0);
	}

	$(document).on('hidden.bs.modal', '#main_modal, #secondary_modal', function () {
		$('.table-row-actions').each(function () {
			restoreFloatingTableActionMenu($(this));
		});

		$(this).find('.table-dropdown-open, .table-dropdown-open-card, .table-dropdown-open-modal')
			.removeClass('table-dropdown-open table-dropdown-open-card table-dropdown-open-modal');
		syncOpenModalState();
	});

	$(document).on('shown.bs.modal', '#main_modal, #secondary_modal', function () {
		syncOpenModalState();
	});

	$(document).on('shown.bs.modal', '#main_modal', function () {
		$(this).find('.member-create-modal-form').each(function () {
			syncMemberCreatePortalFields($(this));
		});
	});

	$("#main_modal").on('show.bs.modal', function () {
		$('#main_modal').css("overflow-y", "hidden");
	});

	$("#main_modal").on('shown.bs.modal', function () {
		$('#main_modal').css("overflow-y", "auto");
	});

	//Ajax Secondary Modal Function
	$(document).on("click", ".ajax-modal-2", function () {
		var link = $(this).attr("href");

		var title = $(this).data("title");
		var fullscreen = $(this).data("fullscreen");
		var size = $(this).data("size");
		var reload = $(this).data("reload");

		$.ajax({
			url: link,
			beforeSend: function () {
				$("#preloader").css("display", "block");
			}, success: function (data) {
				$("#preloader").css("display", "none");
				$('#secondary_modal .modal-title').html(title);
				$('#secondary_modal .modal-body').html(data);
				$("#secondary_modal .alert-primary").addClass('d-none');
				$("#secondary_modal .alert-danger").addClass('d-none');
				$('#secondary_modal').modal('show');


				if (fullscreen == true) {
					$("#secondary_modal >.modal-dialog").removeClass("modal-sm modal-lg modal-xl").addClass("fullscreen-modal");
				} else if (size) {
					$("#secondary_modal >.modal-dialog").removeClass("fullscreen-modal modal-sm modal-lg modal-xl").addClass("modal-" + size);
				} else {
					$("#secondary_modal >.modal-dialog").removeClass("fullscreen-modal");
				}

				if (reload == false) {
					target_select.select2('close');
					$("#secondary_modal .ajax-submit, #secondary_modal .ajax-screen-submit").attr('data-reload', false);
				}

				//init Essention jQuery Library
				$("#secondary_modal select.select2").select2({
					//theme: "classic",
					dropdownParent: $("#secondary_modal .modal-content")
				});

				//$('.year').mask('0000-0000');
				if ($('#secondary_modal .ajax-submit').length) {
					$('#secondary_modal .ajax-submit').parsley();
				}

				if ($('#secondary_modal .ajax-screen-submit').length) {
					$('#secondary_modal .ajax-screen-submit').parsley();
				}

				/** Init Datepicker **/
				init_datepicker('#secondary_modal');

				/** Init Colorpicker **/
				if($('#secondary_modal .color-picker').length){
					$('#secondary_modal .color-picker').colorpicker();
				}

				$(".float-field").on('keypress', function (event) {
					if ((event.which != 46 || $(this).val().indexOf('.') != -1) &&
						(event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});

				$(".int-field").on('keypress', function (event) {
					if ((event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});

				//Ajax Select2
				if ($("#secondary_modal .select2-ajax").length) {
					$('#secondary_modal .select2-ajax').each(function (i, obj) {

						var display2 = "";
						var divider = "";
						if (typeof $(this).data('display2') !== "undefined") {
							display2 = "&display2=" + $(this).data('display2');
						}

						if (typeof $(this).data('divider') !== "undefined") {
							divider = "&divider=" + $(this).data('divider');
						}


						$(this).select2({
							//theme: "classic",
							placeholder: $lang_select_one,
							ajax: {
								url: _tenant_url + '/ajax/get_table_data?table=' + $(this).data('table') + '&value=' + $(this).data('value') + '&display=' + $(this).data('display') + display2 + divider + '&where=' + $(this).data('where'),
								processResults: function (data) {
									return {
										results: data
									};
								}
							}
						}).on('select2:open', () => {
							target_select = $(this);
							$(".select2-results:not(:has(a))").append('<p class="border-top m-0 p-2"><a class="ajax-modal-2" href="'+ $(this).data('href') +'" data-title="'+ $(this).data('title') +'" data-reload="false"><i class="fas fa-plus-circle mr-1"></i>'+ $lang_add_new +'</a></p>');
						});;

					});
				}

				$("#secondary_modal .dropify").dropify();

				$("#secondary_modal input:required, #secondary_modal select:required, #secondary_modal textarea:required")
					.closest(".form-group")
					.find("label.form-label, label.col-form-label, label.control-label")
					.append("<span class='required'> *</span>");
			},
			error: function (request, status, error) {
				$("#secondary_modal").find("button[type=submit]").attr("disabled", false);
				$("#preloader").css("display", "none");
				var message = request.responseJSON && request.responseJSON.message
					? request.responseJSON.message
					: (request.responseText ? request.responseText.replace(/(<([^>]+)>)/ig, "") : (error || 'Error'));

				$("#secondary_modal").find(".alert-danger").html("<span>" + message + "</span>");
				$("#secondary_modal").find(".alert-primary").addClass('d-none');
				$("#secondary_modal").find(".alert-danger").removeClass('d-none');
				$.toast({
					text: message,
					showHideTransition: 'slide',
					icon: 'error',
					position: 'top-right'
				});
				console.log(request.responseText);
			}
		});

		return false;
	});

	$("#secondary_modal").on('show.bs.modal', function () {
		$('#secondary_modal').css("overflow-y", "hidden");
	});

	$("#secondary_modal").on('shown.bs.modal', function () {
		$('#secondary_modal').css("overflow-y", "auto");
	});


	//Ajax Modal Submit
	$(document).on("submit", ".ajax-submit", function () {
		var link = $(this).attr("action");
		var reload = $(this).data('reload');
		var current_modal = $(this).closest('.modal');

		var elem = $(this);
		$(elem).find("button[type=submit]").prop("disabled", true);

		$.ajax({
			method: "POST",
			url: link,
			data: new FormData(this),
			mimeType: "multipart/form-data",
			contentType: false,
			cache: false,
			processData: false,
			beforeSend: function () {
				$("#preloader").css("display", "block");
			}, success: function (data) {
				$(elem).find("button[type=submit]").attr("disabled", false);
				$("#preloader").css("display", "none");
				var json = JSON.parse(data);
				if (json['result'] == "success") {

					if (reload != false) {
						//Main Modal
						if (json['action'] == "store") {
							$('#main_modal .ajax-submit')[0].reset();
						}
						$("#main_modal .alert-primary").html(json['message']);
						$("#main_modal .alert-primary").removeClass('d-none');
						$("#main_modal .alert-danger").addClass('d-none');

						window.setTimeout(function () { window.location.reload() }, 500);
					} else {
						//Secondary Modal
						if (json['action'] == "store") {
							$(current_modal).find('.ajax-submit')[0].reset();
						}
						if (json['action'] == "send") {
							$(elem)[0].reset();
						}

						$(current_modal).find(".alert-primary").html(json['message']);
						$(current_modal).find(".alert-primary").removeClass('d-none');
						$(current_modal).find(".alert-danger").addClass('d-none');

						if (typeof target_select !== 'undefined' && target_select && json['data']) {
							var select_value = json['data'][target_select.data('value')];
							var select_display = json['data'][target_select.data('display')];

							var newOption = new Option(select_display, select_value, true, true);
							target_select.append(newOption).trigger('change');
							$(current_modal).modal('hide');
						}
					}

				} else {
					if (Array.isArray(json['message'])) {
						if (reload != false) {
							//Main Modal
							$("#main_modal .alert-danger").html("");
							jQuery.each(json['message'], function (i, val) {
								$("#main_modal .alert-danger").append("<span>" + val + "</span>");
							});
							$("#main_modal .alert-primary").addClass('d-none');
							$("#main_modal .alert-danger").removeClass('d-none');
						} else {
							//Secondary Modal
							$(current_modal).find(".alert-danger").html('');
							jQuery.each(json['message'], function (i, val) {
								$(current_modal).find(".alert-danger").append("<span>" + val + "</span>");
							});
							$(current_modal).find(".alert-primary").addClass('d-none');
							$(current_modal).find(".alert-danger").removeClass('d-none');
						}
					} else {
						if (reload != false) {
							$("#main_modal .alert-danger").html("<span>" + json['message'] + "</span>");
							$("#main_modal .alert-primary").addClass('d-none');
							$("#main_modal .alert-danger").removeClass('d-none');
						} else {
							$(current_modal).find(".alert-danger").html("<span>" + json['message'] + "</span>");
							$(current_modal).find(".alert-primary").addClass('d-none');
							$(current_modal).find(".alert-danger").removeClass('d-none');
						}
					}
				}
			},
			error: function (request, status, error) {
				$(elem).find("button[type=submit]").attr("disabled", false);
				$("#preloader").css("display", "none");
				var message = request.responseJSON && request.responseJSON.message
					? request.responseJSON.message
					: (request.responseText ? request.responseText.replace(/(<([^>]+)>)/ig, "") : (error || 'Error'));

				$(current_modal).find(".alert-danger").html("<span>" + message + "</span>");
				$(current_modal).find(".alert-primary").addClass('d-none');
				$(current_modal).find(".alert-danger").removeClass('d-none');
				$.toast({
					text: message,
					showHideTransition: 'slide',
					icon: 'error',
					position: 'top-right'
				});
				console.log(request.responseText);
			}
		});

		return false;
	});

	//Ajax Modal Submit without loading
	$(document).on("submit", ".ajax-screen-submit", function () {
		var link = $(this).attr("action");
		var reload = $(this).data('reload');
		var current_modal = $(this).closest('.modal');

		var elem = $(this);
		$(elem).find("button[type=submit]").prop("disabled", true);

		$.ajax({
			method: "POST",
			url: link,
			data: new FormData(this),
			mimeType: "multipart/form-data",
			contentType: false,
			cache: false,
			processData: false,
			beforeSend: function () {
				$("#preloader").css("display", "block");
			}, success: function (data) {
				$(elem).find("button[type=submit]").attr("disabled", false);
				$("#preloader").css("display", "none");
				var json = JSON.parse(data);
				if (json['result'] == "success") {

					$(document).trigger('ajax-screen-submit');

					$.toast({
						text: json['message'],
						showHideTransition: 'slide',
						icon: 'success',
						position: 'top-right'
					});

					var table = json['table'];
					var target_table = $(table);
					var has_data_table = target_table.length
						&& $.fn.DataTable
						&& $.fn.DataTable.isDataTable(target_table);

					if (json['action'] == "update") {

						if (has_data_table) {
							target_table.DataTable().ajax.reload(null, false);
						} else if (json['row']) {
							$(table + ' tr[data-id="row_' + json['data']['id'] + '"]').replaceWith(json['row']);
						} else {
							$(table + ' tr[data-id="row_' + json['data']['id'] + '"]').find('td').each(function () {
								if (typeof $(this).attr("class") != "undefined") {
									$(this).html(json['data'][$(this).attr("class").split(' ')[0]]);
								}
							});
						}

					} else if (json['action'] == "store") {
						$(elem)[0].reset();
						var first_row = target_table.find('tbody').find('tr:eq(0)');

						if (has_data_table) {
							var data_table = target_table.DataTable();
							data_table.search('');
							data_table.columns().search('');
							data_table.page('first').draw();
						} else if (target_table.length && json['row']) {
							target_table.find('tbody').find('tr').has('td[colspan], .dataTables_empty').remove();
							target_table.find('tbody').prepend(json['row']);
						} else if (target_table.length && first_row.length && first_row.find('.dataTables_empty, td[colspan]').length === 0) {
							var new_row = first_row.clone();
							var old_id = (first_row.attr("data-id") || "").replace("row_", "");

							$(new_row).attr("data-id", "row_" + json['data']['id']);

							$(new_row).find('td').each(function () {
								if (typeof $(this).attr("class") != "undefined") {
									var field = $(this).attr("class").split(' ')[0];
									if (typeof json['data'][field] != "undefined") {
										$(this).html(json['data'][field]);
									}
								}
							});


							if (old_id) {
								var id_pattern = new RegExp('/' + old_id + '(/|$)');
								$(new_row).find('a[href], form[action]').each(function () {
									if ($(this).attr("href")) {
										$(this).attr("href", $(this).attr("href").replace(id_pattern, '/' + json['data']['id'] + '$1'));
									}

									if ($(this).attr("action")) {
										$(this).attr("action", $(this).attr("action").replace(id_pattern, '/' + json['data']['id'] + '$1'));
									}
								});
							}

							$(new_row).find('.dropdown-edit').attr("data-href", link + "/" + json['data']['id'] + "/edit");
							$(new_row).find('.dropdown-view').attr("data-href", link + "/" + json['data']['id']);

							target_table.find('tbody').prepend(new_row);
						}

						if (reload == false && typeof target_select !== 'undefined' && target_select && json['data']) {
							var select_value = json['data'][target_select.data('value')];
							var select_display = json['data'][target_select.data('display')];

							var newOption = new Option(select_display, select_value, true, true);
							target_select.append(newOption).trigger('change');

							if(typeof previous_select !== 'undefined' && previous_select != null){
								var newOption = new Option(select_display, select_value, true, true);
								previous_select.append(newOption).trigger('change');
							}
							$(current_modal).modal('hide');
						}

					}
					if (json['resource'] == 'members') {
						$(document).trigger('cavic:members-changed', [json]);
					}
					$(current_modal).modal('hide');
					$(current_modal).find(".alert-primary").addClass('d-none');
					$(current_modal).find(".alert-danger").addClass('d-none');

				} else if (json['result'] == "error") {

					$(current_modal).find(".alert-danger").html("");
					if (Array.isArray(json['message'])) {
						jQuery.each(json['message'], function (i, val) {
							$(current_modal).find(".alert-danger").append("<span>" + val + "</span>");
						});
						$(current_modal).find(".alert-primary").addClass('d-none');
						$(current_modal).find(".alert-danger").removeClass('d-none');
					} else {
						$(current_modal).find(".alert-danger").html("<span>" + json['message'] + "</span>");
						$(current_modal).find(".alert-primary").addClass('d-none');
						$(current_modal).find(".alert-danger").removeClass('d-none');
					}
				} else {
					$.toast({
						text: data.replace(/(<([^>]+)>)/ig, ""),
						showHideTransition: 'slide',
						icon: 'error',
						position: 'top-right'
					});
				}
			},
			error: function (request, status, error) {
				$(elem).find("button[type=submit]").attr("disabled", false);
				$("#preloader").css("display", "none");
				var message = request.responseJSON && request.responseJSON.message
					? request.responseJSON.message
					: (request.responseText ? request.responseText.replace(/(<([^>]+)>)/ig, "") : (error || 'Error'));

				$(current_modal).find(".alert-danger").html("<span>" + message + "</span>");
				$(current_modal).find(".alert-primary").addClass('d-none');
				$(current_modal).find(".alert-danger").removeClass('d-none');
				$.toast({
					text: message,
					showHideTransition: 'slide',
					icon: 'error',
					position: 'top-right'
				});
				console.log(request.responseText);
			}
		});

		return false;
	});

	//Ajax Remove without loading
	$(document).on("click", ".ajax-get-remove", function () {
		var current_modal = $(this).closest('.modal');

		Swal.fire({
			title: $lang_alert_title,
			text: $lang_alert_message,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: $lang_confirm_button_text,
			cancelButtonText: $lang_cancel_button_text
		}).then((result) => {
			if (result.value) {
				var link = $(this).attr("href");
				$.ajax({
					method: "GET",
					url: link,
					beforeSend: function () {
						$("#preloader").css("display", "block");
					}, success: function (data) {
						$("#preloader").css("display", "none");

						var json = JSON.parse(JSON.stringify(data));
						console.log(json['result']);
						if (json['result'] == "success") {

							$.toast({
								text: json['message'],
								showHideTransition: 'slide',
								icon: 'success',
								position: 'top-right'
							});

							var table = json['table'];
							//$(table).find('#row_' + json['id']).remove();
							$(table + ' tr[data-id="row_' + json['id'] + '"]').remove();

						} else if (json['result'] == "error") {
							if (Array.isArray(json['message'])) {
								jQuery.each(json['message'], function (i, val) {
									$.toast({
										text: val,
										showHideTransition: 'slide',
										icon: 'error',
										position: 'top-right'
									});
								});

							} else {
								$.toast({
									text: json['message'],
									showHideTransition: 'slide',
									icon: 'error',
									position: 'top-right'
								});
							}
						} else {
							$.toast({
								text: data.replace(/(<([^>]+)>)/ig, ""),
								showHideTransition: 'slide',
								icon: 'error',
								position: 'top-right'
							});
						}
					},
					error: function (request, status, error) {
						console.log(request.responseText);
					}
				});
			}
		});

		return false;

	});


	//Ajax Remove without loading
	$(document).on("submit", ".ajax-remove", function (event) {
		event.preventDefault();

		var current_modal = $(this).closest('.modal');

		Swal.fire({
			title: $lang_alert_title,
			text: $lang_alert_message,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: $lang_confirm_button_text,
			cancelButtonText: $lang_cancel_button_text
		}).then((result) => {
			if (result.value) {
				var link = $(this).attr("action");
				$.ajax({
					method: "POST",
					url: link,
					data: $(this).serialize(),
					beforeSend: function () {
						$("#preloader").css("display", "block");
					}, success: function (data) {
						$("#preloader").css("display", "none");
						var json = JSON.parse(JSON.stringify(data));
						if (json['result'] == "success") {

							$.toast({
								text: json['message'],
								showHideTransition: 'slide',
								icon: 'success',
								position: 'top-right'
							});

							var table = json['table'];
							//$(table).find('#row_' + json['id']).remove();
							$(table + ' tr[data-id="row_' + json['id'] + '"]').remove();

						} else if (json['result'] == "error") {
							if (Array.isArray(json['message'])) {
								jQuery.each(json['message'], function (i, val) {
									$.toast({
										text: val,
										showHideTransition: 'slide',
										icon: 'error',
										position: 'top-right'
									});
								});

							} else {
								$.toast({
									text: json['message'],
									showHideTransition: 'slide',
									icon: 'error',
									position: 'top-right'
								});
							}
						} else {
							$.toast({
								text: data.replace(/(<([^>]+)>)/ig, ""),
								showHideTransition: 'slide',
								icon: 'error',
								position: 'top-right'
							});
						}
					},
					error: function (request, status, error) {
						console.log(request.responseText);
					}
				});
			} else {
				$(this).find(":submit").prop('disabled', false);
			}
		});

	});


	//Ajax submit without validate
	$(document).on("submit", ".settings-submit", function () {
		var elem = $(this);
		$(elem).find("button[type=submit]").prop("disabled", true);
		var link = $(this).attr("action");
		$.ajax({
			method: "POST",
			url: link,
			data: new FormData(this),
			mimeType: "multipart/form-data",
			contentType: false,
			cache: false,
			processData: false,
			beforeSend: function () {
				$("#preloader").fadeIn();
			}, success: function (data) {
				$("#preloader").fadeOut();
				$(elem).find("button[type=submit]").attr("disabled", false);
				var json = JSON.parse(data);

				if (json['result'] == "success") {
					$("#main_alert > span.msg").html(json['message']);
					$("#main_alert").addClass("alert-success").removeClass("alert-danger");
					$("#main_alert").css('display', 'block');
				} else {
					if (Array.isArray(json['message'])) {
						$("#main_alert > span.msg").html("");
						$("#main_alert").addClass("alert-danger").removeClass("alert-success");

						jQuery.each(json['message'], function (i, val) {
							$("#main_alert > span.msg").append('<i class="ti-alert"></i> ' + val + '<br>');
						});
						$("#main_alert").css('display', 'block');
					} else {
						$("#main_alert > span.msg").html("");
						$("#main_alert").addClass("alert-danger").removeClass("alert-success");
						$("#main_alert > span.msg").html(json['message']);
						$("#main_alert").css('display', 'block');
					}
				}
			},
			error: function (request, status, error) {
				console.log(request.responseText);
			}
		});

		return false;
	});

	//Auto Selected
	if ($(".auto-select").length) {
		$('.auto-select').each(function (i, obj) {
			$(this).val($(this).data('selected')).trigger('change');
		})
	}

	if ($(".auto-multiple-select").length) {
		$('.auto-multiple-select').each(function (i, obj) {
			var values = $(this).data('selected');
			$(this).val(values).trigger('change');
		})
	}

	$(document).on('change', '.c-select', function(){
		if($(this).data('condition') == $(this).val()){
			$('.' + $(this).data('show')).removeClass('d-none');
		}else{
			$('.' + $(this).data('show')).addClass('d-none');
		}
	});

	if(jQuery().DataTable) {
		if ($(".data-table").length) {
			$('.data-table').each(function (i, obj) {
				var table = $(this).DataTable({
					responsive: true,
					"bAutoWidth": false,
					"ordering": false,
					//"lengthChange": false,
					"dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
					"<'row'<'col-sm-12'tr>>" +
					"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
					"language": {
						"decimal": "",
						"emptyTable": $lang_no_data_found,
						"info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
						"infoEmpty": $lang_showing_0_to_0_of_0_entries,
						"infoFiltered": "(filtered from _MAX_ total entries)",
						"infoPostFix": "",
						"thousands": ",",
						"lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
						"loadingRecords": $lang_loading,
						"processing": $lang_processing,
						"search": $lang_search,
						"zeroRecords": $lang_no_matching_records_found,
						"paginate": {
							"first": $lang_first,
							"last": $lang_last,
							"previous": "<i class='fas fa-angle-left'></i>",
							"next": "<i class='fas fa-angle-right'></i>"
						},
						"aria": {
							"sortAscending": ": activate to sort column ascending",
							"sortDescending": ": activate to sort column descending"
						},
					},
					drawCallback: function () {
						$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
					}
				});
			});
		}

		if ($(".report-table").length) {
			$(".report-table").each(function (j, obj) {
			  	var headerText = $(obj).prev(".report-header").html();
				var report_table = $(this).DataTable({
					responsive: true,
					"bAutoWidth": false,
					"ordering": false,
					"lengthChange": false,
					dom:
					"<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
					"<'row'<'col-sm-12'tr>>" +
					"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
					"language": {
						"decimal": "",
						"emptyTable": $lang_no_data_found,
						"info": $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " + $lang_entries,
						"infoEmpty": $lang_showing_0_to_0_of_0_entries,
						"infoFiltered": "(filtered from _MAX_ total entries)",
						"infoPostFix": "",
						"thousands": ",",
						"lengthMenu": $lang_show + " _MENU_ " + $lang_entries,
						"loadingRecords": $lang_loading,
						"processing": $lang_processing,
						"search": $lang_search,
						"zeroRecords": $lang_no_matching_records_found,
						"paginate": {
							"first": $lang_first,
							"last": $lang_last,
							"previous": "<i class='fas fa-angle-left'></i>",
							"next": 	"<i class='fas fa-angle-right'></i>"
						},
						"aria": {
							"sortAscending": ": activate to sort column ascending",
							"sortDescending": ": activate to sort column descending"
						},
						"buttons": {
							copy: $lang_copy,
							excel: $lang_excel,
							pdf: $lang_pdf,
							print: $lang_print,
						}
					},
					drawCallback: function () {
						$(".dataTables_paginate > .pagination").addClass("pagination-bordered");
					},
					buttons: [
						'copy', 'excel', 'pdf',
						{
							extend: 'print',
							title: '',
							customize: function (win) {
								$(win.document.body)
									.css('font-size', '10pt')
									.prepend(
										'<div class="text-center">' + headerText + '</div>'
									);

								$(win.document.body).find('table')
									.addClass('compact')
									.css('font-size', 'inherit');

							}
						}
					],
				});
			});
		}

	}


	//General Settings Page
	if ($("#mail_type").val() == "mail") {
		$(".smtp").prop("disabled", true);
	}

	$(document).on("change", "#mail_type", function () {
		if ($(this).val() == "mail") {
			$(".smtp").prop("disabled", true);
		} else {
			$(".smtp").prop("disabled", false);
		}
	});

	//Access Control
	$(document).on('change', '#permissions #user_role', function () {
		showRole($(this));
	});

	$("#permissions .custom-control-input").each(function () {
		if ($(this).prop("checked") == true) {
			$(this).closest(".collapse").addClass("show");
		}
	});

	$("#user_type").val() == "user"
			? $("#role_id").prop("disabled", false)
			: $("#role_id").prop("disabled", true);


	$(document).on("change", "#user_type", function () {
		$(this).val() == "user"
			? $("#role_id").prop("disabled", false)
			: $("#role_id").prop("disabled", true);
	});


	$(document).on("click", ".notification_mark_as_read", function (event) {
		event.preventDefault();
		var notification = $(this);
		$.ajax({
		  url: $(notification).attr("href"),
		  beforeSend: function () {
			$("#preloader").css("display", "block");
		  },
		  success: function (data) {
			$(notification).prev().find("p").removeClass("unread-notification");
			$(notification).remove();
			$("#notification-count").html(
			  parseInt($("#notification-count").html()) - 1
			);
			$("#preloader").css("display", "none");
		  },
		});
	});

	$(document).on('click','.copy-link',function(){
		var copyText = $(this).data('copy-text');
		navigator.clipboard.writeText(copyText);
		$.toast({text: $(this).data('message'), icon: 'success'});
	});

	//Multi Select
	if ($(".multi-selector").length) {
		$('.multi-selector').each(function (i, obj) {
			var dropdonwValues = '';
			var selectedText = '';

			$($(this).find('option')).each(function(index, option){
				if($(this).is(':selected')){
					selectedText += ", " + option.text;
					dropdonwValues += `<a class="dropdown-item" href="javascript: void(0);"><label class="d-flex align-items-center"><input type="checkbox" class="mr-2" value="${option.value}" data-text="${option.text}" checked><span>${option.text}</span></label></a>`;
				}else{
					dropdonwValues += `<a class="dropdown-item" href="javascript: void(0);"><label class="d-flex align-items-center"><input type="checkbox" class="mr-2" value="${option.value}" data-text="${option.text}"><span>${option.text}</span></label></a>`;
				}
			});

			if(selectedText == ""){
				selectedText = $(this).data('placeholder');
			}else{
				selectedText = selectedText.split(' ').slice(1).join(' ');
			}

			$(this).after(`<div class="dropdown multi-select-box">
				<button class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
					${selectedText}
				</button>
				<div class="dropdown-menu">
				${dropdonwValues}
				</div>
			</div>`);
		})
	}

	$(document).on('change', '.multi-select-box .dropdown-item input', function(){
		var selectedText = '';
		var selectedValues = [];
		$($(this).closest('.dropdown-menu').find('input')).each(function(value, option){
			if($(this).is(':checked')){
				selectedText += ", " + $(this).data('text');
				selectedValues.push( $(this).val());
			}
		});

		$(this).closest('.multi-select-box').prev().val(selectedValues).trigger('change');

		if(selectedText == ""){
			selectedText = $(this).closest('.multi-select-box').prev().data('placeholder');
		}else{
			selectedText = selectedText.split(' ').slice(1).join(' ');
		}

		$(this).closest('.multi-select-box').find('.dropdown-toggle').html(selectedText);
	});

	$(document).on('click', '.multi-select-box.dropdown', function (e) {
		e.stopPropagation();
	});

})(window.jQuery || window.$);

function readFileURL(input) {
	if(input.files){
		for (let i = 0; i < input.files.length; i++) {
			var reader = new FileReader();
			reader.onload = function (e) { };

			$(input).parent().find(".filename").val(input.files[i].name);
			reader.readAsDataURL(input.files[i]);
		}

		if(input.files.length > 1){
			$(input).parent().find(".filename").val(input.files.length + ' files selected');
		}else{
			$(input).parent().find(".filename").val(input.files[0].name);
		}
	}
}

function init_editor() {
	if ($(".summernote").length > 0) {
		tinymce.remove();
		tinymce.init({
			selector: "textarea.summernote",
			theme: "modern",
			height: 250,
			plugins: [
				"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
				"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
				"save table contextmenu directionality emoticons template paste textcolor"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
			style_formats: [
				{ title: 'Bold text', inline: 'b' },
				{ title: 'Red text', inline: 'span', styles: { color: '#ff0000' } },
				{ title: 'Red header', block: 'h1', styles: { color: '#ff0000' } },
				{ title: 'Example 1', inline: 'span', classes: 'example1' },
				{ title: 'Example 2', inline: 'span', classes: 'example2' },
				{ title: 'Table styles' },
				{ title: 'Table row 1', selector: 'tr', classes: 'tablerow1' }
			]
		});
	}

	if ($(".mini-summernote").length > 0) {
		tinymce.remove();
		tinymce.init({
			selector: "textarea.mini-summernote",
			toolbar: "undo redo | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat",
    		plugins: "link lists",
			plugins: "link lists", // Only essential plugins
			branding: false, // Removes "Powered by TinyMCE" branding
			menubar: false,
			height: 200 // Adjusts the editor height
		});
	}
}

function init_datepicker(context) {
	var $context = context ? $(context) : $(document);
	/** Start Datepicker **/
	var date_format = ["Y-m-d", "d-m-Y", "d/m/Y", "m-d-Y", "m.d.Y", "m/d/Y", "d.m.Y", "d/M/Y", "M/d/Y", "d M, Y"];
	var picker_date_format = ["YYYY-MM-DD", "DD-MM-YYYY", "DD/MM/YYYY", "MM-DD-YYYY", "MM.DD.YYYY", "MM/DD/YYYY", "DD.MM.YYYY", "DD/MMM/YYYY", "MMM/DD/YYYY", "DD MMM, YYYY"];

	var fake_format = picker_date_format[date_format.indexOf(_date_format)] || 'DD/MM/YYYY';
	var $datepickers = $context.find(".datepicker");

	//Set Default date
	if ($datepickers.length) {
		$datepickers.each(function (i, obj) {

			$(this).daterangepicker({
				singleDatePicker: true,
				showDropdowns: true,
				locale: {
					format: 'YYYY-MM-DD'
				}
			});

			$(this).css('color', 'transparent');

			if (typeof $(this).next().attr('class') === "undefined") {
				$(this).after('<span class="fake_datepicker"></span>');
				$(this).next('.fake_datepicker').css('margin-top', "-45.2px");
			}
			var dateValue = $(this).val();
			var displayDate = moment(dateValue, ['YYYY-MM-DD', 'YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY'], true);
			if (!displayDate.isValid()) {
				displayDate = moment(dateValue);
			}
			$(this).next('.fake_datepicker').html(displayDate.isValid() ? displayDate.format(fake_format) : '');
		})
	}

	$datepickers.off('apply.daterangepicker.cavic').on('apply.daterangepicker.cavic', function (ev, picker) {
		$(this).next('.fake_datepicker').html(picker.startDate.format(fake_format));
	});

	$(document).off('click.cavicFakeDatepicker', '.fake_datepicker').on('click.cavicFakeDatepicker', '.fake_datepicker', function () {
		$(this).prev().focus();
	});

	/** End Datepicker **/
}

function showRole(elem) {
	if ($(elem).val() == '') {
		return;
	}
	window.location = _user_url + '/roles/' + $(elem).val() + '/access_control';
}
