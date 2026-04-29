<div class="member-documents-modal">
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary btn-sm ajax-modal-2" data-title="{{ _lang('Add New Document') }}" href="{{ route('member_documents.create', $id) }}?context=list">
            <i class="ti-plus mr-1"></i>{{ _lang('Add New') }}
        </a>
    </div>

    <div class="table-responsive">
        <table id="member_documents_modal_table" class="table table-bordered table-striped dashboard-table-compact mb-0">
            <thead>
                <tr>
                    <th>{{ _lang('Member') }}</th>
                    <th>{{ _lang('Document Name') }}</th>
                    <th>{{ _lang('Document') }}</th>
                    <th class="text-center">{{ _lang('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($memberdocuments as $memberdocument)
                    @include('backend.admin.member_documents.modal.row', ['memberdocument' => $memberdocument, 'context' => 'list'])
                @empty
                    <tr><td colspan="4" class="text-center text-muted">{{ _lang('No member documents found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
