/**
 * VitaPro Appointments FSE Dashboard JavaScript
 * 
 * Handles all dashboard functionality including charts, real-time updates, and interactions.
 */

(function($) {
    'use strict';
    
    // Global variables
    let charts = {};
    let refreshInterval;
    let notificationInterval;
    let currentDateRange = 30;
    
    // Initialize dashboard
    $(document).ready(function() {
        initializeDashboard();
        setupEventListeners();
        startRealTimeUpdates();
        initializeNotifications();
    });
    
    /**
     * Initialize dashboard components
     */
    function initializeDashboard() {
        loadDashboardStats();
        initializeCharts();
        setupDateRangePicker();
        initializeCalendar();
        setupFilters();
    }
    
    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Date range selector
        $('#vpa-date-range').on('change', function() {
            const range = $(this).val();
            if (range === 'custom') {
                $('#vpa-custom-date-range').show();
            } else {
                $('#vpa-custom-date-range').hide();
                currentDateRange = parseInt(range);
                refreshDashboard();
            }
        });
        
        // Apply custom date range
        $('#vpa-apply-date-range').on('click', function() {
            const startDate = $('#vpa-start-date').val();
            const endDate = $('#vpa-end-date').val();
            
            if (startDate && endDate) {
                refreshDashboard(startDate, endDate);
            }
        });
        
        // Chart type toggles
        $('.vpa-chart-controls button').on('click', function() {
            const chartType = $(this).data('chart-type');
            const chartContainer = $(this).closest('.vpa-chart-container');
            const chartId = chartContainer.find('canvas').attr('id');
            
            if (charts[chartId]) {
                updateChartType(chartId, chartType);
            }
        });
        
        // Export buttons
        $('#vpa-export-calendar').on('click', exportCalendar);
        $('#vpa-export-report').on('click', exportReport);
        
        // Modal controls
        $('.vpa-modal-close, #vpa-modal-close-btn').on('click', closeModal);
        
        // Filter changes
        $('.vpa-calendar-filters select').on('change', filterCalendar);
        
        // Notification actions
        $(document).on('click', '.vpa-notification-item', markNotificationRead);
        $(document).on('click', '.vpa-notification-dismiss', dismissNotification);
        
        // Real-time toggle
        $('#vpa-realtime-toggle').on('change', toggleRealTimeUpdates);
    }
    
    /**
     * Load dashboard statistics
     */
    function loadDashboardStats(startDate = null, endDate = null) {
        showLoading('.vpa-kpi-grid');
        
        const data = {
            action: 'vpa_dashboard_stats',
            nonce: vpaAdmin.nonce,
            days: currentDateRange
        };
        
        if (startDate && endDate) {
            data.start_date = startDate;
            data.end_date = endDate;
        }
        
        $.post(vpaAdmin.ajaxurl, data)
            .done(function(response) {
                if (response.success) {
                    updateKPICards(response.data);
                } else {
                    showError('.vpa-kpi-grid', response.data || vpaAdmin.strings.error);
                }
            })
            .fail(function() {
                showError('.vpa-kpi-grid', vpaAdmin.strings.error);
            })
            .always(function() {
                hideLoading('.vpa-kpi-grid');
            });
    }
    
    /**
     * Update KPI cards
     */
    function updateKPICards(data) {
        // Total appointments
        $('#total-appointments').text(formatNumber(data.total_appointments));
        $('#appointments-change').html(formatChange(data.appointments_change));
        
        // Total revenue
        $('#total-revenue').text(formatCurrency(data.total_revenue));
        $('#revenue-change').html(formatChange(data.revenue_change));
        
        // Conversion rate
        $('#conversion-rate').text(data.conversion_rate + '%');
        $('#conversion-change').html(formatChange(data.conversion_change));
        
        // Satisfaction score
        $('#satisfaction-score').text(data.satisfaction_score);
        $('#satisfaction-change').html(formatChange(data.satisfaction_change));
        
        // Animate counters
        animateCounters();
    }
    
    /**
     * Initialize charts
     */
    function initializeCharts() {
        initializeAppointmentTrendsChart();
        initializeServiceDistributionChart();
        initializeRevenueChart();
        initializeProfessionalPerformanceChart();
    }
    
    /**
     * Initialize appointment trends chart
     */
    function initializeAppointmentTrendsChart() {
        const ctx = document.getElementById('appointments-trend-chart');
        if (!ctx) return;
        
        $.post(vpaAdmin.ajaxurl, {
            action: 'vpa_appointment_trends',
            nonce: vpaAdmin.nonce,
            days: currentDateRange
        })
        .done(function(response) {
            if (response.success) {
                charts.appointmentsTrend = new Chart(ctx, {
                    type: 'line',
                    data: response.data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            x: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Appointments'
                                },
                                beginAtZero: true
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
                });
            }
        });
    }
    
    /**
     * Initialize service distribution chart
     */
    function initializeServiceDistributionChart() {
        const ctx = document.getElementById('services-distribution-chart');
        if (!ctx) return;
        
        $.post(vpaAdmin.ajaxurl, {
            action: 'vpa_service_distribution',
            nonce: vpaAdmin.nonce,
            days: currentDateRange
        })
        .done(function(response) {
            if (response.success) {
                charts.serviceDistribution = new Chart(ctx, {
                    type: 'doughnut',
                    data: response.data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    }
    
    /**
     * Initialize revenue chart
     */
    function initializeRevenueChart() {
        const ctx = document.getElementById('revenue-chart');
        if (!ctx) return;
        
        $.post(vpaAdmin.ajaxurl, {
            action: 'vpa_revenue_chart',
            nonce: vpaAdmin.nonce
        })
        .done(function(response) {
            if (response.success) {
                charts.revenue = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: response.data.labels,
                        datasets: [{
                            label: 'Revenue',
                            data: response.data.data,
                            backgroundColor: 'rgba(0, 163, 42, 0.8)',
                            borderColor: 'rgba(0, 163, 42, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: ' + formatCurrency(context.parsed.y);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return formatCurrency(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    }
    
    /**
     * Initialize professional performance chart
     */
    function initializeProfessionalPerformanceChart() {
        const ctx = document.getElementById('professional-performance-chart');
        if (!ctx) return;
        
        $.post(vpaAdmin.ajaxurl, {
            action: 'vpa_professional_performance',
            nonce: vpaAdmin.nonce
        })
        .done(function(response) {
            if (response.success) {
                charts.professionalPerformance = new Chart(ctx, {
                    type: 'horizontalBar',
                    data: {
                        labels: response.data.labels,
                        datasets: [{
                            label: 'Appointments',
                            data: response.data.appointments,
                            backgroundColor: 'rgba(0, 115, 170, 0.8)',
                            borderColor: 'rgba(0, 115, 170, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    }
    
    /**
     * Initialize calendar
     */
    function initializeCalendar() {
        const calendarEl = document.getElementById('vpa-fullcalendar');
        if (!calendarEl) return;
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: function(info, successCallback, failureCallback) {
                $.post(vpaAdmin.ajaxurl, {
                    action: 'vpa_get_calendar_events',
                    nonce: vpaAdmin.nonce,
                    start: info.startStr,
                    end: info.endStr,
                    filters: getCalendarFilters()
                })
                .done(function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        failureCallback(response.data);
                    }
                })
                .fail(function() {
                    failureCallback('Failed to load events');
                });
            },
            eventClick: function(info) {
                showAppointmentModal(info.event.id);
            },
            eventDidMount: function(info) {
                // Add status class
                info.el.classList.add('vpa-status-' + info.event.extendedProps.status);
                
                // Add tooltip
                $(info.el).tooltip({
                    title: info.event.extendedProps.tooltip,
                    placement: 'top',
                    container: 'body'
                });
            },
            height: 'auto',
            eventDisplay: 'block',
            dayMaxEvents: 3,
            moreLinkClick: 'popover'
        });
        
        calendar.render();
        window.vpaCalendar = calendar;
    }
    
    /**
     * Initialize notifications
     */
    function initializeNotifications() {
        loadNotifications();
        setupNotificationPolling();
        requestNotificationPermission();
    }
    
    /**
     * Load notifications
     */
    function loadNotifications() {
        $.post(vpaAdmin.ajaxurl, {
            action: 'vpa_get_notifications',
            nonce: vpaNotifications.nonce,
            limit: 10
        })
        .done(function(response) {
            if (response.success) {
                updateNotificationBadge(response.data.unread_count);
                renderNotifications(response.data.notifications);
            }
        });
    }
    
    /**
     * Setup notification polling
     */
    function setupNotificationPolling() {
        notificationInterval = setInterval(function() {
            $.post(vpaAdmin.ajaxurl, {
                action: 'vpa_get_live_activity',
                nonce: vpaNotifications.nonce,
                last_check: localStorage.getItem('vpa_last_notification_check') || ''
            })
            .done(function(response) {
                if (response.success && response.data.activity.length > 0) {
                    showNewNotifications(response.data.activity);
                    localStorage.setItem('vpa_last_notification_check', response.data.last_check);
                }
            });
        }, 30000); // Check every 30 seconds
    }
    
    /**
     * Show new notifications
     */
    function showNewNotifications(notifications) {
        notifications.forEach(function(notification) {
            showDesktopNotification(notification);
            showInAppNotification(notification);
        });
        
        // Update badge
        const currentCount = parseInt($('.vpa-notification-badge').text()) || 0;
        updateNotificationBadge(currentCount + notifications.length);
    }
    
    /**
     * Show desktop notification
     */
    function showDesktopNotification(notification) {
        if (Notification.permission === 'granted') {
            const desktopNotification = new Notification(notification.title, {
                body: notification.description,
                icon: vpaNotifications.icon_url,
                badge: vpaNotifications.badge_url,
                tag: 'vpa-notification-' + notification.id
            });
            
            desktopNotification.onclick = function() {
                window.focus();
                markNotificationRead(notification.id);
                desktopNotification.close();
            };
            
            setTimeout(function() {
                desktopNotification.close();
            }, 5000);
        }
    }
    
    /**
     * Show in-app notification
     */
    function showInAppNotification(notification) {
        const notificationHtml = `
            <div class="vpa-toast-notification vpa-priority-${notification.priority}" data-id="${notification.id}">
                <div class="vpa-toast-icon">
                    <span class="dashicons dashicons-${notification.icon}"></span>
                </div>
                <div class="vpa-toast-content">
                    <div class="vpa-toast-title">${notification.title}</div>
                    <div class="vpa-toast-message">${notification.description}</div>
                </div>
                <div class="vpa-toast-actions">
                    <button class="vpa-toast-close" data-id="${notification.id}">&times;</button>
                </div>
            </div>
        `;
        
        const $toast = $(notificationHtml);
        $('body').append($toast);
        
        // Animate in
        setTimeout(function() {
            $toast.addClass('vpa-toast-show');
        }, 100);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            hideToastNotification($toast);
        }, 5000);
        
        // Close button
        $toast.find('.vpa-toast-close').on('click', function() {
            hideToastNotification($toast);
        });
    }
    
    /**
     * Hide toast notification
     */
    function hideToastNotification($toast) {
        $toast.removeClass('vpa-toast-show');
        setTimeout(function() {
            $toast.remove();
        }, 300);
    }
    
    /**
     * Request notification permission
     */
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
    
    /**
     * Utility functions
     */
    function formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }
    
    function formatChange(change) {
        const icon = change >= 0 ? 'trending_up' : 'trending_down';
        const color = change >= 0 ? 'green' : 'red';
        const sign = change >= 0 ? '+' : '';
        
        return `<span class="vpa-change vpa-change-${color}">
            <span class="dashicons dashicons-${icon}"></span>
            ${sign}${change.toFixed(1)}%
        </span>`;
    }
    
    function showLoading(selector) {
        $(selector).addClass('vpa-loading');
    }
    
    function hideLoading(selector) {
        $(selector).removeClass('vpa-loading');
    }
    
    function showError(selector, message) {
        $(selector).html(`<div class="vpa-error">${message}</div>`);
    }
    
    function animateCounters() {
        $('.vpa-kpi-value').each(function() {
            const $this = $(this);
            const target = parseFloat($this.text().replace(/[^0-9.-]/g, ''));
            
            if (!isNaN(target)) {
                $({ counter: 0 }).animate({ counter: target }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.ceil(this.counter));
                    },
                    complete: function() {
                        $this.text(target);
                    }
                });
            }
        });
    }
    
    function refreshDashboard(startDate = null, endDate = null) {
        loadDashboardStats(startDate, endDate);
        
        // Refresh charts
        Object.keys(charts).forEach(function(chartKey) {
            if (charts[chartKey]) {
                charts[chartKey].destroy();
            }
        });
        
        setTimeout(function() {
            initializeCharts();
        }, 500);
    }
    
    function startRealTimeUpdates() {
        refreshInterval = setInterval(function() {
            if ($('#vpa-realtime-toggle').is(':checked')) {
                refreshDashboard();
            }
        }, 60000); // Refresh every minute
    }
    
    function toggleRealTimeUpdates() {
        const enabled = $('#vpa-realtime-toggle').is(':checked');
        
        if (enabled) {
            startRealTimeUpdates();
            showToast('Real-time updates enabled', 'success');
        } else {
            clearInterval(refreshInterval);
            showToast('Real-time updates disabled', 'info');
        }
    }
    
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="vpa-toast vpa-toast-${type}">
                <span class="dashicons dashicons-${type === 'success' ? 'yes' : 'info'}"></span>
                ${message}
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(function() {
            toast.addClass('vpa-toast-show');
        }, 100);
        
        setTimeout(function() {
            toast.removeClass('vpa-toast-show');
            setTimeout(function() {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        if (notificationInterval) {
            clearInterval(notificationInterval);
        }
    });
    
})(jQuery);