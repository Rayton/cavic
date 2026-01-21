<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\LeaderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuarantorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\LoanPaymentController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DepositMethodController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DepositRequestController;
use App\Http\Controllers\Install\UpdateController;
use App\Http\Controllers\LoanCollateralController;
use App\Http\Controllers\MemberDocumentController;
use App\Http\Controllers\SavingsAccountController;
use App\Http\Controllers\SavingsProductController;
use App\Http\Controllers\SuperAdmin\FaqController;
use App\Http\Controllers\TenantSettingsController;
use App\Http\Controllers\WithdrawMethodController;
use App\Http\Controllers\AutomaticMethodController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\Install\InstallController;
use App\Http\Controllers\SuperAdmin\PageController;
use App\Http\Controllers\SuperAdmin\PostController;
use App\Http\Controllers\SuperAdmin\TeamController;
use App\Http\Controllers\Website\WebsiteController;
use App\Http\Controllers\WithdrawRequestController;
use App\Http\Controllers\SuperAdmin\BackupController;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SuperAdmin\ContactController;
use App\Http\Controllers\SuperAdmin\FeatureController;
use App\Http\Controllers\SuperAdmin\PackageController;
use App\Http\Controllers\SuperAdmin\UtilityController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\SuperAdmin\LanguageController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\SuperAdmin\TestimonialController;
use App\Http\Controllers\SuperAdmin\OfflineMethodController;
use App\Http\Controllers\SuperAdmin\PaymentGatewayController;
use App\Http\Controllers\SuperAdmin\EmailSubscriberController;
use App\Http\Controllers\SuperAdmin\SubscriptionPaymentController;
use App\Http\Controllers\SuperAdmin\NotificationTemplateController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SubscriptionGateway\Mollie\ProcessController as MollieProcessController;
use App\Http\Controllers\SubscriptionGateway\PayPal\ProcessController as PayPalProcessController;
use App\Http\Controllers\SubscriptionGateway\Stripe\ProcessController as StripeProcessController;
use App\Http\Controllers\SubscriptionGateway\Offline\ProcessController as OfflineProcessController;
use App\Http\Controllers\SubscriptionGateway\Paystack\ProcessController as PaystackProcessController;
use App\Http\Controllers\SubscriptionGateway\Razorpay\ProcessController as RazorpayProcessController;
use App\Http\Controllers\SubscriptionGateway\Instamojo\ProcessController as InstamojoProcessController;
use App\Http\Controllers\SubscriptionGateway\Flutterwave\ProcessController as FlutterwaveProcessController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$ev = env('APP_INSTALLED', true) == true && get_option('email_verification', 0) == 1 ? true : false;

Route::group(['middleware' => ['install']], function () use ($ev) {

    Route::prefix('admin')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.login');
        });
        Route::get('/login', [LoginController::class, 'showAdminLoginForm'])->name('admin.login');
        Route::post('/login', [LoginController::class, 'login'])->name('admin.login');
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('admin.password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('admin.password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('admin.password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('admin.password.update');
    });

    Route::prefix('{tenant}')->middleware('tenant')->group(function () {
        Route::get('/members_signup', [RegisterController::class, 'showMembersSignupForm'])->name('tenant.members_signup');
        Route::post('/members_signup', [RegisterController::class, 'members_signup'])->name('tenant.members_signup');
        Route::get('/login', [LoginController::class, 'showTenantLoginForm'])->name('tenant.login');
        Route::post('/login', [LoginController::class, 'login'])->name('tenant.login');
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('tenant.password.request');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('tenant.password.email');
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('tenant.password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('tenant.password.update');
    });

    Auth::routes([
        'login'  => false,
        'reset'  => false,
        'verify' => $ev,
    ]);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'showTenants']);
    Route::get('/logout', [LoginController::class, 'logout']);

    Route::get('/2fa', [TwoFactorController::class, 'show'])->name('2fa')->middleware('auth');

    $initialMiddleware = ['auth', '2fa'];
    if ($ev == 1) {
        array_push($initialMiddleware, 'verified');
    }

    Route::group(['middleware' => $initialMiddleware], function () {

        /** Super Admin Only Routes **/
        Route::name('admin.')->prefix('admin')->middleware(['superadmin'])->group(function () {
            Route::get('dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard.index');

            //2FA Verification
            Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');

            //Profile Controller
            Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
            Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.update')->middleware('demo');
            Route::get('profile/change_password', [ProfileController::class, 'change_password'])->name('profile.change_password');
            Route::post('profile/update_password', [ProfileController::class, 'update_password'])->name('profile.update_password')->middleware('demo');
            Route::match(['get', 'post'], 'profile/enable_2fa', [ProfileController::class, 'enable_2fa'])->name('profile.enable_2fa');
            Route::match(['get', 'post'], 'profile/disable_2fa', [ProfileController::class, 'disable_2fa'])->name('profile.disable_2fa');
            Route::get('profile/notification_mark_as_read/{id}', [ProfileController::class, 'notification_mark_as_read'])->name('profile.notification_mark_as_read');
            Route::get('profile/show_notification/{id}', [ProfileController::class, 'show_notification'])->name('profile.show_notification');

            //Subscription Payments
            Route::get('subscription_payments/{id}/approve_payout_requests', [SubscriptionPaymentController::class, 'approve_payment_requests'])->name('subscription_payments.approve_payment_requests');
            Route::match(['get', 'post'], 'subscription_payments/{id}/reject_payout_requests', [SubscriptionPaymentController::class, 'reject_payment_requests'])->name('subscription_payments.reject_payment_requests');
            Route::get('subscription_payments/get_table_data', [SubscriptionPaymentController::class, 'get_table_data']);
            Route::resource('subscription_payments', SubscriptionPaymentController::class)->except([
                'destroy',
            ])->middleware("demo:PUT|PATCH|DELETE");

            //Tenant Controller
            Route::post('tenants/send_email', [TenantController::class, 'send_email'])->name('tenants.send_email')->middleware('demo');
            Route::get('tenants/get_table_data', [TenantController::class, 'get_table_data']);
            Route::resource('tenants', TenantController::class)->middleware("demo:PUT|PATCH|DELETE");

            Route::group(['middleware' => 'demo'], function () {

                //Language Controller
                Route::get('languages/{lang}/edit_website_language', [LanguageController::class, 'edit_website_language'])->name('languages.edit_website_language');
                Route::resource('languages', LanguageController::class);

                //Utility Controller
                Route::match(['get', 'post'], 'general_settings/{store?}', [UtilityController::class, 'settings'])->name('settings.update_settings');
                Route::post('upload_logo', [UtilityController::class, 'upload_logo'])->name('settings.upload_logo');
                Route::post('remove_cache', [UtilityController::class, 'remove_cache'])->name('settings.remove_cache');
                Route::post('send_test_email', [UtilityController::class, 'send_test_email'])->name('settings.send_test_email');

                //Data Backup
                Route::get('backups', [BackupController::class, 'index'])->name('backup.index');
                Route::get('backups/create', [BackupController::class, 'create_backup'])->name('backup.create')->middleware("demo:GET");
                Route::get('backups/restore', [BackupController::class, 'show_restore_form'])->name('backup.restore');
                Route::post('backups/restore', [BackupController::class, 'restore_backup'])->name('backup.restore');
                Route::get('backups/{file}/download', [BackupController::class, 'download'])->name('backup.download')->middleware("demo:GET");
                Route::delete('backups/{file}/destroy', [BackupController::class, 'destroy'])->name('backup.destroy');

                //Notification Template
                Route::resource('notification_templates', NotificationTemplateController::class)->only([
                    'index', 'edit', 'update', 'show',
                ])->middleware("demo");

                //Package Controller
                Route::resource('packages', PackageController::class);

                //Payment Gateways
                Route::resource('payment_gateways', PaymentGatewayController::class)->except([
                    'create', 'store', 'show', 'destroy',
                ]);

                //Offline Gateways
                Route::resource('offline_methods', OfflineMethodController::class)->except('show');

                //Page Controller
                Route::post('pages/store_default_pages/{slug?}', [PageController::class, 'store_default_pages'])->name('pages.default_pages.store');
                Route::get('pages/default_pages/{slug?}', [PageController::class, 'default_pages'])->name('pages.default_pages');
                Route::resource('pages', PageController::class)->except('show');

                //FAQ Controller
                Route::resource('faqs', FaqController::class)->except('show');

                //Features Controller
                Route::resource('features', FeatureController::class)->except('show');

                //Testimonial Controller
                Route::resource('testimonials', TestimonialController::class)->except('show');

                //Team Controller
                Route::resource('posts', PostController::class)->except('show');

                //Team Controller
                Route::resource('teams', TeamController::class)->except('show');

                //Email Subscribers
                Route::match(['get', 'post'], 'email_subscribers/send_email', [EmailSubscriberController::class, 'send_email'])->name('email_subscribers.send_email');
                Route::get('email_subscribers/export', [EmailSubscriberController::class, 'export'])->name('email_subscribers.export');
                Route::get('email_subscribers/get_table_data', [EmailSubscriberController::class, 'get_table_data']);
                Route::get('email_subscribers', [EmailSubscriberController::class, 'index'])->name('email_subscribers.index');
                Route::delete('email_subscribers/{id}/destroy', [EmailSubscriberController::class, 'destroy'])->name('email_subscribers.destroy');

                //Contact Messages
                Route::get('contact_messages/get_table_data', [ContactController::class, 'get_table_data']);
                Route::resource('contact_messages', ContactController::class)->only(['index', 'show', 'destroy']);
            });
        });

        //Tenant Dashboard
        Route::prefix('{tenant}')->middleware(['tenant', 'tenant.global'])->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

            //2FA Verification
            Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');

            //Profile Controller
            Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
            Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.update')->middleware('demo');
            Route::get('profile/change_password', [ProfileController::class, 'change_password'])->name('profile.change_password');
            Route::post('profile/update_password', [ProfileController::class, 'update_password'])->name('profile.update_password')->middleware('demo');
            Route::match(['get', 'post'], 'profile/enable_2fa', [ProfileController::class, 'enable_2fa'])->name('profile.enable_2fa');
            Route::match(['get', 'post'], 'profile/disable_2fa', [ProfileController::class, 'disable_2fa'])->name('profile.disable_2fa');
            Route::get('profile/notification_mark_as_read/{id}', [ProfileController::class, 'notification_mark_as_read'])->name('profile.notification_mark_as_read');
            Route::get('profile/show_notification/{id}', [ProfileController::class, 'show_notification'])->name('profile.show_notification');

            //Message Controllers
            Route::get('/messages/compose', [MessageController::class, 'compose'])->name('messages.compose');
            Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
            Route::get('/messages/inbox', [MessageController::class, 'inbox'])->name('messages.inbox');
            Route::get('/messages/sent', [MessageController::class, 'sentItems'])->name('messages.sent');
            Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
            Route::get('/messages/reply/{id}', [MessageController::class, 'reply'])->name('messages.reply');
            Route::post('/messages/reply/{id}', [MessageController::class, 'sendReply'])->name('messages.sendReply');
            Route::get('/messages/{id}/download_attachment', [MessageController::class, 'download_attachment'])->name('messages.download_attachment');

            //Ajax Select2 Controller
            Route::get('ajax/get_table_data', [Select2Controller::class, 'get_table_data']);

            /** Tenant Admin Only Routes **/
            Route::middleware('tenant.admin')->group(function () {
                Route::get('membership', [MembershipController::class, 'index'])->name('membership.index');

                //Branch Controller
                Route::resource('branches', BranchController::class)->middleware("demo:PUT|PATCH|DELETE");

                //Savings Products
                Route::resource('savings_products', SavingsProductController::class);

                //Transaction Category
                Route::resource('transaction_categories', TransactionCategoryController::class);

                //Loan Products
                Route::resource('loan_products', LoanProductController::class)->except('show');

                //Expense Categories
                Route::resource('expense_categories', ExpenseCategoryController::class)->except('show');

                //User Management
                Route::get('users/get_table_data', [UserController::class, 'get_table_data']);
                Route::resource('users', UserController::class)->middleware("demo:PUT|PATCH|DELETE");

                //User Roles
                Route::resource('roles', RoleController::class)->middleware("demo:PUT|PATCH|DELETE");

                //Deposit Methods
                Route::resource('deposit_methods', DepositMethodController::class)->except([
                    'show',
                ]);

                //Automatic Methods
                Route::resource('automatic_methods', AutomaticMethodController::class)->except([
                    'show', 'create', 'store', 'destroy',
                ]);

                //Withdraw Methods
                Route::resource('withdraw_methods', WithdrawMethodController::class)->except([
                    'show',
                ]);

                //Permission Controller
                Route::get('permission/access_control', [PermissionController::class, 'index'])->name('permission.index');
                Route::get('permission/access_control/{user_id?}', [PermissionController::class, 'show'])->name('permission.show');
                Route::post('permission/store', [PermissionController::class, 'store'])->name('permission.store');

                //Tenant Settings Controller
                Route::post('settings/upload_logo', [TenantSettingsController::class, 'upload_logo'])->name('settings.upload_logo');
                Route::post('settings/send_test_email', [TenantSettingsController::class, 'send_test_email'])->name('settings.send_test_email');
                Route::post('settings/store_email_settings', [TenantSettingsController::class, 'store_email_settings'])->name('settings.store_email_settings');
                Route::post('settings/store_currency_settings', [TenantSettingsController::class, 'store_currency_settings'])->name('settings.store_currency_settings');
                Route::post('settings/store_general_settings', [TenantSettingsController::class, 'store_general_settings'])->name('settings.store_general_settings');
                Route::get('settings', [TenantSettingsController::class, 'index'])->name('settings.index');

                //Currency Controller
                Route::resource('currency', CurrencyController::class);

                //Notification Template
                Route::resource('email_templates', EmailTemplateController::class)->only([
                    'index', 'edit', 'update', 'show',
                ])->middleware("demo");

                //Leaders Management
                Route::get('leaders', [LeaderController::class, 'index'])->name('leaders.index');
                Route::get('leaders/create/{position?}', [LeaderController::class, 'create'])->name('leaders.create');
                Route::post('leaders', [LeaderController::class, 'store'])->name('leaders.store');
                Route::get('leaders/{id}/edit', [LeaderController::class, 'edit'])->name('leaders.edit');
                Route::match(['put', 'patch'], 'leaders/{id}', [LeaderController::class, 'update'])->name('leaders.update');
                Route::delete('leaders/{id}', [LeaderController::class, 'destroy'])->name('leaders.destroy');

            });

            /** Tenant Role based user Routes **/
            Route::middleware('tenant.user')->group(function () {
                //Dashboard Widget
                Route::get('dashboard/total_customer_widget', [DashboardController::class, 'dashboard_widget'])->name('dashboard.total_customer_widget');
                Route::get('dashboard/deposit_requests_widget', [DashboardController::class, 'dashboard_widget'])->name('dashboard.deposit_requests_widget');
                Route::get('dashboard/withdraw_requests_widget', [DashboardController::class, 'dashboard_widget'])->name('dashboard.withdraw_requests_widget');
                Route::get('dashboard/loan_requests_widget', [DashboardController::class, 'dashboard_widget'])->name('dashboard.loan_requests_widget');
                Route::get('dashboard/expense_overview_widget', [DashboardController::class, 'dashboard_widget'])->name('dashboard.expense_overview_widget');
                Route::get('dashboard/deposit_withdraw_analytics', [DashboardController::class, 'dashboard_widget'])->name('dashboard.deposit_withdraw_analytics');
                Route::get('dashboard/recent_transaction_widget', [DashboardController::class, 'dashboard_widget'])->name('dashboard.recent_transaction_widget');
                Route::get('dashboard/due_loan_list', [DashboardController::class, 'dashboard_widget'])->name('dashboard.due_loan_list');
                Route::get('dashboard/active_loan_balances', [DashboardController::class, 'dashboard_widget'])->name('dashboard.active_loan_balances');

                //Member Controller
                Route::match(['get', 'post'], 'members/import', [MemberController::class, 'import'])->name('members.import');
                Route::match(['get', 'post'], 'members/accept_request/{id}', [MemberController::class, 'accept_request'])->name('members.accept_request');
                Route::get('members/reject_request/{id}', [MemberController::class, 'reject_request'])->name('members.reject_request');
                Route::get('members/pending_requests', [MemberController::class, 'pending_requests'])->name('members.pending_requests');
                Route::get('members/get_member_transaction_data/{member_id}', [MemberController::class, 'get_member_transaction_data']);
                Route::get('members/get_table_data', [MemberController::class, 'get_table_data']);
                Route::post('members/send_email', [MemberController::class, 'send_email'])->name('members.send_email');
                Route::post('members/send_sms', [MemberController::class, 'send_sms'])->name('members.send_sms');
                Route::resource('members', MemberController::class)->middleware("demo:PUT|PATCH|DELETE");

                //Custom Field Controller
                Route::resource('custom_fields', CustomFieldController::class)->except(['index', 'show'])->middleware("demo");
                Route::get('custom_fields/{table}', [CustomFieldController::class, 'index'])->name('custom_fields.index');

                //Members Documents
                Route::get('member_documents/{member_id}', [MemberDocumentController::class, 'index'])->name('member_documents.index');
                Route::get('member_documents/create/{member_id}', [MemberDocumentController::class, 'create'])->name('member_documents.create');
                Route::resource('member_documents', MemberDocumentController::class)->except(['index', 'create', 'show']);

                //Savings Accounts
                Route::get('savings_accounts/get_account_by_member_id/{member_id}', [SavingsAccountController::class, 'get_account_by_member_id']);
                Route::get('savings_accounts/get_table_data', [SavingsAccountController::class, 'get_table_data']);
                Route::resource('savings_accounts', SavingsAccountController::class)->middleware("demo:PUT|PATCH|DELETE");

                //Interest Controller
                Route::get('interest_calculation/get_last_posting/{account_type_id?}', [InterestController::class, 'get_last_posting'])->name('interest_calculation.get_last_posting');
                Route::match(['get', 'post'], 'interest_calculation/calculator', [InterestController::class, 'calculator'])->name('interest_calculation.calculator');
                Route::post('interest_calculation/posting', [InterestController::class, 'interest_posting'])->name('interest_calculation.interest_posting');

                //Transaction
                Route::get('transactions/get_table_data', [TransactionController::class, 'get_table_data']);
                Route::resource('transactions', TransactionController::class);

                //Get Transaction Categories
                Route::get('transaction_categories/get_category_by_type/{type}', [TransactionCategoryController::class, 'get_category_by_type']);

                //Deposit Requests
                Route::post('deposit_requests/get_table_data', [DepositRequestController::class, 'get_table_data']);
                Route::get('deposit_requests/approve/{id}', [DepositRequestController::class, 'approve'])->name('deposit_requests.approve');
                Route::get('deposit_requests/reject/{id}', [DepositRequestController::class, 'reject'])->name('deposit_requests.reject');
                Route::delete('deposit_requests/{id}', [DepositRequestController::class, 'destroy'])->name('deposit_requests.destroy');
                Route::get('deposit_requests/{id}', [DepositRequestController::class, 'show'])->name('deposit_requests.show');
                Route::get('deposit_requests', [DepositRequestController::class, 'index'])->name('deposit_requests.index');

                //Withdraw Requests
                Route::post('withdraw_requests/get_table_data', [WithdrawRequestController::class, 'get_table_data']);
                Route::get('withdraw_requests/approve/{id}', [WithdrawRequestController::class, 'approve'])->name('withdraw_requests.approve');
                Route::get('withdraw_requests/reject/{id}', [WithdrawRequestController::class, 'reject'])->name('withdraw_requests.reject');
                Route::delete('withdraw_requests/{id}', [WithdrawRequestController::class, 'destroy'])->name('withdraw_requests.destroy');
                Route::get('withdraw_requests/{id}', [WithdrawRequestController::class, 'show'])->name('withdraw_requests.show');
                Route::get('withdraw_requests', [WithdrawRequestController::class, 'index'])->name('withdraw_requests.index');

                //Expense
                Route::get('expenses/get_table_data', [ExpenseController::class, 'get_table_data']);
                Route::resource('expenses', ExpenseController::class);

                //Loan Controller
                Route::get('loans/upcoming_loan_repayments', [LoanController::class, 'upcoming_loan_repayments'])->name('loans.upcoming_loan_repayments');
                Route::post('loans/get_table_data', [LoanController::class, 'get_table_data']);
                Route::get('loans/calculator', [LoanController::class, 'calculator'])->name('loans.admin_calculator');
                Route::post('loans/calculator/calculate', [LoanController::class, 'calculate'])->name('loans.calculate');
                Route::match(['get', 'post'], 'loans/approve/{id}', [LoanController::class, 'approve'])->name('loans.approve');
                Route::get('loans/reject/{id}', [LoanController::class, 'reject'])->name('loans.reject');
                Route::get('loans/filter/{status?}', [LoanController::class, 'index'])->name('loans.filter')->where('status', '[A-Za-z]+');
                Route::resource('loans', LoanController::class);

                //Loan Collateral Controller
                Route::get('loan_collaterals/loan/{loan_id}', [LoanCollateralController::class, 'index'])->name('loan_collaterals.index');
                Route::resource('loan_collaterals', LoanCollateralController::class)->except('index');

                //Loan Guarantor Controller
                Route::resource('guarantors', GuarantorController::class)->except(['show', 'index']);

                //Loan Payment Controller
                Route::get('loan_payments/get_repayment_by_loan_id/{loan_id}', [LoanPaymentController::class, 'get_repayment_by_loan_id']);
                Route::get('loan_payments/get_table_data', [LoanPaymentController::class, 'get_table_data']);
                Route::resource('loan_payments', LoanPaymentController::class);

                //Bank Accounts
                Route::resource('bank_accounts', BankAccountController::class)->middleware("demo:PUT|PATCH|DELETE");

                //Bank Transaction
                Route::get('bank_transactions/get_table_data', [BankTransactionController::class, 'get_table_data']);
                Route::resource('bank_transactions', BankTransactionController::class)->middleware("demo:PUT|PATCH|DELETE");

                //Report Controller
                Route::match(['get', 'post'], 'reports/account_statement', [ReportController::class, 'account_statement'])->name('reports.account_statement');
                Route::match(['get', 'post'], 'reports/account_balances', [ReportController::class, 'account_balances'])->name('reports.account_balances');
                Route::match(['get', 'post'], 'reports/transactions_report', [ReportController::class, 'transactions_report'])->name('reports.transactions_report');
                Route::match(['get', 'post'], 'reports/loan_report', [ReportController::class, 'loan_report'])->name('reports.loan_report');
                Route::get('reports/loan_due_report', [ReportController::class, 'loan_due_report'])->name('reports.loan_due_report');
                Route::match(['get', 'post'], 'reports/loan_repayment_report', [ReportController::class, 'loan_repayment_report'])->name('reports.loan_repayment_report');
                Route::match(['get', 'post'], 'reports/expense_report', [ReportController::class, 'expense_report'])->name('reports.expense_report');
                Route::match(['get', 'post'], 'reports/cash_in_hand', [ReportController::class, 'cash_in_hand'])->name('reports.cash_in_hand');
                Route::match(['get', 'post'], 'reports/bank_transactions', [ReportController::class, 'bank_transactions'])->name('reports.bank_transactions');
                Route::get('reports/bank_balances', [ReportController::class, 'bank_balances'])->name('reports.bank_balances');
                Route::match(['get', 'post'], 'reports/revenue_report', [ReportController::class, 'revenue_report'])->name('reports.revenue_report');
            });

            /** Tenant Customer Routes **/
            Route::middleware('tenant.customer')->prefix('portal')->group(function () {
                //Membership Details
                Route::get('profile/membership_details', [ProfileController::class, 'membership_details'])->name('profile.membership_details');

                //Transfer Controller
                Route::match(['get', 'post'], 'transfer/own_account_transfer', [App\Http\Controllers\Customer\TransferController::class, 'own_account_transfer'])->name('transfer.own_account_transfer');
                Route::match(['get', 'post'], 'transfer/other_account_transfer', [App\Http\Controllers\Customer\TransferController::class, 'other_account_transfer'])->name('transfer.other_account_transfer');
                Route::get('transfer/{id}/transaction_details', [App\Http\Controllers\Customer\TransferController::class, 'transaction_details'])->name('trasnactions.details');
                Route::get('transfer/get_exchange_amount/{from?}/{to?}/{amount?}', [App\Http\Controllers\Customer\TransferController::class, 'get_exchange_amount'])->name('transfer.get_exchange_amount');
                Route::post('transfer/get_final_amount', [App\Http\Controllers\Customer\TransferController::class, 'get_final_amount'])->name('transfer.get_final_amount');
                Route::get('transfer/pending_requests', [App\Http\Controllers\Customer\TransferController::class, 'pending_requests'])->name('trasnactions.pending_requests');

                //Loan Controller
                Route::match(['get', 'post'], 'loans/calculator', [App\Http\Controllers\Customer\LoanController::class, 'calculator'])->name('loans.calculator');
                Route::get('loans/loan_products', [App\Http\Controllers\Customer\LoanController::class, 'loan_products'])->name('loans.loan_products');
                Route::match(['get', 'post'], 'loans/apply_loan', [App\Http\Controllers\Customer\LoanController::class, 'apply_loan'])->name('loans.apply_loan');
                Route::get('loans/loan_details/{id}', [App\Http\Controllers\Customer\LoanController::class, 'loan_details'])->name('loans.loan_details');
                Route::match(['get', 'post'], 'loans/payment/{loan_id}', [App\Http\Controllers\Customer\LoanController::class, 'loan_payment'])->name('loans.loan_payment');
                Route::get('loans/my_loans', [App\Http\Controllers\Customer\LoanController::class, 'index'])->name('loans.my_loans');

                //Loan Approval Controller
                Route::get('loan_approvals', [App\Http\Controllers\LoanApprovalController::class, 'index'])->name('loan_approvals.index');
                Route::get('loan_approvals/{id}', [App\Http\Controllers\LoanApprovalController::class, 'show'])->name('loan_approvals.show');
                Route::post('loan_approvals/{id}/approve', [App\Http\Controllers\LoanApprovalController::class, 'approve'])->name('loan_approvals.approve');
                Route::post('loan_approvals/{id}/reject', [App\Http\Controllers\LoanApprovalController::class, 'reject'])->name('loan_approvals.reject');

                //Deposit Money
                Route::match(['get', 'post'], 'deposit/manual_deposit/{id}', [App\Http\Controllers\Customer\DepositController::class, 'manual_deposit'])->name('deposit.manual_deposit');
                Route::get('deposit/offline_methods', [App\Http\Controllers\Customer\DepositController::class, 'manual_methods'])->name('deposit.manual_methods');

                //Instant Deposit
                Route::get('deposit/get_exchange_amount/{from?}/{to?}/{amount?}', [App\Http\Controllers\Customer\DepositController::class, 'get_exchange_amount'])->name('deposit.get_exchange_amount');
                Route::match(['get', 'post'], 'deposit/instant_deposit/{id}', [App\Http\Controllers\Customer\DepositController::class, 'automatic_deposit'])->name('deposit.automatic_deposit');
                Route::get('deposit/instant_methods', [App\Http\Controllers\Customer\DepositController::class, 'automatic_methods'])->name('deposit.automatic_methods');

                //Withdraw Money
                Route::match(['get', 'post'], 'withdraw/offline_withdraw/{id}/{otp?}', [App\Http\Controllers\Customer\WithdrawController::class, 'manual_withdraw'])->name('withdraw.manual_withdraw');
                Route::get('withdraw/offline_methods', [App\Http\Controllers\Customer\WithdrawController::class, 'manual_methods'])->name('withdraw.manual_methods');

                //Report Controller
                Route::match(['get', 'post'], 'reports/account_statement', [App\Http\Controllers\Customer\ReportController::class, 'account_statement'])->name('customer_reports.account_statement');
                Route::match(['get', 'post'], 'reports/transactions_report', [App\Http\Controllers\Customer\ReportController::class, 'transactions_report'])->name('customer_reports.transactions_report');
                Route::match(['get', 'post'], 'reports/account_balances', [App\Http\Controllers\Customer\ReportController::class, 'account_balances'])->name('customer_reports.account_balances');
            });

        });
    });

    Route::get('switch_language', function () {
        if (isset($_GET['language'])) {
            session(['language' => $_GET['language']]);
            return back();
        }
    })->name('switch_language');

    Route::get('switch_branch', function () {
        if (isset($_GET['branch']) && isset($_GET['branch_id'])) {
            session(['branch' => $_GET['branch'], 'branch_id' => $_GET['branch_id']]);
        } else {
            request()->session()->forget(['branch', 'branch_id']);
        }
        return back();
    })->name('switch_branch');

    Route::get('switch_tenant', function () {
        if (!auth()->check()) {
            return back();
        }

        $currentUser = auth()->user();

        if (!isset($_GET['tenant_slug'])) {
            return back();
        }

        $targetTenant = \App\Models\Tenant::where('slug', $_GET['tenant_slug'])->first();
        if (!$targetTenant) {
            return back()->with('error', _lang('Tenant not found'));
        }

        // Logout current user
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Redirect to target tenant login page with email pre-filled
        $loginUrl = url('/' . $targetTenant->slug . '/login?email=' . urlencode($currentUser->email));

        return redirect($loginUrl);
    })->name('switch_tenant');

    Route::get('tenants/check-tenant-slug/{ignoreId?}', [TenantController::class, 'checkSlug'])->name('check-slug');

    //Frontend Website
    Route::get('/about', [WebsiteController::class, 'about']);
    Route::get('/features', [WebsiteController::class, 'features']);
    Route::get('/pricing', [WebsiteController::class, 'pricing']);
    Route::get('/faq', [WebsiteController::class, 'faq']);
    Route::get('/blogs/{slug?}', [WebsiteController::class, 'blogs']);
    Route::get('/contact', [WebsiteController::class, 'contact']);
    Route::get('/privacy-policy', [WebsiteController::class, 'privacy_policy']);
    Route::get('/terms-condition', [WebsiteController::class, 'terms_condition']);
    Route::post('/send_message', [WebsiteController::class, 'send_message']);
    Route::post('/post_comment', [WebsiteController::class, 'post_comment']);
    Route::post('/email_subscription', [WebsiteController::class, 'email_subscription']);

    if (env('APP_INSTALLED', true)) {
        Route::get('/{slug?}', [WebsiteController::class, 'index']);
    } else {
        Route::get('/', function () {
            echo "Installation";
        });
    }
});

//Dashboard Widget
Route::get('dashboard/json_expense_by_category', [DashboardController::class, 'json_expense_by_category'])->middleware('auth');
Route::get('dashboard/json_deposit_withdraw_analytics/{currency_id?}', [DashboardController::class, 'json_deposit_withdraw_analytics'])->middleware('auth');

Route::get('admin/dashboard/json_package_wise_subscription', [SuperAdminDashboardController::class, 'json_package_wise_subscription'])->middleware('auth');
Route::get('admin/dashboard/json_yearly_revenue', [SuperAdminDashboardController::class, 'json_yearly_revenue'])->middleware('auth');
Route::get('admin/dashboard/json_yearly_signup', [SuperAdminDashboardController::class, 'json_yearly_signup'])->middleware('auth');

//Subscription Payment
Route::prefix('subscription_callback')->group(function () {
    Route::get('paypal', [PayPalProcessController::class, 'callback'])->name('subscription_callback.PayPal');
    Route::post('stripe', [StripeProcessController::class, 'callback'])->name('subscription_callback.Stripe');
    Route::post('razorpay', [RazorpayProcessController::class, 'callback'])->name('subscription_callback.Razorpay');
    Route::get('paystack', [PaystackProcessController::class, 'callback'])->name('subscription_callback.Paystack');
    Route::get('flutterwave', [FlutterwaveProcessController::class, 'callback'])->name('subscription_callback.Flutterwave');
    Route::get('mollie', [MollieProcessController::class, 'callback'])->name('subscription_callback.Mollie');
    Route::match(['get', 'post'], 'instamojo', [InstamojoProcessController::class, 'callback'])->name('subscription_callback.Instamojo');
    Route::post('offline_payment/{slug}', [OfflineProcessController::class, 'callback'])->name('subscription_callback.offline');
});

Route::prefix('{tenant}')->middleware(['tenant'])->group(function () {
    Route::prefix('callback')->group(function () {
        //Fiat Currency
        Route::get('paypal', [App\Http\Controllers\Gateway\PayPal\ProcessController::class, 'callback'])->name('callback.PayPal')->middleware('auth');
        Route::post('stripe', [App\Http\Controllers\Gateway\Stripe\ProcessController::class, 'callback'])->name('callback.Stripe')->middleware('auth');
        Route::post('razorpay', [App\Http\Controllers\Gateway\Razorpay\ProcessController::class, 'callback'])->name('callback.Razorpay')->middleware('auth');
        Route::get('paystack', [App\Http\Controllers\Gateway\Paystack\ProcessController::class, 'callback'])->name('callback.Paystack')->middleware('auth');
        Route::get('flutterwave', [App\Http\Controllers\Gateway\Flutterwave\ProcessController::class, 'callback'])->name('callback.Flutterwave')->middleware('auth');
        Route::get('mollie', [App\Http\Controllers\Gateway\Mollie\ProcessController::class, 'callback'])->name('callback.Mollie')->middleware('auth');
        Route::match(['get', 'post'], 'instamojo', [App\Http\Controllers\Gateway\Instamojo\ProcessController::class, 'callback'])->name('callback.Instamojo');

        //Crypto Currency
        Route::post('coinpayments', [App\Http\Controllers\Gateway\CoinPayments\ProcessController::class, 'callback'])->name('CoinPayments');
    });
});

//Membership Subscription
Route::get('membership/packages', [MembershipController::class, 'packages'])->name('membership.packages');
Route::post('membership/choose_package', [MembershipController::class, 'choose_package'])->name('membership.choose_package');
Route::get('membership/payment_gateways', [MembershipController::class, 'payment_gateways'])->name('membership.payment_gateways');
Route::get('membership/make_payment/{gateway}', [MembershipController::class, 'make_payment'])->name('membership.make_payment');

Route::prefix('login/{provider}')->group(function () {
    Route::get('/', [SocialController::class, 'redirect']);
    Route::get('/callback', [SocialController::class, 'callback']);
});

Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index']);
    Route::get('database', [InstallController::class, 'database']);
    Route::post('process_install', [InstallController::class, 'process_install']);
    Route::get('create_user', [InstallController::class, 'create_user']);
    Route::post('store_user', [InstallController::class, 'store_user']);
    Route::get('system_settings', [InstallController::class, 'system_settings']);
    Route::post('finish', [InstallController::class, 'final_touch']);
});

Route::get('system/update/{action?}', [UpdateController::class, 'index']);
Route::get('migration/update', [UpdateController::class, 'update_migration']);
