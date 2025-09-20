/**
 * Real-time form validation utilities
 */

class FormValidator {
    constructor(form, options = {}) {
        this.form = form;
        this.options = {
            validateOnBlur: true,
            validateOnInput: false,
            debounceDelay: 300,
            showSuccessState: true,
            ...options
        };
        
        this.debounceTimers = new Map();
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        const inputs = this.form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            if (this.options.validateOnBlur) {
                input.addEventListener('blur', (e) => this.validateField(e.target));
            }
            
            if (this.options.validateOnInput) {
                input.addEventListener('input', (e) => this.debounceValidation(e.target));
            }
        });

        // Handle form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    debounceValidation(field) {
        const fieldName = field.name;
        
        if (this.debounceTimers.has(fieldName)) {
            clearTimeout(this.debounceTimers.get(fieldName));
        }
        
        const timer = setTimeout(() => {
            this.validateField(field);
            this.debounceTimers.delete(fieldName);
        }, this.options.debounceDelay);
        
        this.debounceTimers.set(fieldName, timer);
    }

    async validateField(field) {
        const fieldName = field.name;
        const fieldValue = field.value;
        
        // Clear previous validation state
        this.clearFieldValidation(field);
        
        // Skip validation for empty optional fields
        if (!fieldValue && !field.required) {
            return;
        }

        try {
            const response = await fetch('/api/validate-field', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field: fieldName,
                    value: fieldValue,
                    rules: this.getFieldRules(field)
                })
            });

            const result = await response.json();
            
            if (result.valid) {
                this.showFieldSuccess(field);
            } else {
                this.showFieldError(field, result.message);
            }
        } catch (error) {
            console.error('Validation error:', error);
        }
    }

    getFieldRules(field) {
        // Extract validation rules from data attributes or form configuration
        const rules = field.dataset.rules || '';
        return rules.split('|').filter(rule => rule.length > 0);
    }

    showFieldError(field, message) {
        field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.remove('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
        
        // Remove existing error message
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error mt-1 text-sm text-red-600 flex items-center';
        errorDiv.innerHTML = `
            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span>${message}</span>
        `;
        
        field.parentNode.appendChild(errorDiv);
    }

    showFieldSuccess(field) {
        if (!this.options.showSuccessState) return;
        
        field.classList.add('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
        field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        
        // Remove error message if exists
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }

    clearFieldValidation(field) {
        field.classList.remove(
            'border-red-300', 'focus:border-red-500', 'focus:ring-red-500',
            'border-green-300', 'focus:border-green-500', 'focus:ring-green-500'
        );
        
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
    }

    async handleSubmit(event) {
        event.preventDefault();
        
        // Validate all fields before submission
        const inputs = this.form.querySelectorAll('input, textarea, select');
        const validationPromises = Array.from(inputs).map(input => this.validateField(input));
        
        await Promise.all(validationPromises);
        
        // Check if there are any validation errors
        const hasErrors = this.form.querySelectorAll('.field-error').length > 0;
        
        if (!hasErrors) {
            // Submit form via AJAX or allow normal submission
            this.submitForm();
        }
    }

    async submitForm() {
        const formData = new FormData(this.form);
        const submitButton = this.form.querySelector('button[type="submit"]');
        
        // Show loading state
        if (submitButton) {
            submitButton.disabled = true;
            const originalText = submitButton.textContent;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;
        }

        try {
            const response = await fetch(this.form.action, {
                method: this.form.method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.handleSuccess(result);
            } else {
                this.handleErrors(result.errors || {});
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showGeneralError('An error occurred while processing your request.');
        } finally {
            // Restore button state
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    }

    handleSuccess(result) {
        // Show success message
        this.showSuccessMessage(result.message || 'Operation completed successfully!');
        
        // Redirect if specified
        if (result.redirect) {
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1500);
        }
        
        // Reset form if specified
        if (result.reset) {
            this.form.reset();
        }
    }

    handleErrors(errors) {
        Object.keys(errors).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                this.showFieldError(field, errors[fieldName][0]);
            }
        });
    }

    showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 z-50 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 shadow-lg';
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">${message}</span>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    showGeneralError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 z-50 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 shadow-lg';
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-medium">${message}</span>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Auto-initialize forms with validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        new FormValidator(form, {
            validateOnBlur: form.dataset.validateOnBlur !== 'false',
            validateOnInput: form.dataset.validateOnInput === 'true',
            showSuccessState: form.dataset.showSuccess !== 'false'
        });
    });
});

// Export for manual initialization
window.FormValidator = FormValidator;