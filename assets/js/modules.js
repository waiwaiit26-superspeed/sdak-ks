/**
 * SDAK-KS Module/Shortcode System
 * Processes WordPress-like shortcodes in page content
 * 
 * Supported shortcodes:
 *   [news]                    — ข่าวสารล่าสุด (default 6 items)
 *   [news limit=8]            — ข่าวสารล่าสุด 8 รายการ
 *   [activities]              — กิจกรรมล่าสุด (default 6 items)
 *   [activities limit=4]      — กิจกรรมล่าสุด 4 รายการ
 *   [activities upcoming=1]   — กิจกรรมที่กำลังจะมาถึง
 */
const Modules = {

    /**
     * Parse and render all shortcodes in content
     * @param {string} html — raw HTML content from DB
     * @returns {string} — HTML with shortcode placeholders replaced
     */
    parse(html) {
        if (!html) return html;

        // Match [module_name attr1=val1 attr2=val2]
        const regex = /\[(\w+)([^\]]*)\]/g;
        let result = html;
        const modules = [];

        result = result.replace(regex, (match, name, attrStr) => {
            const attrs = this.parseAttributes(attrStr);
            const id = `module-${name}-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;

            if (this.renderers[name]) {
                modules.push({ id, name, attrs });
                return `<div id="${id}" class="module-container module-${name} my-4">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <span class="ms-2 text-muted">กำลังโหลด...</span>
                    </div>
                </div>`;
            }

            // Unknown shortcode — leave as is
            return match;
        });

        // Store modules to render after DOM insert
        this._pendingModules = modules;
        return result;
    },

    /**
     * Render all pending modules (call after inserting HTML into DOM)
     */
    async renderPending() {
        if (!this._pendingModules || this._pendingModules.length === 0) return;

        const promises = this._pendingModules.map(mod => {
            const renderer = this.renderers[mod.name];
            if (renderer) {
                return renderer.call(this, mod.id, mod.attrs);
            }
        });

        await Promise.all(promises);
        this._pendingModules = [];
    },

    /**
     * Parse shortcode attributes string
     * e.g. " limit=6 upcoming=1" → { limit: "6", upcoming: "1" }
     */
    parseAttributes(str) {
        const attrs = {};
        if (!str) return attrs;

        const regex = /(\w+)=["']?([^"'\s]+)["']?/g;
        let m;
        while ((m = regex.exec(str)) !== null) {
            attrs[m[1]] = m[2];
        }
        return attrs;
    },

    // ─── Module Renderers ─────────────────────────────────────────

    renderers: {

        /**
         * [news] — Render latest news
         */
        async news(containerId, attrs) {
            const limit = parseInt(attrs.limit) || 6;
            const $el = $(`#${containerId}`);

            try {
                const res = await API.getNewsList({ page: 1, per_page: limit });

                if (!res.success || !res.data || res.data.length === 0) {
                    $el.html(`
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-newspaper fs-3 d-block mb-2"></i>
                            ยังไม่มีข่าวสาร
                        </div>`);
                    return;
                }

                let html = `
                <div class="module-news-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0"><i class="bi bi-newspaper me-2"></i>ข่าวสาร</h4>
                        <a href="${App.resolveNavUrl('./web/?page=news')}" class="btn btn-outline-primary btn-sm">
                            ดูทั้งหมด <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="row g-3">`;

                res.data.forEach(news => {
                    const img = news.cover_image ? App.imgUrl(news.cover_image) : App.resolveNavUrl('./assets/images/default-news.jpg');
                    const date = App.formatDate(news.published_at || news.created_at);
                    const excerpt = news.excerpt || (news.content ? $('<div>').html(news.content).text().substring(0, 100) + '...' : '');

                    html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0" onclick="location.href='${App.resolveNavUrl('./web/?page=news-detail&id=' + news.id)}'" style="cursor:pointer">
                            <img src="${img}" class="card-img-top" style="height:180px;object-fit:cover"
                                alt="${news.title}" onerror="this.src='${App.resolveNavUrl('./assets/images/default-news.jpg')}'">
                            <div class="card-body">
                                <h6 class="card-title mb-1">${news.title}</h6>
                                <p class="card-text text-muted small mb-0">${excerpt}</p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>${date}
                                    <i class="bi bi-eye ms-2 me-1"></i>${App.formatNumber(news.views || 0)}
                                </small>
                            </div>
                        </div>
                    </div>`;
                });

                html += `</div></div>`;
                $el.html(html);

            } catch (e) {
                console.error('Module [news] error:', e);
                $el.html('<div class="alert alert-warning">ไม่สามารถโหลดข่าวสารได้</div>');
            }
        },

        /**
         * [activities] — Render latest activities
         */
        async activities(containerId, attrs) {
            const limit = parseInt(attrs.limit) || 6;
            const $el = $(`#${containerId}`);
            const params = { page: 1, per_page: limit };
            if (attrs.upcoming === '1') params.upcoming = 1;

            try {
                const res = await API.getActivities(params);

                if (!res.success || !res.data || res.data.length === 0) {
                    $el.html(`
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-event fs-3 d-block mb-2"></i>
                            ยังไม่มีกิจกรรม
                        </div>`);
                    return;
                }

                let html = `
                <div class="module-activities-wrapper">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0"><i class="bi bi-calendar-event me-2"></i>กิจกรรม</h4>
                        <a href="${App.resolveNavUrl('./web/?page=activities')}" class="btn btn-outline-primary btn-sm">
                            ดูทั้งหมด <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="row g-3">`;

                res.data.forEach(act => {
                    const img = act.cover_image ? App.imgUrl(act.cover_image) : App.resolveNavUrl('./assets/images/default-activity.jpg');
                    const startDate = App.formatDate(act.start_date);
                    const endDate = act.end_date ? ' - ' + App.formatDate(act.end_date) : '';
                    const fee = act.has_fee && act.fee_amount > 0 ? App.formatCurrency(act.fee_amount) : 'ฟรี';
                    const spots = act.max_participants > 0
                        ? `<span class="badge bg-info">${act.approved_count || 0}/${act.max_participants}</span>`
                        : '<span class="badge bg-success">ไม่จำกัด</span>';

                    html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0" onclick="location.href='${App.resolveNavUrl('./web/?page=activity-detail&id=' + act.id)}'" style="cursor:pointer">
                            <img src="${img}" class="card-img-top" style="height:180px;object-fit:cover"
                                alt="${act.title}" onerror="this.src='${App.resolveNavUrl('./assets/images/default-activity.jpg')}'">
                            <div class="card-body">
                                <h6 class="card-title mb-1">${act.title}</h6>
                                <p class="card-text small text-muted mb-1">
                                    <i class="bi bi-calendar me-1"></i>${startDate}${endDate}
                                </p>
                                ${act.location ? `<p class="card-text small text-muted mb-0"><i class="bi bi-geo-alt me-1"></i>${act.location}</p>` : ''}
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0 d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="bi bi-cash me-1"></i>${fee}</small>
                                ${spots}
                            </div>
                        </div>
                    </div>`;
                });

                html += `</div></div>`;
                $el.html(html);

            } catch (e) {
                console.error('Module [activities] error:', e);
                $el.html('<div class="alert alert-warning">ไม่สามารถโหลดกิจกรรมได้</div>');
            }
        }
    }
};
