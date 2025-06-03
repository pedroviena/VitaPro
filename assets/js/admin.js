/**
 * VitaPro Appointments FSE Admin JavaScript
 * 
 * Handles admin functionality for the plugin.
 */

(function() {
    'use strict';

    // Declare global variables
    const wp = window.wp;
    const jQuery = window.jQuery;
    const ajaxurl = window.ajaxurl;
    const vitaproAdmin = window.vitaproAdmin;

    // Main admin class
    class VitaProAdmin {
        constructor() {
            this.init();
        }

        init() {
            this.initTabs();
            this.initServiceSettings();
            this.initProfessionalSettings();
            this.initAppointmentManagement();
            this.initHolidayManagement();
            this.initCustomFields();
            this.initColorPicker();
            this.initTimePicker();
            this.initDatePicker();
        }

        initTabs() {
            const tabLinks = document.querySelectorAll('.vpa-tab-link');
            const tabContents = document.querySelectorAll('.vpa-tab-content');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs
                    tabLinks.forEach(tab => tab.classList.remove('nav-tab-active'));
                    tabContents.forEach(content => content.classList.remove('vpa-tab-active'));
                    
                    // Add active class to current tab
                    this.classList.add('nav-tab-active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('href').substring(1);
                    document.getElementById(tabId).classList.add('vpa-tab-active');
                    
                    // Store active tab in localStorage
                    localStorage.setItem('vitapro_active_tab', tabId);
                });
            });
            
            // Restore active tab from localStorage
            const activeTab = localStorage.getItem('vitapro_active_tab');
            if (activeTab) {
                const activeTabLink = document.querySelector(`.vpa-tab-link[href="#${activeTab}"]`);
                if (activeTabLink) {
                    activeTabLink.click();
                }
            } else if (tabLinks.length > 0) {
                // Default to first tab
                tabLinks[0].click();
            }
        }

        initServiceSettings() {
            // Duration field handling
            const durationFields = document.querySelectorAll('.vpa-service-duration');
            durationFields.forEach(field => {
                field.addEventListener('change', function() {
                    const minutes = parseInt(this.value);
                    const hoursField = this.closest('.vpa-duration-wrapper').querySelector('.vpa-duration-hours');
                    const minutesField = this.closest('.vpa-duration-wrapper').querySelector('.vpa-duration-minutes');
                    
                    if (hoursField && minutesField) {
                        const hours = Math.floor(minutes / 60);
                        const remainingMinutes = minutes % 60;
                        
                        hoursField.value = hours;
                        minutesField.value = remainingMinutes;
                    }
                });
            });
            
            // Hours and minutes fields handling
            const hoursFields = document.querySelectorAll('.vpa-duration-hours');
            const minutesFields = document.querySelectorAll('.vpa-duration-minutes');
            
            const updateDuration = function(wrapper) {
                const hoursField = wrapper.querySelector('.vpa-duration-hours');
                const minutesField = wrapper.querySelector('.vpa-duration-minutes');
                const durationField = wrapper.querySelector('.vpa-service-duration');
                
                if (hoursField && minutesField && durationField) {
                    const hours = parseInt(hoursField.value) || 0;
                    const minutes = parseInt(minutesField.value) || 0;
                    const totalMinutes = (hours * 60) + minutes;
                    
                    durationField.value = totalMinutes;
                }
            };
            
            hoursFields.forEach(field => {
                field.addEventListener('change', function() {
                    updateDuration(this.closest('.vpa-duration-wrapper'));
                });
            });
            
            minutesFields.forEach(field => {
                field.addEventListener('change', function() {
                    updateDuration(this.closest('.vpa-duration-wrapper'));
                });
            });
            
            // Service image upload
            const imageUploadButtons = document.querySelectorAll('.vpa-upload-image-button');
            imageUploadButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const wrapper = this.closest('.vpa-image-upload-wrapper');
                    const previewElement = wrapper.querySelector('.vpa-image-preview');
                    const idField = wrapper.querySelector('.vpa-image-id');
                    const urlField = wrapper.querySelector('.vpa-image-url');
                    
                    // Create media uploader
                    const mediaUploader = wp.media({
                        title: 'Select Image',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false
                    });
                    
                    // When image is selected
                    mediaUploader.on('select', function() {
                        const attachment = mediaUploader.state().get('selection').first().toJSON();
                        
                        // Update preview
                        previewElement.innerHTML = `<img src="${attachment.url}" alt="Preview">`;
                        previewElement.classList.remove('vpa-hidden');
                        
                        // Update fields
                        idField.value = attachment.id;
                        urlField.value = attachment.url;
                        
                        // Show remove button
                        wrapper.querySelector('.vpa-remove-image-button').classList.remove('vpa-hidden');
                    });
                    
                    // Open media uploader
                    mediaUploader.open();
                });
            });
            
            // Remove image
            const removeImageButtons = document.querySelectorAll('.vpa-remove-image-button');
            removeImageButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const wrapper = this.closest('.vpa-image-upload-wrapper');
                    const previewElement = wrapper.querySelector('.vpa-image-preview');
                    const idField = wrapper.querySelector('.vpa-image-id');
                    const urlField = wrapper.querySelector('.vpa-image-url');
                    
                    // Clear fields
                    previewElement.innerHTML = '';
                    previewElement.classList.add('vpa-hidden');
                    idField.value = '';
                    urlField.value = '';
                    
                    // Hide remove button
                    this.classList.add('vpa-hidden');
                });
            });
        }

        initProfessionalSettings() {
            // Professional image upload (same as service image upload)
            const imageUploadButtons = document.querySelectorAll('.vpa-upload-image-button');
            imageUploadButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const wrapper = this.closest('.vpa-image-upload-wrapper');
                    const previewElement = wrapper.querySelector('.vpa-image-preview');
                    const idField = wrapper.querySelector('.vpa-image-id');
                    const urlField = wrapper.querySelector('.vpa-image-url');
                    
                    // Create media uploader
                    const mediaUploader = wp.media({
                        title: 'Select Image',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false
                    });
                    
                    // When image is selected
                    mediaUploader.on('select', function() {
                        const attachment = mediaUploader.state().get('selection').first().toJSON();
                        
                        // Update preview
                        previewElement.innerHTML = `<img src="${attachment.url}" alt="Preview">`;
                        previewElement.classList.remove('vpa-hidden');
                        
                        // Update fields
                        idField.value = attachment.id;
                        urlField.value = attachment.url;
                        
                        // Show remove button
                        wrapper.querySelector('.vpa-remove-image-button').classList.remove('vpa-hidden');
                    });
                    
                    // Open media uploader
                    mediaUploader.open();
                });
            });
            
            // Service selection in professional edit screen
            const serviceCheckboxes = document.querySelectorAll('.vpa-service-checkbox');
            serviceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const serviceId = this.value;
                    const serviceSettingsRow = document.getElementById(`vpa-service-settings-${serviceId}`);
                    
                    if (serviceSettingsRow) {
                        if (this.checked) {
                            serviceSettingsRow.classList.remove('vpa-hidden');
                        } else {
                            serviceSettingsRow.classList.add('vpa-hidden');
                        }
                    }
                });
            });
            
            // Working hours toggle
            const workingHoursToggle = document.querySelectorAll('.vpa-working-hours-toggle');
            workingHoursToggle.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const day = this.dataset.day;
                    const hoursContainer = document.getElementById(`vpa-working-hours-${day}`);
                    
                    if (hoursContainer) {
                        if (this.checked) {
                            hoursContainer.classList.remove('vpa-hidden');
                        } else {
                            hoursContainer.classList.add('vpa-hidden');
                        }
                    }
                });
            });
            
            // Add time slot button
            const addTimeSlotButtons = document.querySelectorAll('.vpa-add-time-slot');
            addTimeSlotButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const day = this.dataset.day;
                    const container = document.getElementById(`vpa-time-slots-${day}`);
                    const template = document.getElementById('vpa-time-slot-template');
                    
                    if (container && template) {
                        const timeSlotHtml = template.innerHTML;
                        const timeSlotCount = container.querySelectorAll('.vpa-time-slot-row').length;
                        const newTimeSlot = document.createElement('div');
                        
                        newTimeSlot.className = 'vpa-time-slot-row';
                        newTimeSlot.innerHTML = timeSlotHtml.replace(/\{index\}/g, timeSlotCount);
                        
                        container.appendChild(newTimeSlot);
                        
                        // Initialize time pickers for new slot
                        const startTimePicker = newTimeSlot.querySelector('.vpa-time-picker');
                        const endTimePicker = newTimeSlot.querySelector('.vpa-time-picker:last-child');
                        
                        if (startTimePicker && jQuery && jQuery.fn.timepicker) {
                            jQuery(startTimePicker).timepicker({
                                timeFormat: 'HH:mm',
                                step: 15,
                                scrollDefault: '09:00'
                            });
                        }
                        
                        if (endTimePicker && jQuery && jQuery.fn.timepicker) {
                            jQuery(endTimePicker).timepicker({
                                timeFormat: 'HH:mm',
                                step: 15,
                                scrollDefault: '17:00'
                            });
                        }
                        
                        // Add remove button handler
                        const removeButton = newTimeSlot.querySelector('.vpa-remove-time-slot');
                        if (removeButton) {
                            removeButton.addEventListener('click', function(e) {
                                e.preventDefault();
                                newTimeSlot.remove();
                            });
                        }
                    }
                });
            });
            
            // Remove time slot button
            document.querySelectorAll('.vpa-remove-time-slot').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.closest('.vpa-time-slot-row').remove();
                });
            });
        }

        initAppointmentManagement() {
            // Status change handling
            const statusSelect = document.getElementById('vpa-appointment-status');
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    const confirmationField = document.getElementById('vpa-send-confirmation');
                    const cancellationField = document.getElementById('vpa-send-cancellation');
                    
                    if (confirmationField && cancellationField) {
                        if (this.value === 'confirmed') {
                            confirmationField.closest('tr').classList.remove('vpa-hidden');
                            cancellationField.closest('tr').classList.add('vpa-hidden');
                        } else if (this.value === 'cancelled') {
                            confirmationField.closest('tr').classList.add('vpa-hidden');
                            cancellationField.closest('tr').classList.remove('vpa-hidden');
                        } else {
                            confirmationField.closest('tr').classList.add('vpa-hidden');
                            cancellationField.closest('tr').classList.add('vpa-hidden');
                        }
                    }
                });
                
                // Trigger change event to set initial state
                statusSelect.dispatchEvent(new Event('change'));
            }
            
            // Service change handling
            const serviceSelect = document.getElementById('vpa-appointment-service');
            if (serviceSelect) {
                serviceSelect.addEventListener('change', function() {
                    const serviceId = this.value;
                    const professionalSelect = document.getElementById('vpa-appointment-professional');
                    
                    if (professionalSelect) {
                        // Show loading indicator
                        professionalSelect.disabled = true;
                        professionalSelect.innerHTML = '<option value="">Loading...</option>';
                        
                        // Fetch professionals for the selected service
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'vitapro_get_professionals_for_service',
                                service_id: serviceId,
                                nonce: vitaproAdmin.nonce
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            professionalSelect.disabled = false;
                            
                            if (data.success) {
                                professionalSelect.innerHTML = '';
                                
                                // Add empty option
                                const emptyOption = document.createElement('option');
                                emptyOption.value = '';
                                emptyOption.textContent = 'Select a professional';
                                professionalSelect.appendChild(emptyOption);
                                
                                // Add professionals
                                data.data.professionals.forEach(professional => {
                                    const option = document.createElement('option');
                                    option.value = professional.id;
                                    option.textContent = professional.name;
                                    professionalSelect.appendChild(option);
                                });
                                
                                // If there's a selected professional, restore it
                                const selectedProfessional = professionalSelect.dataset.selected;
                                if (selectedProfessional) {
                                    professionalSelect.value = selectedProfessional;
                                }
                            } else {
                                professionalSelect.innerHTML = '<option value="">No professionals available</option>';
                            }
                        })
                        .catch(error => {
                            console.error('Error loading professionals:', error);
                            professionalSelect.disabled = false;
                            professionalSelect.innerHTML = '<option value="">Error loading professionals</option>';
                        });
                    }
                });
                
                // Trigger change event to load professionals on page load
                if (serviceSelect.value) {
                    serviceSelect.dispatchEvent(new Event('change'));
                }
            }
        }

        initHolidayManagement() {
            // Add holiday button
            const addHolidayButton = document.getElementById('vpa-add-holiday');
            if (addHolidayButton) {
                addHolidayButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const container = document.getElementById('vpa-holidays-container');
                    const template = document.getElementById('vpa-holiday-template');
                    
                    if (container && template) {
                        const holidayHtml = template.innerHTML;
                        const holidayCount = container.querySelectorAll('.vpa-holiday-row').length;
                        const newHoliday = document.createElement('div');
                        
                        newHoliday.className = 'vpa-holiday-row';
                        newHoliday.innerHTML = holidayHtml.replace(/\{index\}/g, holidayCount);
                        
                        container.appendChild(newHoliday);
                        
                        // Initialize date pickers for new holiday
                        const startDatePicker = newHoliday.querySelector('.vpa-date-picker');
                        const endDatePicker = newHoliday.querySelector('.vpa-date-picker:last-child');
                        
                        if (startDatePicker && jQuery && jQuery.fn.datepicker) {
                            jQuery(startDatePicker).datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true
                            });
                        }
                        
                        if (endDatePicker && jQuery && jQuery.fn.datepicker) {
                            jQuery(endDatePicker).datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true
                            });
                        }
                        
                        // Add remove button handler
                        const removeButton = newHoliday.querySelector('.vpa-remove-holiday');
                        if (removeButton) {
                            removeButton.addEventListener('click', function(e) {
                                e.preventDefault();
                                newHoliday.remove();
                            });
                        }
                    }
                });
            }
            
            // Remove holiday button
            document.querySelectorAll('.vpa-remove-holiday').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.closest('.vpa-holiday-row').remove();
                });
            });
        }

        initCustomFields() {
            // Add custom field button
            const addCustomFieldButton = document.getElementById('vpa-add-custom-field');
            if (addCustomFieldButton) {
                addCustomFieldButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const container = document.getElementById('vpa-custom-fields-container');
                    const template = document.getElementById('vpa-custom-field-template');
                    
                    if (container && template) {
                        const fieldHtml = template.innerHTML;
                        const fieldCount = container.querySelectorAll('.vpa-custom-field-row').length;
                        const newField = document.createElement('div');
                        
                        newField.className = 'vpa-custom-field-row';
                        newField.innerHTML = fieldHtml.replace(/\{index\}/g, fieldCount);
                        
                        container.appendChild(newField);
                        
                        // Add remove button handler
                        const removeButton = newField.querySelector('.vpa-remove-custom-field');
                        if (removeButton) {
                            removeButton.addEventListener('click', function(e) {
                                e.preventDefault();
                                newField.remove();
                            });
                        }
                        
                        // Add field type change handler
                        const typeSelect = newField.querySelector('.vpa-custom-field-type');
                        if (typeSelect) {
                            typeSelect.addEventListener('change', function() {
                                const optionsRow = this.closest('.vpa-custom-field-row').querySelector('.vpa-field-options-row');
                                if (optionsRow) {
                                    if (this.value === 'select' || this.value === 'radio' || this.value === 'checkbox') {
                                        optionsRow.classList.remove('vpa-hidden');
                                    } else {
                                        optionsRow.classList.add('vpa-hidden');
                                    }
                                }
                            });
                        }
                    }
                });
            }
            
            // Remove custom field button
            document.querySelectorAll('.vpa-remove-custom-field').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.closest('.vpa-custom-field-row').remove();
                });
            });
            
            // Field type change handler
            document.querySelectorAll('.vpa-custom-field-type').forEach(select => {
                select.addEventListener('change', function() {
                    const optionsRow = this.closest('.vpa-custom-field-row').querySelector('.vpa-field-options-row');
                    if (optionsRow) {
                        if (this.value === 'select' || this.value === 'radio' || this.value === 'checkbox') {
                            optionsRow.classList.remove('vpa-hidden');
                        } else {
                            optionsRow.classList.add('vpa-hidden');
                        }
                    }
                });
                
                // Trigger change event to set initial state
                select.dispatchEvent(new Event('change'));
            });
        }

        initColorPicker() {
            // Initialize color pickers if wp-color-picker is available
            if (jQuery && jQuery.fn.wpColorPicker) {
                jQuery('.vpa-color-picker').wpColorPicker();
            }
        }

        initTimePicker() {
            // Initialize time pickers if jQuery UI timepicker is available
            if (jQuery && jQuery.fn.timepicker) {
                jQuery('.vpa-time-picker').timepicker({
                    timeFormat: 'HH:mm',
                    step: 15
                });
            }
        }

        initDatePicker() {
            // Initialize date pickers if jQuery UI datepicker is available
            if (jQuery && jQuery.fn.datepicker) {
                jQuery('.vpa-date-picker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true
                });
            }
        }
    }

    // Initialize admin functionality when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        new VitaProAdmin();
    });
})();