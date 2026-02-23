/**
 * PDPA Consent & Cookie Management
 * จัดการความยินยอม PDPA และคุกกี้
 * 
 * localStorage keys:
 *   - pdpa_privacy_consent : "accepted" | null   → ยอมรับนโยบายความเป็นส่วนตัว
 *   - pdpa_privacy_consent_date : ISO date        → วันที่ยอมรับ
 *   - pdpa_cookie_consent  : JSON object          → การตั้งค่าคุกกี้
 *   - pdpa_cookie_consent_date : ISO date         → วันที่ตั้งค่าคุกกี้
 */
(function() {
    'use strict';

    const CONSENT_KEY         = 'pdpa_privacy_consent';
    const CONSENT_DATE_KEY    = 'pdpa_privacy_consent_date';
    const COOKIE_KEY          = 'pdpa_cookie_consent';
    const COOKIE_DATE_KEY     = 'pdpa_cookie_consent_date';

    // ── Helpers ──
    function getConsent() {
        return localStorage.getItem(CONSENT_KEY);
    }

    function setConsent() {
        localStorage.setItem(CONSENT_KEY, 'accepted');
        localStorage.setItem(CONSENT_DATE_KEY, new Date().toISOString());
    }

    function getCookieConsent() {
        try {
            return JSON.parse(localStorage.getItem(COOKIE_KEY));
        } catch(e) {
            return null;
        }
    }

    function setCookieConsent(settings) {
        localStorage.setItem(COOKIE_KEY, JSON.stringify(settings));
        localStorage.setItem(COOKIE_DATE_KEY, new Date().toISOString());
    }

    // ── Init on DOM Ready ──
    document.addEventListener('DOMContentLoaded', function() {
        var consentOverlay = document.getElementById('pdpa-consent-overlay');
        var cookieBanner   = document.getElementById('pdpa-cookie-banner');

        // Guard: if components aren't in the page, abort
        if (!consentOverlay && !cookieBanner) return;

        // ─── 1. Privacy Consent Modal (First Visit) ───
        if (consentOverlay && !getConsent()) {
            // Show consent modal
            consentOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            var checkbox  = document.getElementById('pdpa-consent-checkbox');
            var acceptBtn = document.getElementById('pdpa-consent-accept-btn');

            if (checkbox && acceptBtn) {
                checkbox.addEventListener('change', function() {
                    acceptBtn.disabled = !this.checked;
                });

                acceptBtn.addEventListener('click', function() {
                    setConsent();
                    consentOverlay.style.display = 'none';
                    document.body.style.overflow = '';
                    // Now show cookie banner if not yet accepted
                    showCookieBannerIfNeeded();
                });
            }
        } else {
            // Consent already given — show cookie banner if needed
            showCookieBannerIfNeeded();
        }

        // ─── 2. Cookie Banner ───
        function showCookieBannerIfNeeded() {
            if (!cookieBanner) return;
            if (getCookieConsent()) return; // already set cookies preference
            cookieBanner.style.display = 'block';
        }

        // Accept All Cookies
        var cookieAcceptBtn = document.getElementById('pdpa-cookie-accept-btn');
        if (cookieAcceptBtn) {
            cookieAcceptBtn.addEventListener('click', function() {
                setCookieConsent({ necessary: true, analytics: true });
                hideCookieBanner();
            });
        }

        // Reject All Cookies (only necessary)
        var cookieRejectBtn = document.getElementById('pdpa-cookie-reject-btn');
        if (cookieRejectBtn) {
            cookieRejectBtn.addEventListener('click', function() {
                setCookieConsent({ necessary: true, analytics: false });
                hideCookieBanner();
            });
        }

        // Settings button
        var cookieSettingsBtn = document.getElementById('pdpa-cookie-settings-btn');
        var settingsPanel     = document.getElementById('pdpa-cookie-settings');
        if (cookieSettingsBtn && settingsPanel) {
            cookieSettingsBtn.addEventListener('click', function() {
                cookieBanner.style.display = 'none';
                settingsPanel.style.display = 'block';
                // Load current settings
                var current = getCookieConsent();
                var analyticsCheck = document.getElementById('cookie-analytics');
                if (analyticsCheck && current) {
                    analyticsCheck.checked = current.analytics || false;
                }
            });
        }

        // Close settings
        var settingsCloseBtn = document.getElementById('pdpa-settings-close-btn');
        if (settingsCloseBtn && settingsPanel) {
            settingsCloseBtn.addEventListener('click', function() {
                settingsPanel.style.display = 'none';
                // Show banner again if not yet saved
                if (!getCookieConsent()) {
                    cookieBanner.style.display = 'block';
                }
            });
        }

        // Save cookie settings
        var saveSettingsBtn = document.getElementById('pdpa-save-settings-btn');
        if (saveSettingsBtn) {
            saveSettingsBtn.addEventListener('click', function() {
                var analyticsCheck = document.getElementById('cookie-analytics');
                setCookieConsent({
                    necessary: true,
                    analytics: analyticsCheck ? analyticsCheck.checked : false
                });
                if (settingsPanel) settingsPanel.style.display = 'none';
                hideCookieBanner();
            });
        }

        function hideCookieBanner() {
            if (cookieBanner) {
                cookieBanner.style.animation = 'pdpa-slide-down 0.3s ease-in forwards';
                setTimeout(function() { cookieBanner.style.display = 'none'; }, 300);
            }
        }
    });

    // ── Add slide-down animation dynamically ──
    var style = document.createElement('style');
    style.textContent = '@keyframes pdpa-slide-down { from { transform: translateY(0); opacity: 1; } to { transform: translateY(100%); opacity: 0; } }';
    document.head.appendChild(style);

})();
