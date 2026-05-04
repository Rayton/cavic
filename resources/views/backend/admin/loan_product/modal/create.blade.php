<div class="loan-product-modal-form">
    @include('backend.admin.loan_product.partials.form', [
        'action' => route('loan_products.store'),
        'formClass' => 'ajax-screen-submit',
        'submitLabel' => _lang('Save Changes'),
    ])
</div>
