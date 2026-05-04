<div class="loan-product-modal-form">
    @include('backend.admin.loan_product.partials.form', [
        'action' => route('loan_products.update', $id),
        'method' => 'PATCH',
        'formClass' => 'ajax-screen-submit',
        'submitLabel' => _lang('Update Changes'),
    ])
</div>
