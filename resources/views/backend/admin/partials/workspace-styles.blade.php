<style>
.workspace-hero-card,
.workspace-page-header-card { border: 1px solid #eef1f5; border-radius: 12px; }
.workspace-hero-card .card-body,
.workspace-page-header-card .card-body { padding: 1.25rem; }
.workspace-section-card { border: 1px solid #eef1f5; border-radius: 12px; }
.workspace-section-card .card-header { background: #fff; border-bottom: 1px solid #eef1f5; }
.workspace-stat-card { border: 1px solid #eef1f5; border-radius: 12px; height: 100%; }
.workspace-stat-card .stat-label { font-size: .82rem; color: #6c757d; margin-bottom: .35rem; }
.workspace-stat-card .stat-value { font-size: 1.4rem; font-weight: 700; color: var(--cavic-primary, #1A8E8F); }
.workspace-stat-card .stat-link { font-size: .82rem; }
.workspace-nav .nav-link { border-radius: 8px; margin-right: .5rem; color: #495057; }
.workspace-nav .nav-link.active { background: var(--cavic-primary, #1A8E8F); color: #fff; }
.workspace-link-list .list-group-item { border-left: 0; border-right: 0; }
.workspace-link-list .list-group-item:first-child { border-top: 0; }
.workspace-link-list .list-group-item:last-child { border-bottom: 0; }
.workspace-pill { display: inline-block; padding: .2rem .6rem; border-radius: 999px; background: var(--cavic-primary-soft, #eef7f7); color: var(--cavic-primary, #1A8E8F); font-size: .75rem; font-weight: 600; }
.workspace-status-chip { display: inline-block; padding: .22rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 700; line-height: 1.2; }
.workspace-status-chip.pending { background: #fff3cd; color: #856404; }
.workspace-status-chip.review { background: #ffe8cc; color: #a04a00; }
.workspace-status-chip.ready { background: #d4edda; color: #155724; }
.workspace-status-chip.upcoming { background: #d1ecf1; color: #0c5460; }
.workspace-status-chip.overdue { background: #f8d7da; color: #721c24; }
.workspace-status-chip.active { background: #d4edda; color: #155724; }
.workspace-status-chip.info { background: #e2e3ff; color: #383d7c; }
.workspace-status-chip.today { background: #ffeeba; color: #7a4d00; }
.workspace-status-chip.critical { background: #f5c6cb; color: #721c24; }
.workspace-page-header-card h4 { color: #1f2d3d; }
.workspace-page-subtitle { max-width: 760px; }
.workspace-page-actions .btn { white-space: nowrap; }
.workspace-breadcrumbs { list-style: none; padding: 0; margin: 0 0 .45rem 0; display: flex; flex-wrap: wrap; align-items: center; }
.workspace-breadcrumbs li { font-size: .78rem; color: #6c757d; }
.workspace-breadcrumbs li + li::before { content: '/'; margin: 0 .5rem; color: #adb5bd; }
.workspace-breadcrumbs a { color: #6c757d; text-decoration: none; }
.workspace-breadcrumbs a:hover { color: var(--cavic-primary, #1A8E8F); }
.workspace-bucket-card { border: 1px solid #eef1f5; border-radius: 12px; height: 100%; }
.workspace-bucket-card .bucket-label { font-size: .78rem; color: #6c757d; margin-bottom: .35rem; }
.workspace-bucket-card .bucket-value { font-size: 1.3rem; font-weight: 700; color: var(--cavic-primary, #1A8E8F); }
.workspace-bucket-card .bucket-meta { font-size: .8rem; color: #6c757d; }
.workspace-exception-list { list-style: none; margin: 0; padding: 0; }
.workspace-exception-list li { display: flex; align-items: center; justify-content: space-between; padding: .65rem 0; border-bottom: 1px solid #eef1f5; }
.workspace-exception-list li:last-child { border-bottom: 0; }
.workspace-section-title { font-size: .92rem; font-weight: 600; color: #1f2d3d; margin-bottom: .75rem; }
.workspace-module-tabs { gap: .35rem; flex-wrap: wrap; }
.workspace-module-tabs .nav-link { display: inline-flex; align-items: center; gap: .45rem; }
.workspace-tab-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 .38rem; border-radius: 999px; background: var(--cavic-primary-soft, rgba(26, 142, 143, .12)); color: var(--cavic-primary, #1A8E8F); font-size: .7rem; font-weight: 700; }
.workspace-nav .nav-link.active .workspace-tab-badge { background: rgba(255,255,255,.18); color: #fff; }
.workspace-quick-actions-card .card-body { padding-top: 1rem; padding-bottom: 1rem; }
.workspace-quick-actions-list .btn { white-space: nowrap; }
.workspace-filter-card .card-body { padding-top: 1rem; padding-bottom: 1rem; }
.workspace-filter-copy { max-width: 520px; }
.workspace-filter-controls .form-control { min-height: 38px; }
.workspace-empty-state { padding: 2rem 1.25rem; text-align: center; border: 1px dashed #d9e2e8; border-radius: 14px; background: #fcfdfd; }
.workspace-empty-state-icon { width: 56px; height: 56px; margin: 0 auto 1rem; border-radius: 50%; background: var(--cavic-primary-soft, #eef7f7); color: var(--cavic-primary, #1A8E8F); display: inline-flex; align-items: center; justify-content: center; font-size: 1.35rem; }
.workspace-empty-state-title { color: #1f2d3d; font-weight: 600; }
.workspace-empty-state-text { color: #6c757d; max-width: 480px; margin-left: auto; margin-right: auto; }
.workspace-anchor-offset { scroll-margin-top: 110px; }
</style>
