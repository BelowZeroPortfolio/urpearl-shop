@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Checkout</h4>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div id="checkout-form">
                        <!-- Payment Form will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading checkout form...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-md-4 mt-4 mt-md-0">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div id="order-summary">
                        <!-- Order summary will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-secondary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 small">Loading order summary...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load order summary
    fetch('{{ route("checkout.summary") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('order-summary').innerHTML = html;
    });

    // Load payment form
    fetch('{{ route("checkout.payment.form") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('checkout-form').innerHTML = html;
        initializeStripe();
    });
});

function initializeStripe() {
    // This will be initialized by the payment form when it loads
    if (typeof window.stripe !== 'undefined') {
        const stripe = Stripe('{{ config("services.stripe.key") }}');
        const elements = stripe.elements();
        
        // Custom styling can be passed to options when creating an Element.
        const style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Create an instance of the card Element.
        const card = elements.create('card', {style: style});

        // Add an instance of the card Element into the `card-element` <div>.
        const cardElement = document.getElementById('card-element');
        if (cardElement) {
            card.mount('#card-element');
        }
    }
}
</script>
@endpush

@endsection
