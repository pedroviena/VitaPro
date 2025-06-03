/**
 * VitaPro Appointments FSE Frontend JavaScript
 * 
 * Handles the booking form functionality, calendar interactions, and other client-side features.
 */

(function() {
    'use strict';

    // Main booking form class
    class VitaProBookingForm {
        constructor(formElement) {
            this.form = formElement;
            this.formId = this.form.dataset.formId || 'default';
            this.currentStep = 1;
            this.maxSteps = parseInt(this.form.dataset.maxSteps || 4);
            this.selectedService = null;
            this.selectedProfessional = null;
            this.selectedDate = null;
            this.selectedTime = null;
            this.formData = {};
            this.calendar = null;
            this.nonce = this.form.querySelector('input[name="vitapro_nonce"]').value;
            
            this.init();
        }

        init() {
            // Initialize steps
            this.showStep(this.currentStep);
            
            // Event listeners for step navigation
            this.form.querySelectorAll('.vpa-next-step').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.nextStep();
                });
            });

            this.form.querySelectorAll('.vpa-prev-step').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.prevStep();
                });
            });

            // Service selection
            this.form.querySelectorAll('.vpa-service-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    this.selectService(option.value);
                });
            });

            // Professional selection
            this.form.querySelectorAll('.vpa-professional-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    this.selectProfessional(option.value);
                });
            });

            // Form submission
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            // Initialize calendar if we have a calendar container
            const calendarContainer = this.form.querySelector('.vpa-calendar-container');
            if (calendarContainer) {
                this.calendar = new VitaProCalendar(calendarContainer, {
                    onDateSelect: (date) => this.onDateSelected(date)
                });
            }
        }

        showStep(stepNumber) {
            // Hide all steps
            this.form.querySelectorAll('.vpa-step').forEach(step => {
                step.classList.add('vpa-hidden');
            });
            
            // Show the current step
            const currentStepElement = this.form.querySelector(`.vpa-step[data-step="${stepNumber}"]`);
            if (currentStepElement) {
                currentStepElement.classList.remove('vpa-hidden');
                
                // Update progress indicator if exists
                const progressIndicator = this.form.querySelector('.vpa-progress-indicator');
                if (progressIndicator) {
                    progressIndicator.setAttribute('data-current-step', stepNumber);
                    progressIndicator.setAttribute('data-max-steps', this.maxSteps);
                }
                
                // Special handling for different steps
                if (stepNumber === 2 && this.selectedService) {
                    this.loadProfessionals(this.selectedService);
                } else if (stepNumber === 3 && this.selectedProfessional) {
                    this.initializeCalendar();
                }
            }
            
            this.currentStep = stepNumber;
        }

        nextStep() {
            // Validate current step
            if (!this.validateCurrentStep()) {
                return;
            }
            
            if (this.currentStep < this.maxSteps) {
                this.showStep(this.currentStep + 1);
            }
        }

        prevStep() {
            if (this.currentStep > 1) {
                this.showStep(this.currentStep - 1);
            }
        }

        validateCurrentStep() {
            let isValid = true;
            const errorArea = this.form.querySelector('.vpa-error-message-area');
            
            // Clear previous errors
            if (errorArea) {
                errorArea.innerHTML = '';
                errorArea.classList.add('vpa-hidden');
            }
            
            // Validate based on current step
            switch (this.currentStep) {
                case 1:
                    if (!this.selectedService) {
                        this.showError('Please select a service to continue.');
                        isValid = false;
                    }
                    break;
                case 2:
                    if (!this.selectedProfessional) {
                        this.showError('Please select a professional to continue.');
                        isValid = false;
                    }
                    break;
                case 3:
                    if (!this.selectedDate || !this.selectedTime) {
                        this.showError('Please select both a date and time slot to continue.');
                        isValid = false;
                    }
                    break;
                case 4:
                    // Validate form fields
                    const requiredFields = this.form.querySelectorAll('.vpa-step[data-step="4"] [required]');
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('vpa-input-error');
                            isValid = false;
                        } else {
                            field.classList.remove('vpa-input-error');
                        }
                    });
                    
                    if (!isValid) {
                        this.showError('Please fill in all required fields.');
                    }
                    
                    // Email validation
                    const emailField = this.form.querySelector('input[type="email"]');
                    if (emailField && emailField.value && !this.isValidEmail(emailField.value)) {
                        emailField.classList.add('vpa-input-error');
                        this.showError('Please enter a valid email address.');
                        isValid = false;
                    }
                    break;
            }
            
            return isValid;
        }

        showError(message) {
            const errorArea = this.form.querySelector('.vpa-error-message-area');
            if (errorArea) {
                errorArea.innerHTML = message;
                errorArea.classList.remove('vpa-hidden');
            }
        }

        selectService(serviceId) {
            this.selectedService = serviceId;
            
            // Update UI
            this.form.querySelectorAll('.vpa-service-option').forEach(option => {
                if (option.value === serviceId) {
                    option.classList.add('vpa-selected');
                } else {
                    option.classList.remove('vpa-selected');
                }
            });
            
            // Enable next button
            const nextButton = this.form.querySelector('.vpa-step[data-step="1"] .vpa-next-step');
            if (nextButton) {
                nextButton.disabled = false;
            }
        }

        selectProfessional(professionalId) {
            this.selectedProfessional = professionalId;
            
            // Update UI
            this.form.querySelectorAll('.vpa-professional-option').forEach(option => {
                if (option.value === professionalId) {
                    option.classList.add('vpa-selected');
                } else {
                    option.classList.remove('vpa-selected');
                }
            });
            
            // Enable next button
            const nextButton = this.form.querySelector('.vpa-step[data-step="2"] .vpa-next-step');
            if (nextButton) {
                nextButton.disabled = false;
            }
        }

        loadProfessionals(serviceId) {
            const professionalsContainer = this.form.querySelector('.vpa-professionals-container');
            if (!professionalsContainer) return;
            
            // Show loading indicator
            professionalsContainer.innerHTML = '<div class="vpa-loading-indicator"><span class="vpa-loading-indicator-text">Loading professionals</span></div>';
            
            // Fetch professionals for the selected service
            const vitaproAppointments = window.vitaproAppointments; // Declare the variable here
            fetch(vitaproAppointments.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'vitapro_get_professionals',
                    service_id: serviceId,
                    nonce: this.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    professionalsContainer.innerHTML = data.data.html;
                    
                    // Re-attach event listeners
                    this.form.querySelectorAll('.vpa-professional-option').forEach(option => {
                        option.addEventListener('click', () => {
                            this.selectProfessional(option.value);
                        });
                    });
                } else {
                    professionalsContainer.innerHTML = '<div class="vpa-error-message-area">' + data.data.message + '</div>';
                }
            })
            .catch(error => {
                console.error('Error loading professionals:', error);
                professionalsContainer.innerHTML = '<div class="vpa-error-message-area">Error loading professionals. Please try again.</div>';
            });
        }

        initializeCalendar() {
            if (!this.calendar) return;
            
            // Reset calendar selections
            this.calendar.reset();
            
            // Clear time slots
            const timeSlotsContainer = this.form.querySelector('.vpa-time-slots-container');
            if (timeSlotsContainer) {
                timeSlotsContainer.querySelector('.vpa-time-slots-list').innerHTML = '';
                timeSlotsContainer.querySelector('.vpa-time-slots-placeholder').classList.remove('vpa-hidden');
            }
            
            // Load available dates
            const vitaproAppointments = window.vitaproAppointments; // Declare the variable here
            this.calendar.loadAvailableDates(this.selectedService, this.selectedProfessional);
        }

        onDateSelected(date) {
            this.selectedDate = date;
            this.selectedTime = null;
            
            // Load time slots for the selected date
            this.loadTimeSlots(date);
        }

        loadTimeSlots(date) {
            const timeSlotsContainer = this.form.querySelector('.vpa-time-slots-container');
            if (!timeSlotsContainer) return;
            
            const timeSlotsListElement = timeSlotsContainer.querySelector('.vpa-time-slots-list');
            const placeholderElement = timeSlotsContainer.querySelector('.vpa-time-slots-placeholder');
            
            // Show loading
            timeSlotsListElement.innerHTML = '';
            placeholderElement.textContent = 'Loading available time slots...';
            placeholderElement.classList.remove('vpa-hidden');
            
            // Format date for the API
            const formattedDate = date.toISOString().split('T')[0];
            
            // Fetch time slots
            const vitaproAppointments = window.vitaproAppointments; // Declare the variable here
            fetch(vitaproAppointments.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'vitapro_get_time_slots',
                    service_id: this.selectedService,
                    professional_id: this.selectedProfessional,
                    date: formattedDate,
                    nonce: this.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.slots && data.data.slots.length > 0) {
                    placeholderElement.classList.add('vpa-hidden');
                    
                    // Render time slots
                    data.data.slots.forEach(slot => {
                        const timeSlotElement = document.createElement('div');
                        timeSlotElement.className = 'vpa-time-slot';
                        timeSlotElement.dataset.time = slot.value;
                        timeSlotElement.textContent = slot.label;
                        
                        timeSlotElement.addEventListener('click', () => {
                            this.selectTimeSlot(slot.value);
                        });
                        
                        timeSlotsListElement.appendChild(timeSlotElement);
                    });
                } else {
                    placeholderElement.textContent = 'No available time slots for this date. Please select another date.';
                }
            })
            .catch(error => {
                console.error('Error loading time slots:', error);
                placeholderElement.textContent = 'Error loading time slots. Please try again.';
            });
        }

        selectTimeSlot(time) {
            this.selectedTime = time;
            
            // Update UI
            this.form.querySelectorAll('.vpa-time-slot').forEach(slot => {
                if (slot.dataset.time === time) {
                    slot.classList.add('vpa-selected');
                } else {
                    slot.classList.remove('vpa-selected');
                }
            });
            
            // Enable next button
            const nextButton = this.form.querySelector('.vpa-step[data-step="3"] .vpa-next-step');
            if (nextButton) {
                nextButton.disabled = false;
            }
        }

        submitForm() {
            // Validate form
            if (!this.validateCurrentStep()) {
                return;
            }
            
            // Collect form data
            const formData = new FormData(this.form);
            formData.append('action', 'vitapro_submit_booking');
            formData.append('service_id', this.selectedService);
            formData.append('professional_id', this.selectedProfessional);
            formData.append('appointment_date', this.selectedDate.toISOString().split('T')[0]);
            formData.append('appointment_time', this.selectedTime);
            
            // Disable submit button and show loading
            const submitButton = this.form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="vpa-loading-indicator-text">Submitting</span>';
            
            // Submit form
            const vitaproAppointments = window.vitaproAppointments; // Declare the variable here
            fetch(vitaproAppointments.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    this.showSuccessMessage(data.data);
                } else {
                    // Show error
                    this.showError(data.data.message || 'An error occurred while submitting your booking. Please try again.');
                    
                    // Re-enable submit button
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error submitting booking:', error);
                this.showError('An error occurred while submitting your booking. Please try again.');
                
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            });
        }

        showSuccessMessage(data) {
            // Hide form steps
            this.form.querySelectorAll('.vpa-step').forEach(step => {
                step.classList.add('vpa-hidden');
            });
            
            // Create and show success message
            const successStep = document.createElement('div');
            successStep.className = 'vpa-step vpa-step-success';
            
            let successContent = `
                <h2>${window.vitaproAppointments.i18n.bookingSuccess}</h2>
                <div class="vpa-success-message">
                    <p>${window.vitaproAppointments.i18n.thankYouMessage}</p>
                </div>
                <div class="vpa-appointment-details">
                    <h3>${window.vitaproAppointments.i18n.appointmentDetails}</h3>
                    <p><strong>${window.vitaproAppointments.i18n.service}:</strong> ${data.service_name}</p>
                    <p><strong>${window.vitaproAppointments.i18n.professional}:</strong> ${data.professional_name}</p>
                    <p><strong>${window.vitaproAppointments.i18n.date}:</strong> ${data.formatted_date}</p>
                    <p><strong>${window.vitaproAppointments.i18n.time}:</strong> ${data.formatted_time}</p>
                    <p><strong>${window.vitaproAppointments.i18n.status}:</strong> ${data.status_label}</p>
                </div>
            `;
            
            if (data.confirmation_message) {
                successContent += `<div class="vpa-mt-20">${data.confirmation_message}</div>`;
            }
            
            successStep.innerHTML = successContent;
            this.form.appendChild(successStep);
            
            // Scroll to top of form
            this.form.scrollIntoView({ behavior: 'smooth' });
        }

        isValidEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }
    }

    // Calendar class
    class VitaProCalendar {
        constructor(container, options = {}) {
            this.container = container;
            this.options = options;
            this.currentDate = new Date();
            this.selectedDate = null;
            this.availableDates = [];
            this.nonce = document.querySelector('input[name="vitapro_nonce"]').value;
            
            this.init();
        }

        init() {
            this.render();
            this.attachEventListeners();
        }

        render() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth();
            
            // Create calendar header
            const header = document.createElement('div');
            header.className = 'vpa-calendar-header';
            
            const prevButton = document.createElement('button');
            prevButton.className = 'vpa-calendar-prev';
            prevButton.innerHTML = '&laquo; ' + window.vitaproAppointments.i18n.prev;
            prevButton.type = 'button';
            
            const nextButton = document.createElement('button');
            nextButton.className = 'vpa-calendar-next';
            nextButton.innerHTML = window.vitaproAppointments.i18n.next + ' &raquo;';
            nextButton.type = 'button';
            
            const monthYearLabel = document.createElement('div');
            monthYearLabel.className = 'vpa-calendar-month-year';
            monthYearLabel.textContent = new Date(year, month, 1).toLocaleDateString(window.vitaproAppointments.locale, { month: 'long', year: 'numeric' });
            
            header.appendChild(prevButton);
            header.appendChild(monthYearLabel);
            header.appendChild(nextButton);
            
            // Create calendar grid
            const calendarGrid = document.createElement('div');
            calendarGrid.className = 'vpa-calendar-grid';
            
            // Create weekday headers
            const weekdaysRow = document.createElement('div');
            weekdaysRow.className = 'vpa-calendar-weekdays';
            
            const weekdays = window.vitaproAppointments.weekdays || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            weekdays.forEach(day => {
                const dayElement = document.createElement('div');
                dayElement.className = 'vpa-calendar-weekday';
                dayElement.textContent = day;
                weekdaysRow.appendChild(dayElement);
            });
            
            calendarGrid.appendChild(weekdaysRow);
            
            // Create days grid
            const daysGrid = document.createElement('div');
            daysGrid.className = 'vpa-calendar-days';
            
            // Get first day of month
            const firstDay = new Date(year, month, 1).getDay();
            
            // Get number of days in month
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            // Get number of days in previous month
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            
            // Add days from previous month
            for (let i = firstDay - 1; i >= 0; i--) {
                const dayElement = document.createElement('div');
                dayElement.className = 'vpa-calendar-day vpa-other-month vpa-disabled';
                dayElement.textContent = daysInPrevMonth - i;
                daysGrid.appendChild(dayElement);
            }
            
            // Add days of current month
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'vpa-calendar-day';
                dayElement.textContent = i;
                dayElement.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                
                // Check if this is today
                const currentDate = new Date(year, month, i);
                if (currentDate.getTime() === today.getTime()) {
                    dayElement.classList.add('vpa-today');
                }
                
                // Check if date is in the past
                if (currentDate < today) {
                    dayElement.classList.add('vpa-disabled');
                }
                
                daysGrid.appendChild(dayElement);
            }
            
            // Add days from next month to fill the grid
            const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
            const nextMonthDays = totalCells - (firstDay + daysInMonth);
            
            for (let i = 1; i <= nextMonthDays; i++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'vpa-calendar-day vpa-other-month vpa-disabled';
                dayElement.textContent = i;
                daysGrid.appendChild(dayElement);
            }
            
            calendarGrid.appendChild(daysGrid);
            
            // Clear container and append new calendar
            this.container.innerHTML = '';
            this.container.appendChild(header);
            this.container.appendChild(calendarGrid);
        }

        attachEventListeners() {
            // Previous month button
            this.container.querySelector('.vpa-calendar-prev').addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.render();
                this.updateAvailableDates();
            });
            
            // Next month button
            this.container.querySelector('.vpa-calendar-next').addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.render();
                this.updateAvailableDates();
            });
            
            // Day selection
            this.container.querySelectorAll('.vpa-calendar-day:not(.vpa-disabled):not(.vpa-other-month)').forEach(day => {
                day.addEventListener('click', () => {
                    if (day.classList.contains('vpa-available')) {
                        this.selectDate(day.dataset.date);
                    }
                });
            });
        }

        selectDate(dateString) {
            // Convert string to Date object
            const [year, month, day] = dateString.split('-').map(Number);
            this.selectedDate = new Date(year, month - 1, day);
            
            // Update UI
            this.container.querySelectorAll('.vpa-calendar-day').forEach(day => {
                day.classList.remove('vpa-selected');
                if (day.dataset.date === dateString) {
                    day.classList.add('vpa-selected');
                }
            });
            
            // Call callback if provided
            if (this.options.onDateSelect) {
                this.options.onDateSelect(this.selectedDate);
            }
        }

        loadAvailableDates(serviceId, professionalId) {
            // Show loading state
            this.container.querySelectorAll('.vpa-calendar-day:not(.vpa-disabled):not(.vpa-other-month)').forEach(day => {
                day.classList.add('vpa-loading');
            });
            
            // Get current month and year
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth() + 1;
            
            // Fetch available dates
            const vitaproAppointments = window.vitaproAppointments; // Declare the variable here
            fetch(vitaproAppointments.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'vitapro_get_available_dates',
                    service_id: serviceId,
                    professional_id: professionalId,
                    year: year,
                    month: month,
                    nonce: this.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.availableDates = data.data.available_dates || [];
                    this.updateAvailableDates();
                } else {
                    console.error('Error loading available dates:', data.data.message);
                }
            })
            .catch(error => {
                console.error('Error loading available dates:', error);
            });
        }

        updateAvailableDates() {
            // Reset all days
            this.container.querySelectorAll('.vpa-calendar-day').forEach(day => {
                day.classList.remove('vpa-available', 'vpa-loading');
            });
            
            // Mark available dates
            this.availableDates.forEach(dateString => {
                const dayElement = this.container.querySelector(`.vpa-calendar-day[data-date="${dateString}"]`);
                if (dayElement) {
                    dayElement.classList.add('vpa-available');
                }
            });
        }

        reset() {
            this.selectedDate = null;
            this.currentDate = new Date();
            this.render();
        }
    }

    // Initialize booking forms when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize booking forms
        document.querySelectorAll('.vitapro-booking-form').forEach(form => {
            new VitaProBookingForm(form);
        });
        
        // Initialize service list functionality
        document.querySelectorAll('.vitapro-service-list-wrapper').forEach(serviceList => {
            serviceList.querySelectorAll('.vpa-book-service-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    const serviceId = this.dataset.serviceId;
                    const bookingFormId = this.dataset.bookingFormId;
                    
                    if (bookingFormId) {
                        const bookingForm = document.getElementById(bookingFormId);
                        if (bookingForm) {
                            e.preventDefault();
                            
                            // Select the service in the form
                            const serviceOption = bookingForm.querySelector(`.vpa-service-option[value="${serviceId}"]`);
                            if (serviceOption) {
                                serviceOption.click();
                                
                                // Scroll to the form
                                bookingForm.scrollIntoView({ behavior: 'smooth' });
                                
                                // Proceed to next step
                                const nextButton = bookingForm.querySelector('.vpa-step[data-step="1"] .vpa-next-step');
                                if (nextButton) {
                                    nextButton.click();
                                }
                            }
                        }
                    }
                });
            });
        });
        
        // Initialize professional list functionality
        document.querySelectorAll('.vitapro-professional-list-wrapper').forEach(professionalList => {
            professionalList.querySelectorAll('.vpa-book-professional-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    const professionalId = this.dataset.professionalId;
                    const bookingFormId = this.dataset.bookingFormId;
                    
                    if (bookingFormId) {
                        const bookingForm = document.getElementById(bookingFormId);
                        if (bookingForm) {
                            e.preventDefault();
                            
                            // Store the professional ID to be selected after service selection
                            bookingForm.dataset.preselectedProfessional = professionalId;
                            
                            // Scroll to the form
                            bookingForm.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            });
        });
        
        // Initialize appointment cancellation functionality
        document.querySelectorAll('.vpa-cancel-button').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm(window.vitaproAppointments.i18n.confirmCancellation)) {
                    e.preventDefault();
                }
            });
        });
    });
})();