@extends('layouts.app')

@section('title', 'Email Testing - Development')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Email Testing Interface</h1>
                <p class="text-gray-600">Preview and test email templates (Development Only)</p>
            </div>

            <!-- Mail Configuration Status -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                <h2 class="text-lg font-semibold text-blue-900 mb-3">Mail Configuration</h2>
                <button id="check-config" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Check Configuration
                </button>
                <div id="config-result" class="mt-4 hidden">
                    <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"></pre>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Low Stock Alert Testing -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-orange-900 mb-4">Low Stock Alert Email</h2>
                    
                    <div class="space-y-4">
                        <button id="preview-low-stock" class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg hover:bg-orange-700 transition-colors">
                            Preview Template
                        </button>
                        
                        <div class="flex gap-2">
                            <input type="email" id="low-stock-email" placeholder="test@example.com" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <button id="send-low-stock" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                                Send Test
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Confirmation Testing -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-green-900 mb-4">Order Confirmation Email</h2>
                    
                    <div class="space-y-4">
                        <button id="preview-order-confirmation" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                            Preview Template
                        </button>
                        
                        <div class="flex gap-2">
                            <input type="email" id="order-confirmation-email" placeholder="test@example.com" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <button id="send-order-confirmation" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                Send Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Area -->
            <div id="results" class="mt-8 hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Results</h3>
                <div id="results-content" class="bg-gray-50 border rounded-lg p-4">
                    <!-- Results will be displayed here -->
                </div>
            </div>

            <!-- Email Preview Modal -->
            <div id="email-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
                        <div class="flex justify-between items-center p-4 border-b">
                            <h3 class="text-lg font-semibold">Email Preview</h3>
                            <button id="close-preview" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="email-preview-content" class="p-4 overflow-y-auto max-h-[80vh]">
                            <!-- Email preview will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultsDiv = document.getElementById('results');
    const resultsContent = document.getElementById('results-content');
    const previewModal = document.getElementById('email-preview-modal');
    const previewContent = document.getElementById('email-preview-content');

    function showResult(message, type = 'info') {
        resultsDiv.classList.remove('hidden');
        resultsContent.innerHTML = `<div class="text-${type === 'error' ? 'red' : 'green'}-700">${message}</div>`;
    }

    function showPreview(html) {
        previewContent.innerHTML = html;
        previewModal.classList.remove('hidden');
    }

    // Check mail configuration
    document.getElementById('check-config').addEventListener('click', function() {
        fetch('/dev/mail-test/config')
            .then(response => response.json())
            .then(data => {
                document.getElementById('config-result').classList.remove('hidden');
                document.getElementById('config-result').querySelector('pre').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                showResult('Error checking configuration: ' + error.message, 'error');
            });
    });

    // Preview low stock alert
    document.getElementById('preview-low-stock').addEventListener('click', function() {
        fetch('/dev/mail-test/preview/low-stock-alert')
            .then(response => response.text())
            .then(html => {
                showPreview(html);
            })
            .catch(error => {
                showResult('Error previewing email: ' + error.message, 'error');
            });
    });

    // Preview order confirmation
    document.getElementById('preview-order-confirmation').addEventListener('click', function() {
        fetch('/dev/mail-test/preview/order-confirmation')
            .then(response => response.text())
            .then(html => {
                showPreview(html);
            })
            .catch(error => {
                showResult('Error previewing email: ' + error.message, 'error');
            });
    });

    // Send test low stock alert
    document.getElementById('send-low-stock').addEventListener('click', function() {
        const email = document.getElementById('low-stock-email').value;
        if (!email) {
            showResult('Please enter an email address', 'error');
            return;
        }

        fetch('/dev/mail-test/send/low-stock-alert', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult(data.message, 'success');
            } else {
                showResult(data.error, 'error');
            }
        })
        .catch(error => {
            showResult('Error sending email: ' + error.message, 'error');
        });
    });

    // Send test order confirmation
    document.getElementById('send-order-confirmation').addEventListener('click', function() {
        const email = document.getElementById('order-confirmation-email').value;
        if (!email) {
            showResult('Please enter an email address', 'error');
            return;
        }

        fetch('/dev/mail-test/send/order-confirmation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult(data.message, 'success');
            } else {
                showResult(data.error, 'error');
            }
        })
        .catch(error => {
            showResult('Error sending email: ' + error.message, 'error');
        });
    });

    // Close preview modal
    document.getElementById('close-preview').addEventListener('click', function() {
        previewModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    previewModal.addEventListener('click', function(e) {
        if (e.target === previewModal) {
            previewModal.classList.add('hidden');
        }
    });
});
</script>
@endsection