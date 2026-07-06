/**
 * SDAK-KS Main Application Script
 */
const App = {
    /**
     * Resolve image URL — external URLs pass through, relative paths get BASE_PATH prepended
     * Set bustCache=true to force the browser to reload the image after an update.
     */
    imgUrl(url, bustCache = false) {
        if (!url) return '';
        let resolved = url;
        if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('data:')) {
            resolved = url;
        } else {
            const base = (typeof BASE_PATH !== 'undefined' ? BASE_PATH : './');
            resolved = url.startsWith('/') ? (base + url.slice(1)) : (base + url);
        }
        if (bustCache && !resolved.startsWith('data:')) {
            const sep = resolved.includes('?') ? '&' : '?';
            return resolved + sep + 't=' + Date.now();
        }
        return resolved;
    },

    /**
     * Get the best available profile image for a user.
     * Priority: profile_image (user-uploaded) → google_picture (Google sync) → default avatar.
     */
    getProfileImage(user, bustCache = false) {
        const url = (user && (user.profile_image || user.google_picture) || '').trim();
        return url ? App.imgUrl(url, bustCache) : App.imgUrl('assets/images/default-avatar.png', bustCache);
    },

    /**
     * Initialize app
     */
    init() {
        this.updateNavbar();
        this.loadDynamicNav();
        this.initTooltips();
        this.loadDynamicFavicon();
        this.loadMemberTypes();
    },

    /**
     * Load dynamic favicon from settings
     */
    loadDynamicFavicon() {
        API.getSettings().then(function(result) {
            if (result.success && result.data) {
                // Favicon
                const el = document.getElementById('dynamic-favicon');
                if (el) {
                    const fav = result.data.logo_favicon || result.data.logo_web || '';
                    if (fav) {
                        el.href = fav.startsWith('http') ? fav : (BASE_PATH + fav);
                    }
                }

                // Navbar brand logo
                const logoWeb = result.data.logo_web || '';
                if (logoWeb) {
                    const brandIcon = document.getElementById('navbar-brand-icon');
                    if (brandIcon) {
                        const src = logoWeb.startsWith('http') ? logoWeb : (BASE_PATH + logoWeb);
                        brandIcon.innerHTML = '<img src="' + src + '" alt="logo" style="height:32px;width:auto;object-fit:contain;">';
                    }
                }

                // Navbar brand text
                const shortName = result.data.site_name_short || '';
                if (shortName) {
                    const brandText = document.getElementById('navbar-brand-text');
                    if (brandText) brandText.textContent = shortName;
                    const brandTextSm = document.getElementById('navbar-brand-text-sm');
                    if (brandTextSm) brandTextSm.textContent = shortName;
                }

                // Footer logo
                const footerLogoEl = document.getElementById('footer-logo');
                if (footerLogoEl && logoWeb) {
                    const src = logoWeb.startsWith('http') ? logoWeb : (BASE_PATH + logoWeb);
                    footerLogoEl.src = src;
                    footerLogoEl.style.display = 'inline';
                    const footerIcon = document.getElementById('footer-icon');
                    if (footerIcon) footerIcon.style.display = 'none';
                }

                // Footer brand text
                if (shortName) {
                    const ft = document.getElementById('footer-brand-text');
                    if (ft) ft.textContent = shortName;
                }

                // Footer description
                const siteName = result.data.site_name || '';
                const siteNameEn = result.data.site_name_en || '';
                const footerDesc = document.getElementById('footer-description');
                if (footerDesc && (siteName || siteNameEn)) {
                    let html = siteName || '';
                    if (siteNameEn) html += '<br>' + siteNameEn;
                    footerDesc.innerHTML = html;
                }

                // Footer copyright
                if (siteName && shortName) {
                    const fc = document.getElementById('footer-copyright-text');
                    if (fc) fc.textContent = siteName + ' (' + shortName + ')';
                }

                // Footer contact info
                const contactAddress = result.data.contact_address || '';
                const contactEmail = result.data.contact_email || '';
                const contactPhone = result.data.contact_phone || '';
                if (contactAddress) {
                    const addrEl = document.querySelector('#footer-address span');
                    if (addrEl) addrEl.textContent = contactAddress;
                }
                if (contactEmail) {
                    const emailEl = document.querySelector('#footer-email span');
                    if (emailEl) emailEl.textContent = contactEmail;
                }
                if (contactPhone) {
                    const phoneEl = document.getElementById('footer-phone');
                    if (phoneEl) {
                        phoneEl.style.display = '';
                        const phoneSpan = phoneEl.querySelector('span');
                        if (phoneSpan) phoneSpan.textContent = contactPhone;
                    }
                }

                // Footer embed stats code (Histats, etc.)
                const embedStats = result.data.embed_stats_code || '';
                if (embedStats) {
                    const embedEl = document.getElementById('footer-embed-stats');
                    if (embedEl) {
                        embedEl.innerHTML = embedStats;
                        // Execute script tags inside the embed
                        embedEl.querySelectorAll('script').forEach(function(oldScript) {
                            const newScript = document.createElement('script');
                            if (oldScript.src) {
                                newScript.src = oldScript.src;
                            } else {
                                newScript.textContent = oldScript.textContent;
                            }
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });
                    }
                }

                // Dynamic page title
                if (shortName || siteNameEn) {
                    const titleParts = document.title.split('|');
                    const pagePart = (titleParts[0] || '').trim();
                    const suffix = shortName + (siteNameEn ? ' ' + siteNameEn : '');
                    document.title = pagePart ? pagePart + ' | ' + suffix : suffix;
                }

                // Footer social links
                const socialEl = document.getElementById('footer-social');
                if (socialEl) {
                    const socials = [
                        { key: 'social_facebook',  icon: 'bi-facebook',  color: '#1877f2' },
                        { key: 'social_line',      icon: 'bi-line',      color: '#06c755' },
                        { key: 'social_youtube',   icon: 'bi-youtube',   color: '#ff0000' },
                        { key: 'social_tiktok',    icon: 'bi-tiktok',    color: '#000000' },
                        { key: 'social_instagram', icon: 'bi-instagram', color: '#e4405f' },
                        { key: 'social_website',   icon: 'bi-globe',     color: '#6c757d' },
                        { key: 'contact_email',    icon: 'bi-envelope',  color: '#6c757d', prefix: 'mailto:' },
                    ];
                    let socialHtml = '';
                    socials.forEach(function(s) {
                        const val = result.data[s.key] || '';
                        if (val) {
                            const href = s.prefix ? (s.prefix + val) : val;
                            socialHtml += '<a href="' + href + '" class="fs-4" target="_blank" rel="noopener" title="' + s.key.replace('social_', '').replace('contact_', '') + '"><i class="bi ' + s.icon + '"></i></a>';
                        }
                    });
                    if (socialHtml) {
                        socialEl.innerHTML = socialHtml;
                        socialEl.style.cssText = '';
                    }
                }

                // Theme color override from DB
                const themeColor = result.data.theme_color || '';
                if (themeColor) {
                    App.applyThemeColor(themeColor);
                }
            }
        }).catch(function() {});
    },

    /**
     * Apply theme color from a single hex color
     * Generates light, dark, and gradient variants automatically
     */
    applyThemeColor(hex) {
        if (!hex || !hex.match(/^#[0-9A-Fa-f]{6}$/)) return;

        // Parse hex to RGB
        const r = parseInt(hex.slice(1,3), 16);
        const g = parseInt(hex.slice(3,5), 16);
        const b = parseInt(hex.slice(5,7), 16);

        // Color utility functions
        const lighten = (c, pct) => Math.min(255, Math.round(c + (255 - c) * pct));
        const darken = (c, pct) => Math.max(0, Math.round(c * (1 - pct)));
        const toHex = (r, g, b) => '#' + [r, g, b].map(c => c.toString(16).padStart(2, '0')).join('');

        // Generate color palette from single primary color
        const light     = toHex(lighten(r, 0.25), lighten(g, 0.25), lighten(b, 0.25));
        const dark      = toHex(darken(r, 0.2),   darken(g, 0.2),   darken(b, 0.2));
        const vdark     = toHex(darken(r, 0.4),   darken(g, 0.4),   darken(b, 0.4));
        const vlight    = toHex(lighten(r, 0.5),   lighten(g, 0.5),   lighten(b, 0.5));
        const ultraDark = toHex(darken(r, 0.65),   darken(g, 0.65),   darken(b, 0.65));
        const footerDark= toHex(darken(r, 0.75),   darken(g, 0.75),   darken(b, 0.75));

        const root = document.documentElement;

        // Core palette
        root.style.setProperty('--primary', hex);
        root.style.setProperty('--primary-light', light);
        root.style.setProperty('--primary-dark', dark);
        root.style.setProperty('--primary-very-dark', vdark);
        root.style.setProperty('--primary-very-light', vlight);
        root.style.setProperty('--primary-ultra-dark', ultraDark);

        // Utility 
        root.style.setProperty('--light-bg', vlight + '1a');

        // Gradients
        root.style.setProperty('--gradient-primary', 'linear-gradient(135deg, ' + vdark + ' 0%, ' + hex + ' 50%, ' + light + ' 100%)');
        root.style.setProperty('--gradient-hero', 'linear-gradient(135deg, ' + ultraDark + ' 0%, ' + vdark + ' 30%, ' + hex + ' 55%, ' + light + ' 80%, ' + vlight + ' 100%)');
        root.style.setProperty('--gradient-component', 'linear-gradient(135deg, ' + vdark + ', ' + light + ')');
        root.style.setProperty('--gradient-footer', 'linear-gradient(135deg, ' + ultraDark + ' 0%, ' + vdark + ' 100%)');
        root.style.setProperty('--gradient-auth', 'linear-gradient(135deg, ' + ultraDark + ' 0%, ' + hex + ' 50%, ' + light + ' 100%)');
        root.style.setProperty('--footer-bottom-bg', footerDark);

        // AdminLTE CSS variables
        root.style.setProperty('--adminlte-primary', hex);
        root.style.setProperty('--adminlte-info', light);
    },

    /**
     * Update navbar based on login state
     */
    updateNavbar() {
        const user = API.getUser();
        const $authNav = $('#auth-nav');
        const $userNav = $('#user-nav');

        if (user) {
            $authNav.hide();
            $userNav.show();
            $('#nav-username').text(user.full_name || user.username);
            const avatarSrc = App.getProfileImage(user, true);
            $('#nav-avatar').attr('src', avatarSrc);
            if (user.role === 'admin') {
                $('#nav-admin-link').show();
                $('#nav-finance-link').show();
                $('#nav-payment-approval-link').show();
            } else {
                // Check finance manager permission for non-admin users
                API.getMyFinancePermissions().then(res => {
                    if (res.success && res.data && res.data.is_finance_manager) {
                        $('#nav-finance-link').show();
                        $('#nav-payment-approval-link').show();
                        // Load pending payments badge
                        API.getPendingPayments({ status: 'pending' }).then(ppRes => {
                            if (ppRes.success && ppRes.data && ppRes.data.length > 0) {
                                $('#nav-pending-payments-badge').text(ppRes.data.length).show();
                            }
                        }).catch(() => {});
                    }
                }).catch(() => {});
            }
            // Load notification badges
            this.loadNotifications();
            // Auto-refresh notifications every 60 seconds
            if (!this._notifInterval) {
                this._notifInterval = setInterval(() => this.loadNotifications(), 60000);
            }
        } else {
            $authNav.show();
            $userNav.hide();
        }
    },

    /**
     * Load notification counts and update badge
     */
    async loadNotifications() {
        try {
            const res = await API.getNotifications();
            if (!res.success) return;
            const d = res.data;
            const $badge = $('#notif-badge');
            const $list  = $('#notif-list');
            const basePath = (typeof BASE_PATH !== 'undefined' ? BASE_PATH : './');

            if (d.total > 0) {
                $badge.text(d.total > 99 ? '99+' : d.total).show();
            } else {
                $badge.hide();
            }

            // Build dropdown items (dual classes for BS4+BS5 compatibility)
            let html = '';
            if (d.unpaid_fees > 0) {
                html += '<a class="dropdown-item py-2" href="' + basePath + 'member/?page=fees">' +
                    '<i class="bi bi-cash-coin text-danger mr-2 me-2"></i>' +
                    'ค่าธรรมเนียมค้างชำระ <span class="badge badge-danger bg-danger badge-pill rounded-pill float-right float-end">' + d.unpaid_fees + '</span></a>';
            }
            if (d.new_activities > 0) {
                html += '<a class="dropdown-item py-2" href="' + basePath + 'web/?page=activities">' +
                    '<i class="bi bi-calendar-event text-primary mr-2 me-2"></i>' +
                    'กิจกรรมใหม่ <span class="badge badge-primary bg-primary badge-pill rounded-pill float-right float-end">' + d.new_activities + '</span></a>';
            }
            if (d.pending_registrations > 0) {
                html += '<a class="dropdown-item py-2" href="' + basePath + 'web/?page=activities">' +
                    '<i class="bi bi-hourglass-split text-warning mr-2 me-2"></i>' +
                    'รอการอนุมัติ <span class="badge badge-warning bg-warning badge-pill rounded-pill float-right float-end">' + d.pending_registrations + '</span></a>';
            }

            if (html === '') {
                html = '<span class="dropdown-item-text text-muted text-center py-2" style="font-size:.85rem;">ไม่มีการแจ้งเตือน</span>';
            }

            $list.html(html);
        } catch(e) {
            // silently ignore notification errors
        }
    },

    /**
     * Initialize Bootstrap tooltips (BS4 & BS5 compatible)
     */
    initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            // Bootstrap 5
            $('[data-bs-toggle="tooltip"]').each(function() {
                new bootstrap.Tooltip(this);
            });
        } else if ($.fn.tooltip) {
            // Bootstrap 4
            $('[data-toggle="tooltip"]').tooltip();
        }
    },

    /**
     * Resolve nav URL — replace ./ prefix with BASE_PATH for correct relative paths
     */
    resolveNavUrl(url) {
        if (!url) return '#';
        // External URLs — keep as-is
        if (url.startsWith('http://') || url.startsWith('https://')) return url;
        // Replace leading ./ with BASE_PATH
        const base = (typeof BASE_PATH !== 'undefined') ? BASE_PATH : './';
        if (url.startsWith('./')) return base + url.substring(2);
        return url;
    },

    /**
     * Load dynamic navigation from API
     */
    loadDynamicNav() {
        const $nav = $('#dynamic-nav');
        if (!$nav.length) return;

        // Detect Bootstrap version: BS5 has bootstrap.Dropdown, BS4 has $.fn.dropdown
        const isBS5 = (typeof bootstrap !== 'undefined' && bootstrap.Dropdown);
        const toggleAttr = isBS5 ? 'data-bs-toggle="dropdown"' : 'data-toggle="dropdown"';

        API.getNavTree().then(res => {
            if (res.success && res.data) {
                $nav.empty();
                res.data.forEach(item => {
                    const icon = item.icon ? `<i class="${this.escapeHtml(item.icon)} me-1 mr-1"></i> ` : '';
                    const target = item.target === '_blank' ? ' target="_blank"' : '';
                    const url = this.resolveNavUrl(item.url);

                    if (item.children && item.children.length) {
                        const $dd = $(`<li class="nav-item dropdown"></li>`);
                        $dd.append(`<a class="nav-link dropdown-toggle" href="#" role="button" ${toggleAttr} aria-expanded="false">${icon}${this.escapeHtml(item.title)}</a>`);
                        const $menu = $('<ul class="dropdown-menu"></ul>');
                        item.children.forEach(child => {
                            const cIcon = child.icon ? `<i class="${this.escapeHtml(child.icon)} me-1 mr-1"></i> ` : '';
                            const cTarget = child.target === '_blank' ? ' target="_blank"' : '';
                            const cUrl = this.resolveNavUrl(child.url);
                            $menu.append(`<li><a class="dropdown-item" href="${this.escapeHtml(cUrl)}"${cTarget}>${cIcon}${this.escapeHtml(child.title)}</a></li>`);
                        });
                        $dd.append($menu);
                        $nav.append($dd);
                    } else {
                        $nav.append(`<li class="nav-item"><a class="nav-link" href="${this.escapeHtml(url)}"${target}>${icon}${this.escapeHtml(item.title)}</a></li>`);
                    }
                });
            }
        }).catch(() => {});
    },

    /**
     * Show loading
     */
    showLoading() {
        if ($('#loading-overlay').length === 0) {
            $('body').append('<div id="loading-overlay" class="loading-overlay"><div class="spinner-custom"></div></div>');
        }
        $('#loading-overlay').show();
    },

    /**
     * Hide loading
     */
    hideLoading() {
        $('#loading-overlay').hide();
    },

    /**
     * Format date Thai
     */
    formatDate(dateStr) {
        if (!dateStr) return '-';
        const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const d = new Date(dateStr);
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear() + 543}`;
    },

    /**
     * Format datetime Thai
     */
    formatDateTime(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return this.formatDate(dateStr) + ` ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} น.`;
    },

    /**
     * Format number
     */
    formatNumber(num) {
        return Number(num).toLocaleString('th-TH');
    },

    /**
     * Format currency
     */
    formatCurrency(num) {
        return Number(num).toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' บาท';
    },

    /**
     * Escape HTML entities for safe inline use
     */
    escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    },

    // ─── Cached member type data ───
    _memberTypeLabels: null,       // { ordinary: 'สมาชิกสามัญ', … }
    _memberTypeLabelsShort: null,  // { ordinary: 'สามัญ', … }
    _memberTypeData: null,         // full rows array

    /**
     * Load member types from API (called once, then cached)
     */
    async loadMemberTypes() {
        if (this._memberTypeLabels) return;
        try {
            const res = await API.getMemberTypes();
            if (res.success && Array.isArray(res.data)) {
                this._memberTypeData = res.data;
                this._memberTypeLabels = {};
                this._memberTypeLabelsShort = {};
                res.data.forEach(t => {
                    this._memberTypeLabels[t.type_key] = t.label;
                    this._memberTypeLabelsShort[t.type_key] = t.label_short || t.label;
                });
            }
        } catch (e) { /* use fallback */ }
        // Ensure fallback always exists
        if (!this._memberTypeLabels) {
            this._memberTypeLabels = { ordinary:'สมาชิกสามัญ', associate:'สมาชิกวิสามัญ', affiliate:'สมาชิกสมทบ', honorary:'สมาชิกกิตติมศักดิ์' };
            this._memberTypeLabelsShort = { ordinary:'สามัญ', associate:'วิสามัญ', affiliate:'สมทบ', honorary:'กิตติมศักดิ์' };
        }
    },

    /**
     * Get member type label (full). Synchronous — requires loadMemberTypes() called beforehand.
     */
    getMemberTypeLabel(type) {
        if (!type) return 'ยังไม่ระบุ';
        const labels = this._memberTypeLabels || {
            'ordinary': 'สมาชิกสามัญ',
            'associate': 'สมาชิกวิสามัญ',
            'affiliate': 'สมาชิกสมทบ',
            'honorary': 'สมาชิกกิตติมศักดิ์'
        };
        return labels[type] || type;
    },

    /**
     * Get member type label (short)
     */
    getMemberTypeLabelShort(type) {
        if (!type) return 'ยังไม่ระบุ';
        const labels = this._memberTypeLabelsShort || {
            'ordinary': 'สามัญ',
            'associate': 'วิสามัญ',
            'affiliate': 'สมทบ',
            'honorary': 'กิตติมศักดิ์'
        };
        return labels[type] || type;
    },

    /**
     * Get member type badge
     */
    getMemberTypeBadge(type) {
        if (!type) return '<span class="badge bg-secondary">ยังไม่ระบุ</span>';
        return `<span class="badge badge-${type}">${this.getMemberTypeLabel(type)}</span>`;
    },

    /**
     * Build <option> HTML for member type selects
     * @param {boolean} short - use short labels
     * @param {boolean} withEmpty - prepend empty "-- เลือก --" option
     */
    getMemberTypeOptions(short = true, withEmpty = false) {
        const labels = short ? this._memberTypeLabelsShort : this._memberTypeLabels;
        const fallback = short
            ? { ordinary:'สามัญ', associate:'วิสามัญ', affiliate:'สมทบ', honorary:'กิตติมศักดิ์' }
            : { ordinary:'สมาชิกสามัญ', associate:'สมาชิกวิสามัญ', affiliate:'สมาชิกสมทบ', honorary:'สมาชิกกิตติมศักดิ์' };
        const map = labels || fallback;
        let html = withEmpty ? '<option value="">-- เลือก --</option>' : '';
        Object.entries(map).forEach(([k, v]) => {
            html += `<option value="${k}">${this.escapeHtml(v)}</option>`;
        });
        return html;
    },

    /**
     * Get role badge
     */
    getRoleBadge(role) {
        if (!role) return '';
        const map = {
            'admin': { label: 'ผู้ดูแลระบบ', color: 'danger' },
            'member': { label: 'สมาชิก', color: 'info' }
        };
        const r = map[role] || { label: role, color: 'secondary' };
        return `<span class="badge bg-${r.color}">${r.label}</span>`;
    },

    /**
     * Get action label for activity logs
     */
    getActionLabel(action) {
        const labels = {
            'login': 'เข้าสู่ระบบ',
            'logout': 'ออกจากระบบ',
            'register': 'สมัครสมาชิก',
            'create': 'สร้าง',
            'update': 'แก้ไข',
            'delete': 'ลบ',
            'approve_member': 'อนุมัติสมาชิก',
            'reject_member': 'ปฏิเสธสมาชิก',
            'suspend_member': 'ระงับสมาชิก',
            'cancel_member': 'ยกเลิกสมาชิก',
            'activate_member': 'เปิดใช้งานสมาชิก',
            'update_member': 'แก้ไขสมาชิก',
            'update_profile': 'แก้ไขโปรไฟล์',
            'approve_registration': 'อนุมัติลงทะเบียน',
            'reject_registration': 'ปฏิเสธลงทะเบียน',
            'cancel_registration': 'ยกเลิกลงทะเบียน',
            'reorder': 'จัดเรียง'
        };
        return labels[action] || action;
    },

    /**
     * Get module label for activity logs
     */
    getModuleLabel(module) {
        const labels = {
            'auth': 'ระบบยืนยันตัวตน',
            'member': 'สมาชิก',
            'news': 'ข่าวสาร',
            'activity': 'กิจกรรม',
            'page': 'หน้าเพจ',
            'nav': 'เมนู'
        };
        return labels[module] || module;
    },

    /**
     * Get module badge color
     */
    getModuleBadge(module) {
        const colors = {
            'auth': 'primary',
            'member': 'info',
            'news': 'success',
            'activity': 'warning',
            'page': 'secondary',
            'nav': 'dark'
        };
        const color = colors[module] || 'secondary';
        return `<span class="badge bg-${color}">${this.getModuleLabel(module)}</span>`;
    },

    /**
     * Get status label
     */
    getStatusLabel(status) {
        const labels = {
            'active': 'ใช้งาน',
            'pending': 'รออนุมัติ',
            'cancelled': 'ยกเลิก',
            'suspended': 'ระงับ',
            'draft': 'แบบร่าง',
            'published': 'เผยแพร่',
            'archived': 'เก็บถาวร',
            'open': 'เปิดรับ',
            'closed': 'ปิดรับ',
            'approved': 'อนุมัติ',
            'rejected': 'ปฏิเสธ',
            'paid': 'ชำระแล้ว',
            'not_required': 'ไม่ต้องชำระ'
        };
        return labels[status] || status;
    },

    /**
     * Get status badge
     */
    getStatusBadge(status) {
        const colors = {
            'active': 'success', 'pending': 'warning', 'cancelled': 'danger',
            'suspended': 'secondary', 'draft': 'secondary', 'published': 'success',
            'open': 'success', 'closed': 'danger', 'approved': 'success',
            'rejected': 'danger', 'paid': 'success', 'not_required': 'light'
        };
        const color = colors[status] || 'secondary';
        return `<span class="badge bg-${color}">${this.getStatusLabel(status)}</span>`;
    },

    /**
     * Default image placeholder
     */
    defaultImage(type = 'general') {
        const placeholders = {
            'profile': 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="%231a5276" width="100" height="100"/><text x="50" y="55" text-anchor="middle" fill="white" font-size="40">👤</text></svg>',
            'news': 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200"><rect fill="%23e0e0e0" width="400" height="200"/><text x="200" y="105" text-anchor="middle" fill="%23999" font-size="16">ไม่มีรูปภาพ</text></svg>',
            'activity': 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200"><rect fill="%23d5e8d4" width="400" height="200"/><text x="200" y="105" text-anchor="middle" fill="%23666" font-size="16">กิจกรรม</text></svg>'
        };
        return placeholders[type] || placeholders['news'];
    },

    /**
     * Truncate text
     */
    truncate(text, length = 100) {
        if (!text) return '';
        return text.length > length ? text.substring(0, length) + '...' : text;
    },

    /**
     * Escape HTML entities
     */
    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    /**
     * Confirm dialog
     */
    async confirm(title, text, icon = 'warning') {
        const result = await Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonColor: '#1a5276',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        });
        return result.isConfirmed;
    },

    /**
     * Success alert
     */
    success(message) {
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    },

    /**
     * Error alert
     */
    error(message) {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: message
        });
    },

    /**
     * Handle API response
     */
    handleResponse(result, successMsg = null) {
        if (result.success) {
            if (successMsg || result.message) {
                this.success(successMsg || result.message);
            }
            return true;
        } else {
            this.error(result.message || 'เกิดข้อผิดพลาด');
            return false;
        }
    },

    /**
     * Logout
     */
    async doLogout() {
        const confirmed = await this.confirm('ออกจากระบบ', 'คุณต้องการออกจากระบบหรือไม่?', 'question');
        if (confirmed) {
            await API.logout();
            window.location.href = API.baseUrl;
        }
    },

    /**
     * Require login
     */
    requireLogin() {
        if (!API.isLoggedIn()) {
            window.location.href = API.baseUrl + 'auth/?page=login';
            return false;
        }
        return true;
    },

    /**
     * Require admin
     */
    requireAdmin() {
        if (!API.isAdmin()) {
            window.location.href = API.baseUrl;
            return false;
        }
        return true;
    },

    /**
     * Build pagination HTML
     */
    buildPagination(selector, pagination, callback) {
        const $el = $(selector);
        if (!pagination || pagination.total_pages <= 1) { $el.html(''); return; }

        // Store callback globally so onclick can reference it
        const cbName = '_pgCb_' + selector.replace(/[^a-zA-Z0-9]/g, '');
        window[cbName] = callback;

        let html = '<ul class="pagination pagination-sm justify-content-center mb-0">';
        const { page, total_pages } = pagination;

        html += `<li class="page-item ${page <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="${cbName}(${page - 1}); return false;">«</a></li>`;

        const start = Math.max(1, page - 2);
        const end = Math.min(total_pages, page + 2);

        if (start > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="${cbName}(1); return false;">1</a></li>`;
            if (start > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for (let i = start; i <= end; i++) {
            html += `<li class="page-item ${i === page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="${cbName}(${i}); return false;">${i}</a></li>`;
        }

        if (end < total_pages) {
            if (end < total_pages - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            html += `<li class="page-item"><a class="page-link" href="#" onclick="${cbName}(${total_pages}); return false;">${total_pages}</a></li>`;
        }

        html += `<li class="page-item ${page >= total_pages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="${cbName}(${page + 1}); return false;">»</a></li>`;
        html += '</ul>';

        $el.html(html);
    }
};

// Initialize on DOM ready
$(document).ready(function() {
    App.init();
});
