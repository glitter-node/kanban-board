import './bootstrap';
import './kanban-board';

import Alpine from 'alpinejs';

const analyticsEndpoint = '/analytics/events';
const analyticsQueue = [];
const analyticsMaxBatchSize = 25;
const analyticsFlushDelay = 1200;
let analyticsFlushTimer = null;
let sessionStartAt = Date.now();
let firstTrackedAction = false;
let recentClicks = [];
let sessionEndedTracked = false;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
const experimentAssignments = (() => {
    const content = document.querySelector('meta[name="app-experiments"]')?.getAttribute('content') ?? '{}';

    try {
        return JSON.parse(content);
    } catch (error) {
        console.error(error);
        return {};
    }
})();

const ensureSessionId = () => {
    const key = 'kanban.analytics.session_id';
    const existing = window.sessionStorage.getItem(key);

    if (existing) {
        return existing;
    }

    const sessionId = `${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
    window.sessionStorage.setItem(key, sessionId);

    return sessionId;
};

const analyticsContext = () => {
    const path = window.location.pathname;
    const boardMatch = path.match(/\/boards\/(\d+)/);

    return {
        session_id: ensureSessionId(),
        path,
        board_id: boardMatch ? Number(boardMatch[1]) : undefined,
        viewport: window.innerWidth < 768 ? 'mobile' : 'desktop',
        experiments: experimentAssignments,
    };
};

const scheduleAnalyticsFlush = (delay = analyticsFlushDelay) => {
    if (analyticsFlushTimer) {
        return;
    }

    analyticsFlushTimer = window.setTimeout(() => {
        analyticsFlushTimer = null;
        void flushAnalytics();
    }, delay);
};

const flushAnalytics = async ({ immediate = false } = {}) => {
    if (analyticsFlushTimer) {
        window.clearTimeout(analyticsFlushTimer);
        analyticsFlushTimer = null;
    }

    if (!analyticsQueue.length) {
        return;
    }

    const events = analyticsQueue.splice(0, immediate ? analyticsQueue.length : analyticsMaxBatchSize);

    try {
        await fetch(analyticsEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            keepalive: true,
            body: JSON.stringify({ events }),
        });
    } catch (error) {
        console.error(error);
        analyticsQueue.unshift(...events);
    }

    if (analyticsQueue.length) {
        scheduleAnalyticsFlush(200);
    }
};

window.trackEvent = function trackEvent(name, payload = {}) {
    if (!name) {
        return;
    }

    const eventPayload = {
        ...analyticsContext(),
        ...payload,
    };

    if (!firstTrackedAction && !['session_started', 'session_ended', 'feedback_opened'].includes(name)) {
        firstTrackedAction = true;
        eventPayload.time_to_first_action_ms = Date.now() - sessionStartAt;
    }

    analyticsQueue.push({
        event_name: name,
        payload: eventPayload,
        created_at: new Date().toISOString(),
    });

    if (analyticsQueue.length >= analyticsMaxBatchSize) {
        void flushAnalytics({ immediate: true });
        return;
    }

    scheduleAnalyticsFlush();
};

window.submitUxFeedback = function submitUxFeedback(payload = {}) {
    window.trackEvent('feedback_submitted', {
        sentiment: payload.sentiment ?? 'positive',
        comment: payload.comment ?? '',
        context: payload.context ?? window.location.pathname,
    });

    void flushAnalytics({ immediate: true });
};

window.trackEvent('session_started', {
    started_at: new Date().toISOString(),
});

window.addEventListener('click', (event) => {
    const now = Date.now();
    recentClicks = recentClicks.filter((timestamp) => now - timestamp < 1500);
    recentClicks.push(now);

    if (recentClicks.length >= 5) {
        window.trackEvent('ux_issue_detected', {
            issue: 'rage_click',
            target: event.target instanceof Element ? event.target.tagName.toLowerCase() : 'unknown',
        });
        recentClicks = [];
    }
}, { passive: true });

document.addEventListener('click', (event) => {
    const backTrigger = event.target instanceof Element
        ? event.target.closest('[data-action="back"]')
        : null;

    if (!backTrigger) {
        return;
    }

    window.history.back();
});

const trackSessionEnd = () => {
    if (sessionEndedTracked) {
        return;
    }

    sessionEndedTracked = true;
    window.trackEvent('session_ended', {
        duration_ms: Date.now() - sessionStartAt,
        ended_at: new Date().toISOString(),
    });

    void flushAnalytics({ immediate: true });
};

window.addEventListener('pagehide', trackSessionEnd);

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
        trackSessionEnd();
    }
});

window.Alpine = Alpine;

Alpine.start();
