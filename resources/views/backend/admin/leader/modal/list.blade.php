<style>
    .leader-management-modal .member-compact-card { overflow: visible; }
    .leader-management-modal .leader-table-wrap { overflow: visible; }
    .leader-management-modal .leader-actions-col,
    .leader-management-modal .leader-actions-cell { width: 72px; min-width: 72px; }
    .leader-management-modal .leader-actions-cell { position: relative; padding-right: .75rem; }
    .leader-management-modal .table-row-actions { display: inline-flex; justify-content: center; }
    .leader-management-modal .table-row-actions > .btn.dropdown-toggle.btn-xs { width: 34px; height: 34px; min-height: 34px; border-radius: 12px; }
    .leader-management-modal .table-row-actions .dropdown-menu { z-index: 2065; min-width: 168px; padding: .25rem; border-radius: 12px; }
    .leader-management-modal .table-row-actions .dropdown-item { min-height: 32px; padding: .35rem .55rem; gap: .45rem; border-radius: 8px; font-size: .78rem; line-height: 1.15; }
    .leader-management-modal .table-row-actions .dropdown-item i { width: 16px; text-align: center; font-size: .85rem; }
    .leader-management-modal .leader-header-actions { gap: .45rem; }

    @media (max-width: 767px) {
        .leader-management-modal .leader-table-wrap { overflow-x: auto; overflow-y: visible; }
    }
</style>

<div class="member-detail-modal leader-management-modal">
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
        <div>
            <h6 class="mb-1">{{ _lang('Leaders Management') }}</h6>
            <div class="text-muted small">{{ _lang('Manage member leadership assignments without leaving the Members workspace.') }}</div>
        </div>
        <div class="d-flex flex-wrap leader-header-actions">
            <a class="btn btn-primary btn-xs ajax-modal-2" href="{{ route('leaders.create', ['position' => 'secretary']) }}" data-title="{{ _lang('Add Secretary') }}">
                <i class="ti-plus mr-1"></i>{{ _lang('Add Secretary') }}
            </a>
            <a class="btn btn-primary btn-xs ajax-modal-2" href="{{ route('leaders.create', ['position' => 'chairman']) }}" data-title="{{ _lang('Add Chairman') }}">
                <i class="ti-plus mr-1"></i>{{ _lang('Add Chairman') }}
            </a>
        </div>
    </div>

    @php
        $leaderGroups = [
            _lang('Secretaries') => $secretaries,
            _lang('Chairmen') => $chairmen,
        ];
    @endphp

    @foreach($leaderGroups as $groupTitle => $groupLeaders)
        <div class="mb-3">
            <div class="workspace-section-title mb-2">{{ $groupTitle }}</div>
            <div class="member-compact-card">
                <div class="table-responsive leader-table-wrap">
                    <table class="table table-bordered table-striped member-compact-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ _lang('Leader') }}</th>
                                <th>{{ _lang('Member No') }}</th>
                                <th>{{ _lang('Branch') }}</th>
                                <th>{{ _lang('Status') }}</th>
                                <th class="text-center leader-actions-col">{{ _lang('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($groupLeaders as $leader)
                                <tr>
                                    <td>
                                        @if($leader->member)
                                            <a href="{{ route('members.show', $leader->member_id) }}" class="ajax-modal-2" data-title="{{ _lang('Member Details') }}" data-size="lg">
                                                {{ $leader->member->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ _lang('Not Assigned') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($leader->member)->member_no ?: _lang('N/A') }}</td>
                                    <td>{{ optional(optional($leader->member)->branch)->name ?: _lang('N/A') }}</td>
                                    <td>
                                        <span class="workspace-status-chip {{ (int) $leader->status === 1 ? 'active' : 'review' }}">
                                            {{ (int) $leader->status === 1 ? _lang('Active') : _lang('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="text-center leader-actions-cell">
                                        @include('backend.admin.partials.table-actions', [
                                            'items' => array_filter([
                                                $leader->member ? ['label' => _lang('View Member'), 'url' => route('members.show', $leader->member_id), 'icon' => 'ti-eye', 'class' => 'ajax-modal-2', 'data_title' => _lang('Member Details'), 'data_size' => 'lg'] : null,
                                                ['label' => _lang('Edit'), 'url' => route('leaders.edit', $leader->id), 'icon' => 'ti-pencil-alt', 'class' => 'ajax-modal-2', 'data_title' => _lang('Edit Leader')],
                                                ['label' => _lang('Delete'), 'url' => route('leaders.destroy', $leader->id), 'icon' => 'ti-trash', 'method' => 'delete', 'class' => 'btn-remove'],
                                            ]),
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">{{ _lang('No leaders found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
