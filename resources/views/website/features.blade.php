@extends('website.layouts')

@section('content')
<!-- Header-->
<header class="bg-header">
    <div class="container px-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xxl-6">
                <div class="text-center my-5">
                    <h3 class="wow animate__zoomIn">{{ $page_title }}</h3>
                    <ul class="list-inline breadcrumbs text-capitalize">
                        <li class="list-inline-item"><a href="{{ url('/') }}">{{ _lang('Home') }}</a></li>
                        <li class="list-inline-item">/ &nbsp; <a href="{{ url('/features') }}">{{ _lang('Features') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Page Content-->
<section id="services">
    <div class="container my-3">
        <div class="row gx-5 justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="text-center section-header">
                    <h3 class="wow animate__zoomIn">{{ _lang('Features') }}</h3>
                    <h2 class="wow animate__fadeInUp">{{ isset($pageData->features_heading) ? $pageData->features_heading : '' }}</h2>
                    <p class="wow animate__fadeInUp">{{ isset($pageData->features_sub_heading) ? $pageData->features_sub_heading : '' }}</p>
                </div>
            </div>
        </div>
        
        <div class="row align-items-stretch">                                                     
            @foreach($features as $index => $feature)                                             
            <div class="col-lg-4 mb-5 d-flex">
                <div class="feature wow animate__zoomIn flex-fill" data-wow-delay=".2s">
                    <div class="icon text-primary fw-bold mb-4">
                        @php
                            // Check if icon is a number or empty, replace with relevant Bootstrap icon
                            $iconHtml = trim($feature->icon ?? '');
                            $title = strtolower($feature->translation->title ?? '');
                            
                            // Check if icon is numeric (just a number) or contains numbers in HTML
                            $isNumeric = is_numeric($iconHtml) || preg_match('/^[0-9]+$/', $iconHtml);
                            $hasNumberInHtml = preg_match('/>([0-9]+)</', $iconHtml) || preg_match('/^[0-9]+$/', strip_tags($iconHtml));
                            // Check for number-based Bootstrap icons like bi-1-circle, bi-2-circle, etc.
                            $hasNumberIcon = preg_match('/bi-[0-9]+(-circle|-square|-fill)?/i', $iconHtml);
                            $hasIconTag = str_contains($iconHtml, '<i') && (str_contains($iconHtml, 'bi-') || str_contains($iconHtml, 'fa-'));
                            
                            // If icon is a number, contains numbers in HTML, has number-based icon, or doesn't have proper icon HTML, replace it
                            if ($isNumeric || $hasNumberInHtml || $hasNumberIcon || empty($iconHtml) || !$hasIconTag) {
                                // Map specific feature titles to Bootstrap icons
                                $iconMap = [
                                    // Specific matches first
                                    'multi-branch' => 'bi-building',
                                    'branch management' => 'bi-building',
                                    'member account' => 'bi-wallet2',
                                    'account management' => 'bi-wallet2',
                                    'loan management' => 'bi-cash-coin',
                                    'deposits' => 'bi-arrow-down-circle',
                                    'withdrawals' => 'bi-arrow-up-circle',
                                    'deposits & withdrawals' => 'bi-arrow-repeat',
                                    'online payment' => 'bi-credit-card',
                                    'payment integration' => 'bi-credit-card-2-front',
                                    'financial reports' => 'bi-graph-up',
                                    'automated' => 'bi-graph-up-arrow',
                                    'expense' => 'bi-cash-stack',
                                    'fund management' => 'bi-bank',
                                    'security' => 'bi-shield-check',
                                    'data protection' => 'bi-shield-lock',
                                    'self-service' => 'bi-person-circle',
                                    'member portal' => 'bi-person-badge',
                                    'portal' => 'bi-window',
                                    
                                    // General keyword matches
                                    'loan' => 'bi-cash-coin',
                                    'savings' => 'bi-piggy-bank',
                                    'account' => 'bi-wallet2',
                                    'deposit' => 'bi-arrow-down-circle',
                                    'withdraw' => 'bi-arrow-up-circle',
                                    'transaction' => 'bi-arrow-left-right',
                                    'member' => 'bi-people',
                                    'customer' => 'bi-person',
                                    'report' => 'bi-graph-up',
                                    'mobile' => 'bi-phone',
                                    'online' => 'bi-globe',
                                    'payment' => 'bi-credit-card',
                                    'bank' => 'bi-bank',
                                    'money' => 'bi-currency-dollar',
                                    'manage' => 'bi-gear',
                                    'management' => 'bi-gear',
                                    'dashboard' => 'bi-speedometer2',
                                ];
                                
                                $selectedIcon = null;
                                
                                // Find matching icon based on title keywords (check longer phrases first)
                                foreach ($iconMap as $keyword => $icon) {
                                    if (str_contains($title, $keyword)) {
                                        $selectedIcon = $icon;
                                        break;
                                    }
                                }
                                
                                // If no match found, use index-based icons
                                if (!$selectedIcon) {
                                    $fallbackIcons = [
                                        'bi-building',           // Branch/Management
                                        'bi-wallet2',           // Account
                                        'bi-cash-coin',         // Loan
                                        'bi-arrow-repeat',      // Deposits/Withdrawals
                                        'bi-credit-card',       // Payment
                                        'bi-graph-up',          // Reports
                                        'bi-cash-stack',        // Expense
                                        'bi-shield-check',      // Security
                                        'bi-person-circle',     // Portal
                                    ];
                                    $selectedIcon = $fallbackIcons[$index % count($fallbackIcons)];
                                }
                                
                                $iconHtml = '<i class="bi ' . $selectedIcon . '"></i>';
                            }
                        @endphp
                        {!! xss_clean($iconHtml) !!}
                    </div>
                    @if($feature->translation)
                        <h2 class="mb-1 mb-3">{{ $feature->translation->title }}</h2>
                        <p>{{ $feature->translation->content }}</p>
                    @endif
                </div>
            </div>
            @endforeach            
        </div>
    </div>
</section>
@endsection
