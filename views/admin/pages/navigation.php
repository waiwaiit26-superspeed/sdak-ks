<?php $pageTitle = 'จัดการเมนู'; $page = 'navigation'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-list me-2"></i>จัดการเมนู</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">เมนู</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-info btn-add-nav" onclick="openNavForm()">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มเมนู
                </button>
            </div>

            <div class="card nav-mgmt-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="card-title mb-0"><i class="bi bi-grip-vertical me-2"></i>ลาก↕จัดลำดับ · ลาก↔จัดเมนูย่อย <span class="nav-count-badge" id="navCount"></span></span>
                    <button class="btn btn-sm btn-outline-light" id="btnSaveOrder" style="display:none;" onclick="saveNavOrder()">
                        <i class="bi bi-check-lg me-1"></i> บันทึกลำดับ
                    </button>
                </div>
                <div class="card-body p-0">
                    <div id="navLoading" class="nav-loading-state">
                        <div class="spinner-border text-info"></div>
                        <p class="mt-3 text-muted mb-0">กำลังโหลดรายการเมนู...</p>
                    </div>
                    <ul class="list-group list-group-flush" id="navList"></ul>
                    <div id="navEmpty" class="nav-empty-state" style="display:none;">
                        <i class="bi bi-menu-button-wide"></i>
                        <p>ยังไม่มีรายการเมนู</p>
                        <button class="btn btn-outline-info btn-sm" onclick="openNavForm()">
                            <i class="bi bi-plus-lg me-1"></i> เพิ่มเมนูรายการแรก
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Nav Form Modal -->
<div class="modal fade nav-form-modal" id="navFormModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="navFormTitle"><i class="bi bi-plus-circle me-2"></i>เพิ่มเมนู</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="navForm">
                <div class="modal-body">
                    <input type="hidden" id="navId" name="id">

                    <div class="form-group">
                        <label for="navTitle">ชื่อเมนู <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="navTitle" name="title" required placeholder="กรอกชื่อเมนู">
                    </div>

                    <div class="form-group">
                        <label for="navAlias">Alias (ชื่อย่อภาษาอังกฤษ)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i> page=</span>
                            </div>
                            <input type="text" class="form-control" id="navAlias" name="alias"
                                placeholder="เช่น about-us, contact"
                                pattern="[a-z0-9\-_]+"
                                title="ใช้ตัวพิมพ์เล็ก ตัวเลข - _ เท่านั้น">
                        </div>
                        <small class="form-text text-muted">URL: <code>./web/?page=<span id="aliasPreview">alias</span></code></small>
                    </div>

                    <div class="form-group">
                        <label for="navLinkType">ประเภทลิงก์</label>
                        <select class="form-control" id="navLinkType" onchange="toggleLinkType()">
                            <option value="url">URL กำหนดเอง</option>
                            <option value="page">เลือกหน้าเพจที่มีอยู่</option>
                            <option value="create_page">สร้างหน้าเพจใหม่</option>
                        </select>
                    </div>

                    <div class="form-group" id="urlGroup">
                        <label for="navUrl">URL</label>
                        <input type="text" class="form-control" id="navUrl" name="url" placeholder="เช่น ./web/?page=news หรือ https://...">
                    </div>

                    <div class="form-group" id="pageGroup" style="display:none;">
                        <label for="navPageId">เลือกหน้าเพจ</label>
                        <select class="form-control" id="navPageId" name="page_id">
                            <option value="">-- เลือกหน้าเพจ --</option>
                        </select>
                    </div>

                    <div class="form-group" id="createPageGroup" style="display:none;">
                        <div class="callout callout-info mb-0">
                            <h6><i class="bi bi-info-circle me-1"></i> สร้างหน้าเพจอัตโนมัติ</h6>
                            <p class="mb-0">ระบบจะสร้างหน้าเพจใหม่โดยใช้ <strong>ชื่อเมนู</strong> เป็นชื่อหน้า
                            และ <strong>Alias</strong> เป็น slug<br>
                            <small>สามารถแก้ไขเนื้อหาได้ภายหลังที่หน้า "จัดการหน้าเพจ"</small></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="navParentId">เมนูหลัก (Parent)</label>
                        <select class="form-control" id="navParentId" name="parent_id">
                            <option value="">-- ไม่มี (เป็นเมนูหลัก) --</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="bi bi-palette me-1"></i> ไอคอน</label>
                                <input type="hidden" id="navIcon" name="icon">
                                <div class="icon-picker-selected d-flex align-items-center mb-2">
                                    <span class="icon-picker-preview" id="iconPreview">
                                        <i class="bi bi-cursor" id="iconPreviewI"></i>
                                    </span>
                                    <span class="icon-picker-label text-muted ml-2" id="iconPreviewLabel">ยังไม่ได้เลือก</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ml-auto" onclick="clearIconSelection()" title="ล้างไอคอน">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <input type="text" class="form-control form-control-sm mb-2" id="iconSearchInput" placeholder="ค้นหาไอคอน..." autocomplete="off">
                                <div class="icon-picker-grid" id="iconPickerGrid"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="navTarget">เปิดลิงก์</label>
                                <select class="form-control" id="navTarget" name="target">
                                    <option value="_self">หน้าเดิม</option>
                                    <option value="_blank">แท็บใหม่</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="custom-control custom-switch mt-2">
                        <input class="custom-control-input" type="checkbox" id="navIsActive" name="is_active" checked>
                        <label class="custom-control-label" for="navIsActive">เปิดใช้งาน</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<script>
let navItems = [];
let pages = [];
let orderChanged = false;
let sortableInstance = null;

// ── Icon Picker Data ──
const ICON_LIST = [
    // Navigation & UI
    {icon:'bi bi-house',label:'house'},{icon:'bi bi-house-fill',label:'house fill'},
    {icon:'bi bi-list',label:'list'},{icon:'bi bi-grid',label:'grid'},{icon:'bi bi-grid-fill',label:'grid fill'},
    {icon:'bi bi-menu-button',label:'menu'},{icon:'bi bi-three-dots',label:'dots'},
    {icon:'bi bi-arrow-right',label:'arrow right'},{icon:'bi bi-arrow-left',label:'arrow left'},
    {icon:'bi bi-chevron-right',label:'chevron right'},{icon:'bi bi-chevron-down',label:'chevron down'},
    // People & Users
    {icon:'bi bi-people',label:'people'},{icon:'bi bi-people-fill',label:'people fill'},
    {icon:'bi bi-person',label:'person'},{icon:'bi bi-person-fill',label:'person fill'},
    {icon:'bi bi-person-badge',label:'person badge'},{icon:'bi bi-person-plus',label:'person plus'},
    {icon:'bi bi-person-circle',label:'person circle'},{icon:'bi bi-person-gear',label:'person gear'},
    // Communication
    {icon:'bi bi-envelope',label:'envelope'},{icon:'bi bi-envelope-fill',label:'envelope fill'},
    {icon:'bi bi-telephone',label:'telephone'},{icon:'bi bi-telephone-fill',label:'telephone fill'},
    {icon:'bi bi-chat',label:'chat'},{icon:'bi bi-chat-dots',label:'chat dots'},
    {icon:'bi bi-megaphone',label:'megaphone'},{icon:'bi bi-megaphone-fill',label:'megaphone fill'},
    {icon:'bi bi-bell',label:'bell'},{icon:'bi bi-bell-fill',label:'bell fill'},
    // Content & Media
    {icon:'bi bi-newspaper',label:'newspaper'},{icon:'bi bi-file-earmark',label:'file'},
    {icon:'bi bi-file-earmark-text',label:'file text'},{icon:'bi bi-file-earmark-richtext',label:'file rich'},
    {icon:'bi bi-folder',label:'folder'},{icon:'bi bi-folder-fill',label:'folder fill'},
    {icon:'bi bi-image',label:'image'},{icon:'bi bi-image-fill',label:'image fill'},
    {icon:'bi bi-camera',label:'camera'},{icon:'bi bi-camera-fill',label:'camera fill'},
    {icon:'bi bi-film',label:'film'},{icon:'bi bi-music-note',label:'music'},
    // Education & Knowledge
    {icon:'bi bi-book',label:'book'},{icon:'bi bi-book-fill',label:'book fill'},
    {icon:'bi bi-mortarboard',label:'mortarboard'},{icon:'bi bi-mortarboard-fill',label:'mortarboard fill'},
    {icon:'bi bi-journal',label:'journal'},{icon:'bi bi-journal-text',label:'journal text'},
    {icon:'bi bi-pencil',label:'pencil'},{icon:'bi bi-pencil-square',label:'pencil square'},
    // Business & Work
    {icon:'bi bi-briefcase',label:'briefcase'},{icon:'bi bi-briefcase-fill',label:'briefcase fill'},
    {icon:'bi bi-building',label:'building'},{icon:'bi bi-building-fill',label:'building fill'},
    {icon:'bi bi-bank',label:'bank'},{icon:'bi bi-shop',label:'shop'},
    {icon:'bi bi-cart',label:'cart'},{icon:'bi bi-wallet',label:'wallet'},
    {icon:'bi bi-cash',label:'cash'},{icon:'bi bi-receipt',label:'receipt'},
    // Calendar & Time
    {icon:'bi bi-calendar',label:'calendar'},{icon:'bi bi-calendar-event',label:'calendar event'},
    {icon:'bi bi-calendar-check',label:'calendar check'},{icon:'bi bi-calendar-heart',label:'calendar heart'},
    {icon:'bi bi-clock',label:'clock'},{icon:'bi bi-clock-fill',label:'clock fill'},
    {icon:'bi bi-alarm',label:'alarm'},{icon:'bi bi-stopwatch',label:'stopwatch'},
    // Location & Map
    {icon:'bi bi-map',label:'map'},{icon:'bi bi-map-fill',label:'map fill'},
    {icon:'bi bi-geo-alt',label:'location'},{icon:'bi bi-geo-alt-fill',label:'location fill'},
    {icon:'bi bi-pin-map',label:'pin map'},{icon:'bi bi-compass',label:'compass'},
    {icon:'bi bi-globe',label:'globe'},{icon:'bi bi-globe2',label:'globe2'},
    // Awards & Special
    {icon:'bi bi-award',label:'award'},{icon:'bi bi-award-fill',label:'award fill'},
    {icon:'bi bi-trophy',label:'trophy'},{icon:'bi bi-trophy-fill',label:'trophy fill'},
    {icon:'bi bi-star',label:'star'},{icon:'bi bi-star-fill',label:'star fill'},
    {icon:'bi bi-heart',label:'heart'},{icon:'bi bi-heart-fill',label:'heart fill'},
    {icon:'bi bi-hand-thumbs-up',label:'thumbs up'},{icon:'bi bi-emoji-smile',label:'smile'},
    // Tools & Settings
    {icon:'bi bi-gear',label:'gear'},{icon:'bi bi-gear-fill',label:'gear fill'},
    {icon:'bi bi-tools',label:'tools'},{icon:'bi bi-wrench',label:'wrench'},
    {icon:'bi bi-sliders',label:'sliders'},{icon:'bi bi-filter',label:'filter'},
    {icon:'bi bi-search',label:'search'},{icon:'bi bi-zoom-in',label:'zoom in'},
    // Security & Access
    {icon:'bi bi-shield',label:'shield'},{icon:'bi bi-shield-check',label:'shield check'},
    {icon:'bi bi-lock',label:'lock'},{icon:'bi bi-lock-fill',label:'lock fill'},
    {icon:'bi bi-key',label:'key'},{icon:'bi bi-key-fill',label:'key fill'},
    {icon:'bi bi-eye',label:'eye'},{icon:'bi bi-eye-fill',label:'eye fill'},
    // Technology
    {icon:'bi bi-code',label:'code'},{icon:'bi bi-code-slash',label:'code slash'},
    {icon:'bi bi-terminal',label:'terminal'},{icon:'bi bi-cloud',label:'cloud'},
    {icon:'bi bi-cloud-fill',label:'cloud fill'},{icon:'bi bi-download',label:'download'},
    {icon:'bi bi-upload',label:'upload'},{icon:'bi bi-link',label:'link'},
    {icon:'bi bi-link-45deg',label:'link 45'},{icon:'bi bi-qr-code',label:'qr code'},
    // Status & Info
    {icon:'bi bi-info-circle',label:'info'},{icon:'bi bi-info-circle-fill',label:'info fill'},
    {icon:'bi bi-question-circle',label:'question'},{icon:'bi bi-exclamation-triangle',label:'warning'},
    {icon:'bi bi-check-circle',label:'check'},{icon:'bi bi-check-circle-fill',label:'check fill'},
    {icon:'bi bi-x-circle',label:'x circle'},{icon:'bi bi-flag',label:'flag'},
    {icon:'bi bi-flag-fill',label:'flag fill'},{icon:'bi bi-bookmark',label:'bookmark'},
    // Charts & Data
    {icon:'bi bi-graph-up',label:'graph up'},{icon:'bi bi-graph-down',label:'graph down'},
    {icon:'bi bi-bar-chart',label:'bar chart'},{icon:'bi bi-pie-chart',label:'pie chart'},
    {icon:'bi bi-clipboard-data',label:'clipboard data'},{icon:'bi bi-table',label:'table'},
    // Misc
    {icon:'bi bi-lightning',label:'lightning'},{icon:'bi bi-lightning-fill',label:'lightning fill'},
    {icon:'bi bi-fire',label:'fire'},{icon:'bi bi-sun',label:'sun'},
    {icon:'bi bi-moon',label:'moon'},{icon:'bi bi-brightness-high',label:'brightness'},
    {icon:'bi bi-tag',label:'tag'},{icon:'bi bi-tag-fill',label:'tag fill'},
    {icon:'bi bi-pin',label:'pin'},{icon:'bi bi-paperclip',label:'paperclip'},
    {icon:'bi bi-printer',label:'printer'},{icon:'bi bi-box',label:'box'},
    {icon:'bi bi-gift',label:'gift'},{icon:'bi bi-cursor',label:'cursor'},
    {icon:'bi bi-hand-index',label:'hand index'},{icon:'bi bi-life-preserver',label:'life preserver'},
    {icon:'bi bi-signpost',label:'signpost'},{icon:'bi bi-bullseye',label:'bullseye'},
    {icon:'bi bi-cup-hot',label:'cup hot'},{icon:'bi bi-flower1',label:'flower'},
    {icon:'bi bi-palette',label:'palette'},{icon:'bi bi-brush',label:'brush'}
];

function renderIconPicker(filter) {
    const grid = document.getElementById('iconPickerGrid');
    const selected = document.getElementById('navIcon').value;
    const q = (filter || '').toLowerCase();
    let html = '';
    ICON_LIST.forEach(item => {
        if (q && !item.label.includes(q) && !item.icon.includes(q)) return;
        const isActive = item.icon === selected ? ' active' : '';
        html += `<div class="icon-picker-item${isActive}" data-icon="${item.icon}" onclick="selectIcon('${item.icon}','${item.label}')" title="${item.label}"><i class="${item.icon}"></i></div>`;
    });
    if (!html) html = '<div class="text-muted text-center py-2 w-100" style="font-size:.8rem">ไม่พบไอคอน</div>';
    grid.innerHTML = html;
}

function selectIcon(iconClass, label) {
    document.getElementById('navIcon').value = iconClass;
    document.getElementById('iconPreviewI').className = iconClass;
    document.getElementById('iconPreviewLabel').textContent = label || iconClass;
    document.getElementById('iconPreviewLabel').classList.remove('text-muted');
    // Highlight active
    document.querySelectorAll('#iconPickerGrid .icon-picker-item').forEach(el => {
        el.classList.toggle('active', el.dataset.icon === iconClass);
    });
}

function clearIconSelection() {
    document.getElementById('navIcon').value = '';
    document.getElementById('iconPreviewI').className = 'bi bi-cursor';
    document.getElementById('iconPreviewLabel').textContent = 'ยังไม่ได้เลือก';
    document.getElementById('iconPreviewLabel').classList.add('text-muted');
    document.querySelectorAll('#iconPickerGrid .icon-picker-item').forEach(el => {
        el.classList.remove('active');
    });
}

// Horizontal drag detection
let dragStartX = 0;
let dragItemEl = null;
const INDENT_THRESHOLD = 60; // px to trigger indent change

$(function() {
    App.requireAdmin();
    loadNavItems();
    loadPages();
    initNavForm();
    renderIconPicker();

    // Icon search filter
    $('#iconSearchInput').on('input', function() {
        renderIconPicker($(this).val());
    });

    // Track mouse during drag for horizontal indent
    document.addEventListener('mousemove', onDragMouseMove);
    document.addEventListener('touchmove', onDragTouchMove, { passive: false });

    // Auto-generate alias from title
    $('#navTitle').on('input', function() {
        if (!$('#navId').val()) {
            const alias = $(this).val().trim()
                .toLowerCase()
                .replace(/[ก-๙]+/g, '')
                .replace(/[^a-z0-9\-_ ]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            if (!$('#navAlias').data('manual')) {
                $('#navAlias').val(alias);
                $('#aliasPreview').text(alias || 'alias');
            }
        }
    });

    $('#navAlias').on('input', function() {
        const val = $(this).val().toLowerCase().replace(/[^a-z0-9\-_]/g, '');
        $(this).val(val);
        $(this).data('manual', true);
        $('#aliasPreview').text(val || 'alias');
    });
});

// ── Horizontal drag → indent/outdent ──
function onDragMouseMove(e) {
    if (!dragItemEl) return;
    handleHorizontalDrag(e.clientX);
}

function onDragTouchMove(e) {
    if (!dragItemEl) return;
    handleHorizontalDrag(e.touches[0].clientX);
}

function handleHorizontalDrag(clientX) {
    const dx = clientX - dragStartX;
    const currentIndent = parseInt(dragItemEl.dataset.indent) || 0;

    if (dx > INDENT_THRESHOLD && currentIndent < 1) {
        // Check if can indent (must have a sibling above)
        const $el = $(dragItemEl);
        const $prev = $el.prev('.nav-item-row');
        if ($prev.length) {
            setItemIndent(dragItemEl, 1);
            dragStartX = clientX; // reset threshold
            markChanged();
        }
    } else if (dx < -INDENT_THRESHOLD && currentIndent > 0) {
        setItemIndent(dragItemEl, 0);
        dragStartX = clientX;
        markChanged();
    }
}

function setItemIndent(el, level) {
    el.dataset.indent = level;
    el.classList.remove('nav-indent-0', 'nav-indent-1');
    el.classList.add('nav-indent-' + level);
}

function markChanged() {
    orderChanged = true;
    $('#btnSaveOrder').show();
}

// ── Indent / Outdent by button ──
function indentItem(id) {
    const $el = $(`#navList li[data-id="${id}"]`);
    const currentIndent = parseInt($el.attr('data-indent')) || 0;
    if (currentIndent >= 1) return; // max 1 level
    const $prev = $el.prev('.nav-item-row');
    if (!$prev.length) return; // first item can't indent
    setItemIndent($el[0], currentIndent + 1);
    markChanged();
}

function outdentItem(id) {
    const $el = $(`#navList li[data-id="${id}"]`);
    const currentIndent = parseInt($el.attr('data-indent')) || 0;
    if (currentIndent <= 0) return;
    setItemIndent($el[0], currentIndent - 1);
    markChanged();
}

// ── Data loading ──
async function loadNavItems() {
    try {
        const res = await API.getNavList();
        if (res.success) {
            navItems = res.data || [];
            renderNavList();
        }
    } catch (e) {
        console.error(e);
    } finally {
        $('#navLoading').hide();
    }
}

async function loadPages() {
    try {
        const res = await API.getPages({ per_page: 100 });
        if (res.success) {
            pages = res.data?.data || res.data || [];
        }
    } catch (e) {
        console.error(e);
    }
}

// ── Render flat list ──
function renderNavList() {
    const $list = $('#navList').empty();

    if (sortableInstance) {
        sortableInstance.destroy();
        sortableInstance = null;
    }

    if (!navItems.length) {
        $('#navEmpty').show();
        $('#navCount').text('');
        return;
    }
    $('#navEmpty').hide();
    $('#navCount').text(navItems.length + ' รายการ');

    // Build flat ordered list: parent → children → parent → children ...
    const parents = navItems.filter(n => !n.parent_id);
    const childMap = {};
    navItems.filter(n => n.parent_id).forEach(n => {
        if (!childMap[n.parent_id]) childMap[n.parent_id] = [];
        childMap[n.parent_id].push(n);
    });

    const flatList = [];
    parents.forEach(item => {
        flatList.push({ ...item, _indent: 0 });
        (childMap[item.id] || []).forEach(child => {
            flatList.push({ ...child, _indent: 1 });
        });
    });

    flatList.forEach(item => {
        $list.append(buildNavRow(item));
    });

    // Single Sortable for the flat list
    sortableInstance = new Sortable($list[0], {
        animation: 200,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onStart: function(evt) {
            dragItemEl = evt.item;
            dragStartX = evt.originalEvent
                ? (evt.originalEvent.clientX || (evt.originalEvent.touches && evt.originalEvent.touches[0].clientX) || 0)
                : 0;
        },
        onEnd: function(evt) {
            dragItemEl = null;
            validateIndents();
            markChanged();
        }
    });
}

// ── Ensure indent rules: no orphan children, first item can't be child ──
function validateIndents() {
    const items = $('#navList .nav-item-row');
    items.each(function(i) {
        const indent = parseInt(this.dataset.indent) || 0;
        if (indent > 0 && i === 0) {
            // First item must be parent
            setItemIndent(this, 0);
        }
        if (indent > 0) {
            // Must have a parent-level item somewhere above
            let hasParent = false;
            for (let j = i - 1; j >= 0; j--) {
                if ((parseInt(items[j].dataset.indent) || 0) === 0) {
                    hasParent = true;
                    break;
                }
            }
            if (!hasParent) setItemIndent(this, 0);
        }
    });
}

// ── Build single nav row ──
function buildNavRow(item) {
    const indent = item._indent || 0;
    const activeClass = item.is_active == 1 ? 'nav-status-active' : 'nav-status-inactive';
    const activeText = item.is_active == 1 ? '<i class="bi bi-check-circle me-1"></i>เปิด' : '<i class="bi bi-x-circle me-1"></i>ปิด';
    const icon = item.icon ? '<i class="' + item.icon + ' me-2"></i>' : '<i class="bi bi-link-45deg me-2"></i>';
    const aliasTag = item.alias ? `<span class="nav-alias-badge">page=${App.escapeHtml(item.alias)}</span>` : '';
    const url = item.page_title
        ? '<i class="bi bi-file-earmark-text me-1"></i>' + item.page_title
        : (item.url || '-');

    return `<li class="nav-item-row nav-indent-${indent}" data-id="${item.id}" data-indent="${indent}">
        <span class="drag-handle"><i class="bi bi-grip-vertical"></i></span>
        <div class="nav-indent-btns">
            <button type="button" class="btn btn-indent-left" onclick="outdentItem(${item.id})" title="เลื่อนออก (เมนูหลัก)">
                <i class="bi bi-arrow-bar-left"></i>
            </button>
            <button type="button" class="btn btn-indent-right" onclick="indentItem(${item.id})" title="เลื่อนเข้า (เมนูย่อย)">
                <i class="bi bi-arrow-bar-right"></i>
            </button>
        </div>
        <div class="nav-info">
            <div class="nav-title">${icon}${App.escapeHtml(item.title)} ${aliasTag}</div>
            <div class="nav-url">${url}</div>
        </div>
        <span class="nav-badge">
            <span class="${activeClass}">${activeText}</span>
        </span>
        <div class="nav-actions">
            <button class="btn btn-nav-open" onclick="window.open('${item.url || '#'}', '_blank')" title="เปิดดู">
                <i class="bi bi-box-arrow-up-right"></i>
            </button>
            <button class="btn btn-nav-edit" onclick="editNavItem(${item.id})" title="แก้ไข">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-nav-delete" onclick="deleteNavItem(${item.id}, '${App.escapeHtml(item.title)}')" title="ลบ">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </li>`;
}

// ── Save order: determine parent_id from indent hierarchy ──
async function saveNavOrder() {
    const items = [];
    let sortOrder = 1;
    let lastParentId = null;

    $('#navList .nav-item-row').each(function() {
        const id = parseInt($(this).data('id'));
        const indent = parseInt(this.dataset.indent) || 0;

        if (indent === 0) {
            lastParentId = id;
            items.push({ id, sort_order: sortOrder++, parent_id: null });
        } else {
            items.push({ id, sort_order: sortOrder++, parent_id: lastParentId });
        }
    });

    try {
        const res = await API.reorderNav(items);
        if (res.success) {
            App.success('บันทึกลำดับเรียบร้อย');
            orderChanged = false;
            $('#btnSaveOrder').hide();
            loadNavItems();
        } else {
            App.error(res.message || 'เกิดข้อผิดพลาด');
        }
    } catch (e) {
        App.error('เกิดข้อผิดพลาด');
    }
}

function openNavForm(data) {
    const isEdit = data && data.id;
    $('#navFormTitle').html(isEdit ? '<i class="bi bi-pencil-square me-2"></i>แก้ไขเมนู' : '<i class="bi bi-plus-circle me-2"></i>เพิ่มเมนู');
    $('#navForm')[0].reset();
    $('#navId').val('');
    $('#navAlias').data('manual', false);
    $('#aliasPreview').text('alias');
    clearIconSelection();
    $('#iconSearchInput').val('');
    renderIconPicker();

    // Populate parent dropdown
    const $parent = $('#navParentId').empty();
    $parent.append('<option value="">-- ไม่มี (เป็นเมนูหลัก) --</option>');
    navItems.filter(n => !n.parent_id).forEach(n => {
        if (!isEdit || n.id !== data.id) {
            $parent.append(`<option value="${n.id}">${App.escapeHtml(n.title)}</option>`);
        }
    });

    // Populate page dropdown
    const $pageSelect = $('#navPageId').empty();
    $pageSelect.append('<option value="">-- เลือกหน้าเพจ --</option>');
    pages.forEach(p => {
        $pageSelect.append(`<option value="${p.id}">${App.escapeHtml(p.title)} (${p.status})</option>`);
    });

    if (isEdit) {
        $('#navLinkType option[value="create_page"]').hide();
    } else {
        $('#navLinkType option[value="create_page"]').show();
    }

    if (isEdit) {
        $('#navId').val(data.id);
        $('#navTitle').val(data.title);
        $('#navAlias').val(data.alias || '');
        $('#navAlias').data('manual', true);
        $('#aliasPreview').text(data.alias || 'alias');
        $('#navUrl').val(data.url || '');
        $('#navIcon').val(data.icon || '');
        if (data.icon) {
            const found = ICON_LIST.find(i => i.icon === data.icon);
            selectIcon(data.icon, found ? found.label : data.icon);
        } else {
            clearIconSelection();
        }
        $('#navTarget').val(data.target || '_self');
        $('#navIsActive').prop('checked', data.is_active == 1);
        $('#navParentId').val(data.parent_id || '');

        if (data.page_id) {
            $('#navLinkType').val('page');
            $('#navPageId').val(data.page_id);
        } else {
            $('#navLinkType').val('url');
        }
        toggleLinkType();
    } else {
        $('#navLinkType').val('url');
        toggleLinkType();
    }

    $('#navFormModal').modal('show');
}

function toggleLinkType() {
    const type = $('#navLinkType').val();
    $('#urlGroup').toggle(type === 'url');
    $('#pageGroup').toggle(type === 'page');
    $('#createPageGroup').toggle(type === 'create_page');

    if (type === 'create_page') {
        $('#navAlias').attr('required', true);
    } else {
        $('#navAlias').removeAttr('required');
    }
}

function editNavItem(id) {
    const item = navItems.find(n => n.id === id);
    if (item) openNavForm(item);
}

async function deleteNavItem(id, title) {
    const result = await Swal.fire({
        title: 'ลบเมนู?',
        html: `คุณต้องการลบเมนู "<b>${title}</b>" ?<br><small class="text-danger">เมนูย่อยทั้งหมดจะถูกลบด้วย</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        try {
            const res = await API.deleteNav(id);
            if (res.success) {
                App.success('ลบเมนูเรียบร้อย');
                loadNavItems();
            } else {
                App.error(res.message || 'เกิดข้อผิดพลาด');
            }
        } catch (e) {
            App.error('เกิดข้อผิดพลาด');
        }
    }
}

function initNavForm() {
    $('#navForm').on('submit', async function(e) {
        e.preventDefault();

        const id = $('#navId').val();
        const linkType = $('#navLinkType').val();

        const data = {
            title: $('#navTitle').val().trim(),
            alias: $('#navAlias').val().trim() || null,
            url: linkType === 'url' ? $('#navUrl').val().trim() : '',
            page_id: linkType === 'page' ? ($('#navPageId').val() || null) : null,
            parent_id: $('#navParentId').val() || null,
            icon: $('#navIcon').val().trim(),
            target: $('#navTarget').val(),
            is_active: $('#navIsActive').is(':checked') ? 1 : 0
        };

        if (!data.title) {
            App.error('กรุณากรอกชื่อเมนู');
            return;
        }

        if (linkType === 'create_page') {
            if (!data.alias) {
                App.error('กรุณากรอก Alias สำหรับสร้างหน้าเพจใหม่');
                return;
            }
            data.create_page = true;
        }

        try {
            let res;
            if (id) {
                data.id = parseInt(id);
                res = await API.updateNav(data);
            } else {
                res = await API.createNav(data);
            }

            if (res.success) {
                $('#navFormModal').modal('hide');
                App.success(id ? 'แก้ไขเมนูเรียบร้อย' : 'เพิ่มเมนูเรียบร้อย');
                loadNavItems();
                if (linkType === 'create_page') {
                    loadPages();
                }
            } else {
                App.error(res.message || 'เกิดข้อผิดพลาด');
            }
        } catch (e) {
            App.error('เกิดข้อผิดพลาด');
        }
    });
}
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
