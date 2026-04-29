<form method="post" class="ajax-submit member-import-modal-form" autocomplete="off" action="{{ route('members.import') }}" enctype="multipart/form-data">
    @csrf

    <div class="member-import-modal">
        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Upload XLSX File') }}</label>
                    <input type="file" class="dropify" name="file" data-allowed-file-extensions="xlsx" required>
                </div>

                <div class="text-muted small">
                    {{ _lang('Import member records from the standard CAVIC spreadsheet template.') }}
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card mb-0">
                    <div class="card-header">
                        <span class="panel-title">{{ _lang('Instructions') }}</span>
                    </div>
                    <div class="card-body">
                        <ol class="pl-3 mb-3">
                            <li>{{ _lang('Only XLSX file are allowed') }}</li>
                            <li>{{ _lang('First row need to keep blank or use for column name only') }}</li>
                            <li>{{ _lang('Required field must exists otherwise entire row will be ignore') }}</li>
                            <li>{{ _lang('Email address must be unique') }}</li>
                            <li>{{ _lang('Member no must be unique') }}</li>
                            <li>{{ _lang('Branch name should be correct otherwise added to main branch') }}</li>
                        </ol>
                        <a href="{{ asset('public/import_sample/members.xlsx') }}" class="btn btn-outline-danger btn-sm">
                            <i class="ti-download mr-1"></i>{{ _lang('Download Sample File') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right border-top pt-3 mt-3">
            <button type="button" class="btn btn-outline-secondary btn-sm mr-2" data-dismiss="modal">{{ _lang('Cancel') }}</button>
            <button type="submit" class="btn btn-primary btn-sm"><i class="ti-import mr-1"></i>{{ _lang('Import Members') }}</button>
        </div>
    </div>
</form>
