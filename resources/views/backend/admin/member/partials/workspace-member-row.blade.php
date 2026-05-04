<tr>
    <td>{{ $member->name }}</td>
    <td>{{ $member->member_no }}</td>
    <td>{{ optional($member->branch)->name ?: get_tenant_option('default_branch_name', 'Main Branch') }}</td>
    <td>{{ $member->documents_count ?? 0 }}</td>
    <td><span class="workspace-status-chip active">{{ _lang('Active') }}</span></td>
    <td>
        @include('backend.admin.partials.table-actions', [
            'items' => [
                ['label' => _lang('Edit'), 'url' => route('members.edit', ['tenant' => request()->tenant->slug, 'member' => $member->id]), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal', 'data_title' => _lang('Edit Member'), 'data_fullscreen' => true],
                ['label' => _lang('View'), 'url' => route('members.show', ['tenant' => request()->tenant->slug, 'member' => $member->id]), 'icon' => 'ti-eye', 'class' => 'ajax-modal', 'data_title' => _lang('Member Details'), 'data_size' => 'lg'],
                ['label' => _lang('Documents'), 'url' => route('member_documents.index', ['tenant' => request()->tenant->slug, 'member_id' => $member->id]), 'icon' => 'ti-files', 'class' => 'ajax-modal', 'data_title' => _lang('Member Documents'), 'data_fullscreen' => true],
                ['label' => _lang('Delete'), 'url' => route('members.destroy', ['tenant' => request()->tenant->slug, 'member' => $member->id]), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove'],
            ],
        ])
    </td>
</tr>
