/**
 * SDAK-KS API Client
 * Token-based API communication layer
 */
const API = {
    baseUrl: (typeof BASE_PATH !== 'undefined') ? BASE_PATH : './',

    /**
     * Get stored auth token
     */
    getToken() {
        return localStorage.getItem('sdak_token');
    },

    /**
     * Get stored user data
     */
    getUser() {
        const data = localStorage.getItem('sdak_user');
        return data ? JSON.parse(data) : null;
    },

    /**
     * Check if user is logged in
     */
    isLoggedIn() {
        return !!this.getToken();
    },

    /**
     * Check if user is admin
     */
    isAdmin() {
        const user = this.getUser();
        return user && user.role === 'admin';
    },

    /**
     * Save auth data
     */
    saveAuth(data) {
        localStorage.setItem('sdak_token', data.token);
        localStorage.setItem('sdak_refresh_token', data.refresh_token);
        localStorage.setItem('sdak_user', JSON.stringify(data.user));
    },

    /**
     * Clear auth data
     */
    clearAuth() {
        localStorage.removeItem('sdak_token');
        localStorage.removeItem('sdak_refresh_token');
        localStorage.removeItem('sdak_user');
    },

    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const url = this.baseUrl + endpoint;
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        const token = this.getToken();
        if (token) {
            headers['X-Auth-Token'] = token;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json();

            // Handle 401 - try refresh (skip for login/register actions)
            if (response.status === 401 && !options._retry) {
                const isAuthAction = url.indexOf('controller=auth&action=login') !== -1
                    || url.indexOf('controller=auth&action=register') !== -1
                    || url.indexOf('controller=auth&action=google-login') !== -1
                    || url.indexOf('controller=auth&action=complete-google-register') !== -1
                    || url.indexOf('controller=auth&action=forget-password') !== -1
                    || url.indexOf('controller=auth&action=reset-password') !== -1;

                if (isAuthAction) {
                    // Auth actions: return the error data directly (don't try refresh)
                    return data;
                }

                const refreshed = await this.refreshToken();
                if (refreshed) {
                    options._retry = true;
                    headers['X-Auth-Token'] = this.getToken();
                    const retryResponse = await fetch(url, { ...options, headers });
                    return await retryResponse.json();
                } else {
                    this.clearAuth();
                    if (window.location.href.indexOf('login') === -1 && window.location.href.indexOf('register') === -1) {
                        window.location.href = this.baseUrl + 'auth/?page=login';
                        // Never resolve — prevent error popups while redirecting
                        return new Promise(() => {});
                    }
                    return data;
                }
            }

            return data;
        } catch (error) {
            console.error('API Error:', error, 'URL:', url);
            return { success: false, message: 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง' };
        }
    },

    /**
     * Build API URL:  api/?controller=X&action=Y&extra_params
     */
    apiUrl(controller, action, params = {}) {
        const p = new URLSearchParams({ controller, action, ...params });
        return 'api/?' + p.toString();
    },

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? endpoint + (endpoint.includes('?') ? '&' : '?') + queryString : endpoint;
        return this.request(url, { method: 'GET' });
    },

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    /**
     * Upload file
     */
    async upload(file, type = 'general') {
        const formData = new FormData();
        formData.append('file', file);

        const token = this.getToken();
        const headers = {};
        if (token) {
            headers['X-Auth-Token'] = token;
        }

        try {
            const response = await fetch(this.baseUrl + this.apiUrl('upload', 'image', { type }), {
                method: 'POST',
                headers,
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { success: false, message: 'เกิดข้อผิดพลาดในการอัปโหลด' };
        }
    },

    /**
     * Upload logo (PNG with crop support)
     */
    async uploadLogo(file, cropData = null) {
        const formData = new FormData();
        formData.append('file', file);
        if (cropData) {
            formData.append('cropX', Math.round(cropData.x));
            formData.append('cropY', Math.round(cropData.y));
            formData.append('cropWidth', Math.round(cropData.width));
            formData.append('cropHeight', Math.round(cropData.height));
        }

        const token = this.getToken();
        const headers = {};
        if (token) headers['X-Auth-Token'] = token;

        try {
            const response = await fetch(this.baseUrl + this.apiUrl('upload', 'logo'), {
                method: 'POST',
                headers,
                body: formData
            });
            return await response.json();
        } catch (error) {
            return { success: false, message: 'เกิดข้อผิดพลาดในการอัปโหลด' };
        }
    },

    /**
     * Refresh token
     */
    async refreshToken() {
        const refreshToken = localStorage.getItem('sdak_refresh_token');
        if (!refreshToken) return false;

        try {
            const response = await fetch(this.baseUrl + this.apiUrl('auth', 'refresh'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ refresh_token: refreshToken })
            });
            const data = await response.json();

            if (data.success) {
                localStorage.setItem('sdak_token', data.data.token);
                localStorage.setItem('sdak_refresh_token', data.data.refresh_token);
                return true;
            }
        } catch (e) { }
        return false;
    },

    // ── AUTH ──────────────────────────────────────────────
    async login(login, password) {
        return this.post(this.apiUrl('auth', 'login'), { login, password });
    },

    async register(data) {
        return this.post(this.apiUrl('auth', 'register'), data);
    },

    async googleLogin(googleToken) {
        return this.post(this.apiUrl('auth', 'google-login'), { google_token: googleToken });
    },

    async completeGoogleRegister(googleToken, memberType, paymentSlip = null, nameData = null) {
        const data = { google_token: googleToken, member_type: memberType };
        if (paymentSlip) data.payment_slip = paymentSlip;
        if (nameData) {
            data.prefix = nameData.prefix || '';
            data.first_name = nameData.first_name || '';
            data.last_name = nameData.last_name || '';
        }
        return this.post(this.apiUrl('auth', 'complete-google-register'), data);
    },

    async logout() {
        const result = await this.post(this.apiUrl('auth', 'logout'));
        this.clearAuth();
        return result;
    },

    async me() {
        return this.get(this.apiUrl('auth', 'me'));
    },

    // ── MEMBERS ──────────────────────────────────────────
    async getProfile(id = null) {
        const params = id ? { id } : {};
        return this.get(this.apiUrl('member', 'profile'), params);
    },

    async updateProfile(data) {
        return this.post(this.apiUrl('member', 'update'), data);
    },

    async getMembers(params = {}) {
        return this.get(this.apiUrl('member', 'list'), params);
    },

    async approveMember(userId, action, reason = '', memberNumber = '') {
        return this.post(this.apiUrl('member', 'approve'), { user_id: userId, action, reason, member_number: memberNumber });
    },

    async checkFeeStatus(userId) {
        return this.get(this.apiUrl('member', 'check-fee-status') + '&user_id=' + userId);
    },

    async confirmFeePayment(userId, issueReceipt = false) {
        return this.post(this.apiUrl('member', 'confirm-fee-payment'), { user_id: userId, issue_receipt: issueReceipt ? 1 : 0 });
    },

    async getNextMemberNumber() {
        return this.get(this.apiUrl('member', 'next-member-number'));
    },

    async createMember(data) {
        return this.post(this.apiUrl('member', 'create'), data);
    },

    async deleteMember(userId) {
        return this.post(this.apiUrl('member', 'delete'), { user_id: userId });
    },

    async importMembers(members) {
        return this.post(this.apiUrl('member', 'import'), { members });
    },

    async getMemberStatistics() {
        return this.get(this.apiUrl('member', 'statistics'));
    },

    // ── NEWS ─────────────────────────────────────────────
    async getNewsList(params = {}) {
        return this.get(this.apiUrl('news', 'list'), params);
    },

    async getNewsDetail(id) {
        return this.get(this.apiUrl('news', 'detail'), { id });
    },

    async createNews(data) {
        return this.post(this.apiUrl('news', 'create'), data);
    },

    async updateNews(data) {
        return this.post(this.apiUrl('news', 'update'), data);
    },

    async deleteNews(id) {
        return this.post(this.apiUrl('news', 'delete'), { id });
    },

    // ── ACTIVITIES ───────────────────────────────────────
    async getActivities(params = {}) {
        return this.get(this.apiUrl('activity', 'list'), params);
    },

    async getActivityDetail(id) {
        return this.get(this.apiUrl('activity', 'detail'), { id });
    },

    async createActivity(data) {
        return this.post(this.apiUrl('activity', 'create'), data);
    },

    async updateActivity(data) {
        return this.post(this.apiUrl('activity', 'update'), data);
    },

    async deleteActivity(id) {
        return this.post(this.apiUrl('activity', 'delete'), { id });
    },

    async registerActivity(activityId, paymentSlip = null) {
        const body = { activity_id: activityId };
        if (paymentSlip) body.payment_proof = paymentSlip;
        return this.post(this.apiUrl('activity', 'register'), body);
    },

    async cancelActivityRegistration(registrationId) {
        return this.post(this.apiUrl('activity', 'cancel-registration'), { registration_id: registrationId });
    },

    async uploadPaymentSlip(registrationId, slipUrl) {
        return this.post(this.apiUrl('activity', 'upload-slip'), { registration_id: registrationId, payment_proof: slipUrl });
    },

    async getPendingPayments(params = {}) {
        return this.get(this.apiUrl('activity', 'pending-payments'), params);
    },

    async verifyPayment(registrationId, action, note = null) {
        const body = { registration_id: registrationId, action: action };
        if (note) body.note = note;
        return this.post(this.apiUrl('activity', 'verify-payment'), body);
    },

    async approveRegistration(registrationId, status, paymentStatus = null) {
        const body = { registration_id: registrationId, status };
        if (paymentStatus) body.payment_status = paymentStatus;
        return this.post(this.apiUrl('activity', 'approve-registration'), body);
    },

    async getActivityRegistrations(activityId, status = null) {
        const params = { id: activityId };
        if (status) params.status = status;
        return this.get(this.apiUrl('activity', 'registrations'), params);
    },
    async getPublicRegistrations(activityId, code) {
        return this.get(this.apiUrl('activity', 'public-registrations'), { id: activityId, code: code });
    },
    async resetAccessCode(activityId) {
        return this.post(this.apiUrl('activity', 'reset-access-code'), { id: activityId });
    },
    async removeAccessCode(activityId) {
        return this.post(this.apiUrl('activity', 'remove-access-code'), { id: activityId });
    },

    // ── DASHBOARD (admin) ────────────────────────────────
    async getDashboard() {
        return this.get(this.apiUrl('dashboard', 'index'));
    },

    async getDashboardStatistics() {
        return this.get(this.apiUrl('dashboard', 'statistics'));
    },

    async getPublicStats() {
        return this.get(this.apiUrl('dashboard', 'public_stats'));
    },

    // ── PAGES ────────────────────────────────────────────
    async getPages(params = {}) {
        return this.get(this.apiUrl('page', 'list'), params);
    },

    async getPageDetail(idOrSlug) {
        const params = typeof idOrSlug === 'number' ? { id: idOrSlug } : { slug: idOrSlug };
        return this.get(this.apiUrl('page', 'detail'), params);
    },

    async createPage(data) {
        return this.post(this.apiUrl('page', 'create'), data);
    },

    async updatePage(data) {
        return this.post(this.apiUrl('page', 'update'), data);
    },

    async deletePage(id) {
        return this.post(this.apiUrl('page', 'delete'), { id });
    },

    // ── NAV ──────────────────────────────────────────────
    async getNavTree() {
        return this.get(this.apiUrl('nav', 'tree'));
    },

    async getNavList() {
        return this.get(this.apiUrl('nav', 'list'));
    },

    async createNav(data) {
        return this.post(this.apiUrl('nav', 'create'), data);
    },

    async updateNav(data) {
        return this.post(this.apiUrl('nav', 'update'), data);
    },

    async deleteNav(id) {
        return this.post(this.apiUrl('nav', 'delete'), { id });
    },

    async reorderNav(items) {
        return this.post(this.apiUrl('nav', 'reorder'), { items });
    },

    // ── LOGS (admin) ─────────────────────────────────────
    async getLogs(params = {}) {
        return this.get(this.apiUrl('log', 'list'), params);
    },

    async getRecentLogs(params = {}) {
        return this.get(this.apiUrl('log', 'recent'), params);
    },

    // ── SETTINGS (admin) ─────────────────────────────────
    async getSettings() {
        return this.get(this.apiUrl('settings', 'list'));
    },

    async updateSettings(data) {
        return this.post(this.apiUrl('settings', 'update'), { settings: data });
    },

    // ── MEMBER TYPES (ประเภทสมาชิก) ──────────────────────
    async getMemberTypes() {
        return this.get(this.apiUrl('settings', 'member-types'));
    },

    async updateMemberType(data) {
        return this.post(this.apiUrl('settings', 'update-member-type'), data);
    },

    async createMemberType(data) {
        return this.post(this.apiUrl('settings', 'create-member-type'), data);
    },

    // ── FEES (ค่าธรรมเนียมสมาชิก) ────────────────────────
    async getFees(params = {}) {
        return this.get(this.apiUrl('fee', 'list'), params);
    },

    async getFeeSummary(year = null) {
        const params = year ? { year } : {};
        return this.get(this.apiUrl('fee', 'summary'), params);
    },

    async generateFees(year) {
        return this.post(this.apiUrl('fee', 'generate'), { year });
    },

    async approveFee(feeId, action, note = '', receivedDate = '') {
        return this.post(this.apiUrl('fee', 'approve'), { fee_id: feeId, action, note, received_date: receivedDate });
    },

    async getMyFees() {
        return this.get(this.apiUrl('fee', 'my-fees'));
    },

    async getMyCurrentFee() {
        return this.get(this.apiUrl('fee', 'my-current'));
    },

    async createMyFee() {
        return this.post(this.apiUrl('fee', 'create-my-fee'));
    },

    async uploadFeeSlip(feeId, paymentSlip) {
        return this.post(this.apiUrl('fee', 'upload-slip'), { fee_id: feeId, payment_slip: paymentSlip });
    },

    // ── RECEIPTS ──
    async getReceipts(params = {}) {
        return this.get(this.apiUrl('receipt', 'list'), params);
    },
    async getReceiptDetail(id) {
        return this.get(this.apiUrl('receipt', 'detail'), { id });
    },
    async findReceiptByRef(referenceNo) {
        return this.get(this.apiUrl('receipt', 'find-by-ref'), { reference_no: referenceNo });
    },
    async createReceipt(data) {
        return this.post(this.apiUrl('receipt', 'create'), data);
    },
    async updateReceipt(data) {
        return this.post(this.apiUrl('receipt', 'update'), data);
    },
    async getNextReceiptNumber(issuedDate = '') {
        return this.get(this.apiUrl('receipt', 'next-number'), { issued_date: issuedDate });
    },
    async checkReceiptDuplicate(receiptNumber, issuedDate, excludeId = 0) {
        return this.get(this.apiUrl('receipt', 'check-duplicate'), { receipt_number: receiptNumber, issued_date: issuedDate, exclude_id: excludeId });
    },
    async searchReceiptMembers(q = '') {
        return this.get(this.apiUrl('receipt', 'search-members'), { q });
    },
    async getReceiptReferenceData(receiptType, referenceId) {
        return this.get(this.apiUrl('receipt', 'reference-data'), { receipt_type: receiptType, reference_id: referenceId });
    },
    async searchReceiptReference(type, q = '') {
        return this.get(this.apiUrl('receipt', 'search-reference'), { type, q });
    },
    async getMyReceipts() {
        return this.get(this.apiUrl('receipt', 'my-receipts'));
    },

    // ── NOTIFICATIONS ──
    async getNotifications() {
        return this.get(this.apiUrl('member', 'notifications'));
    },

    // ── FINANCE ──
    // Categories
    async getFinanceCategories(params = {}) {
        return this.get(this.apiUrl('finance', 'categories'), params);
    },
    async getFinanceActiveCategories(type = null) {
        return this.get(this.apiUrl('finance', 'active-categories'), type ? { type } : {});
    },
    async createFinanceCategory(data) {
        return this.post(this.apiUrl('finance', 'create-category'), data);
    },
    async updateFinanceCategory(data) {
        return this.post(this.apiUrl('finance', 'update-category'), data);
    },
    async deleteFinanceCategory(id) {
        return this.post(this.apiUrl('finance', 'delete-category'), { id });
    },

    // Transactions
    async getFinanceTransactions(params = {}) {
        return this.get(this.apiUrl('finance', 'list'), params);
    },
    async getFinanceDetail(id) {
        return this.get(this.apiUrl('finance', 'detail'), { id });
    },
    async createFinanceTransaction(data) {
        return this.post(this.apiUrl('finance', 'create'), data);
    },
    async updateFinanceTransaction(data) {
        return this.post(this.apiUrl('finance', 'update'), data);
    },
    async deleteFinanceTransaction(id) {
        return this.post(this.apiUrl('finance', 'delete'), { id });
    },

    // Summary & Reports
    async getFinanceSummary(params = {}) {
        return this.get(this.apiUrl('finance', 'summary'), params);
    },
    async getFinanceMonthlySummary(year) {
        return this.get(this.apiUrl('finance', 'monthly-summary'), { year });
    },
    async exportFinance(params = {}) {
        return this.get(this.apiUrl('finance', 'export'), params);
    },

    // Finance Managers
    async getFinanceManagers() {
        return this.get(this.apiUrl('finance', 'managers'));
    },
    async getFinanceAvailableMembers() {
        return this.get(this.apiUrl('finance', 'available-members'));
    },
    async assignFinanceManager(userId, permissions = null) {
        const data = { user_id: userId };
        if (permissions) data.permissions = permissions;
        return this.post(this.apiUrl('finance', 'assign-manager'), data);
    },
    async revokeFinanceManager(userId) {
        return this.post(this.apiUrl('finance', 'revoke-manager'), { user_id: userId });
    },
    async updateFinanceManagerPermissions(userId, permissions) {
        return this.post(this.apiUrl('finance', 'update-manager-permissions'), { user_id: userId, permissions });
    },
    async toggleFinanceManager(userId) {
        return this.post(this.apiUrl('finance', 'toggle-manager'), { user_id: userId });
    },
    async deleteFinanceManager(userId) {
        return this.post(this.apiUrl('finance', 'delete-manager'), { user_id: userId });
    },
    async getMyFinancePermissions() {
        return this.get(this.apiUrl('finance', 'my-permissions'));
    }
};
