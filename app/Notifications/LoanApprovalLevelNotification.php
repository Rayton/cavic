<?php

namespace App\Notifications;

use App\Channels\SmsMessage;
use App\Models\EmailTemplate;
use App\Models\Loan;
use App\Models\LoanApproval;
use App\Utilities\Overrider;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanApprovalLevelNotification extends Notification
{
    use Queueable;

    private $loan;
    private $approval;
    private $action; // 'pending', 'approved', 'rejected', 'all_approved'
    private $template;
    private $replace = [];

    /**
     * Create a new notification instance.
     */
    public function __construct(Loan $loan, LoanApproval $approval, $action = 'pending')
    {
        Overrider::load("Settings");
        $this->loan = $loan;
        $this->approval = $approval;
        $this->action = $action;

        // Determine template slug based on action
        $templateSlug = 'LOAN_APPROVAL_' . strtoupper($action);
        if ($action == 'all_approved') {
            $templateSlug = 'LOAN_ALL_APPROVALS_COMPLETE';
        }

        $this->template = EmailTemplate::where('slug', $templateSlug)
            ->where('tenant_id', request()->tenant->id ?? $loan->tenant_id)
            ->first();

        // Set replacement variables
        $this->replace['borrowerName'] = $this->loan->borrower->name;
        $this->replace['approverName'] = $this->approval->approver ? $this->approval->approver->name : _lang('Approver');
        $this->replace['loanId'] = $this->loan->loan_id;
        $this->replace['amount'] = decimalPlace($this->loan->applied_amount, currency($this->loan->currency->name));
        $this->replace['approvalLevel'] = _lang($this->approval->approval_level_name);
        $this->replace['approvalDate'] = $this->approval->approved_at ? $this->approval->approved_at->format(get_date_format()) : now()->format(get_date_format());
        $this->replace['remarks'] = $this->approval->remarks ?? '';
        $this->replace['loanLink'] = route('loan_approvals.show', ['tenant' => request()->tenant->slug ?? 'default', 'id' => $this->approval->id]);
        $this->replace['myLoansLink'] = route('loans.my_loans', request()->tenant->slug ?? 'default');
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = [];
        
        // Always send email - either from template if enabled, or use default message
        if ($this->template != null && $this->template->email_status == 1) {
            array_push($channels, 'mail');
        } else if ($this->template == null) {
            // No template found, send default email
            array_push($channels, 'mail');
        }
        
        // SMS if template exists and is enabled
        if ($this->template != null && $this->template->sms_status == 1) {
            array_push($channels, \App\Channels\SMS::class);
        }
        
        // Database notification
        if ($this->template != null && $this->template->notification_status == 1) {
            array_push($channels, 'database');
        } else {
            // Always save to database even if no template
            array_push($channels, 'database');
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $message = $this->template ? processShortCode($this->template->email_body, $this->replace) : $this->getDefaultMessage();

        return (new MailMessage)
            ->subject($this->template ? $this->template->subject : $this->getDefaultSubject())
            ->markdown('email.notification', ['message' => $message]);
    }

    /**
     * Get the sms representation of the notification.
     */
    public function toSMS($notifiable)
    {
        $message = $this->template ? processShortCode($this->template->sms_body, $this->replace) : $this->getDefaultSmsMessage();

        return (new SmsMessage())
            ->setContent($message)
            ->setRecipient($notifiable->country_code . $notifiable->mobile);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $message = $this->template ? processShortCode($this->template->notification_body, $this->replace) : $this->getDefaultMessage();
        return [
            'subject' => $this->template ? $this->template->subject : $this->getDefaultSubject(),
            'message' => $message
        ];
    }

    private function getDefaultSubject()
    {
        switch ($this->action) {
            case 'pending':
                return _lang('Loan Approval Required') . ' - ' . _lang($this->approval->approval_level_name);
            case 'approved':
                return _lang('Loan Approved at') . ' ' . _lang($this->approval->approval_level_name);
            case 'rejected':
                return _lang('Loan Rejected at') . ' ' . _lang($this->approval->approval_level_name);
            case 'all_approved':
                return _lang('All Loan Approvals Complete');
            default:
                return _lang('Loan Approval Update');
        }
    }

    private function getDefaultMessage()
    {
        $levelName = _lang($this->approval->approval_level_name);
        $loanId = $this->loan->loan_id;
        $amount = decimalPlace($this->loan->applied_amount, currency($this->loan->currency->name));

        switch ($this->action) {
            case 'pending':
                $approverName = $this->approval->approver ? $this->approval->approver->name : _lang('Approver');
                return _lang('Dear') . ' ' . $approverName . ',<br><br>' .
                    _lang('A loan application requires your approval at the') . ' ' . $levelName . ' ' . _lang('level') . '.<br><br>' .
                    _lang('Borrower') . ': ' . $this->loan->borrower->name . '<br>' .
                    _lang('Loan ID') . ': ' . $loanId . '<br>' .
                    _lang('Amount') . ': ' . $amount . '<br><br>' .
                    _lang('Please review and approve the loan application') . '.<br><br>' .
                    '<a href="' . $this->replace['loanLink'] . '">' . _lang('Click here to review and approve') . '</a>';
            case 'approved':
                return _lang('Dear') . ' ' . $this->loan->borrower->name . ',<br><br>' .
                    _lang('Your loan application has been approved at the') . ' ' . $levelName . ' ' . _lang('level') . '.<br><br>' .
                    _lang('Loan ID') . ': ' . $loanId . '<br>' .
                    _lang('Amount') . ': ' . $amount;
            case 'rejected':
                return _lang('Dear') . ' ' . $this->loan->borrower->name . ',<br><br>' .
                    _lang('Your loan application has been rejected at the') . ' ' . $levelName . ' ' . _lang('level') . '.<br><br>' .
                    _lang('Loan ID') . ': ' . $loanId . '<br>' .
                    _lang('Amount') . ': ' . $amount . '<br><br>' .
                    _lang('Remarks') . ': ' . ($this->approval->remarks ?? '');
            case 'all_approved':
                return _lang('Dear') . ' ' . $this->loan->borrower->name . ',<br><br>' .
                    _lang('Congratulations! Your loan application has been approved by all required approvers and is now pending final administrative approval') . '.<br><br>' .
                    _lang('Loan ID') . ': ' . $loanId . '<br>' .
                    _lang('Amount') . ': ' . $amount;
            default:
                return _lang('Loan approval status updated');
        }
    }

    private function getDefaultSmsMessage()
    {
        $levelName = _lang($this->approval->approval_level_name);
        $loanId = $this->loan->loan_id;

        switch ($this->action) {
            case 'pending':
                return _lang('Loan approval required') . ' - ' . $levelName . '. ' . _lang('Loan ID') . ': ' . $loanId;
            case 'approved':
                return _lang('Loan approved at') . ' ' . $levelName . '. ' . _lang('Loan ID') . ': ' . $loanId;
            case 'rejected':
                return _lang('Loan rejected at') . ' ' . $levelName . '. ' . _lang('Loan ID') . ': ' . $loanId;
            case 'all_approved':
                return _lang('All loan approvals complete') . '. ' . _lang('Loan ID') . ': ' . $loanId;
            default:
                return _lang('Loan approval update') . '. ' . _lang('Loan ID') . ': ' . $loanId;
        }
    }
}
