<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Feature;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Map feature titles to Bootstrap icons
        $iconMap = [
            // Specific matches first (case-insensitive)
            'multi-branch' => 'bi-building',
            'branch management' => 'bi-building',
            'member account' => 'bi-wallet2',
            'account management' => 'bi-wallet2',
            'loan management' => 'bi-cash-coin',
            'deposits' => 'bi-arrow-down-circle',
            'withdrawals' => 'bi-arrow-up-circle',
            'deposits & withdrawals' => 'bi-arrow-repeat',
            'deposits and withdrawals' => 'bi-arrow-repeat',
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
        
        // Fallback icons array
        $fallbackIcons = [
            'bi-building',
            'bi-wallet2',
            'bi-cash-coin',
            'bi-arrow-repeat',
            'bi-credit-card',
            'bi-graph-up',
            'bi-cash-stack',
            'bi-shield-check',
            'bi-person-circle',
        ];
        
        // Get all features
        $features = Feature::with('translation')->get();
        
        foreach ($features as $index => $feature) {
            $iconHtml = trim($feature->icon ?? '');
            
            // Check if icon is numeric or contains numbers
            $isNumeric = is_numeric($iconHtml) || preg_match('/^[0-9]+$/', $iconHtml);
            $hasNumberInHtml = preg_match('/>([0-9]+)</', $iconHtml) || preg_match('/^[0-9]+$/', strip_tags($iconHtml));
            // Check for number-based Bootstrap icons like bi-1-circle, bi-2-circle, etc.
            $hasNumberIcon = preg_match('/bi-[0-9]+(-circle|-square|-fill)?/i', $iconHtml);
            $hasIconTag = str_contains($iconHtml, '<i') && (str_contains($iconHtml, 'bi-') || str_contains($iconHtml, 'fa-'));
            
            // If icon is a number, contains numbers, has number-based icon, or doesn't have proper icon HTML, replace it
            if ($isNumeric || $hasNumberInHtml || $hasNumberIcon || empty($iconHtml) || !$hasIconTag) {
                $title = strtolower($feature->translation->title ?? '');
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
                    $selectedIcon = $fallbackIcons[$index % count($fallbackIcons)];
                }
                
                // Update the icon field with Bootstrap icon HTML
                $newIconHtml = '<i class="bi ' . $selectedIcon . '"></i>';
                
                DB::table('features')
                    ->where('id', $feature->id)
                    ->update(['icon' => $newIconHtml]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration updates data, so we can't easily reverse it
        // If needed, you would need to restore from backup
    }
};
