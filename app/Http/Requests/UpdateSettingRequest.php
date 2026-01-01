<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'company_name' => $this->companyName,
            'company_email' => $this->companyEmail,
            'company_phone' => $this->companyPhone,
            'company_address' => $this->companyAddress,
            'company_website' => $this->companyWebsite,
            'logo_path' => $this->logoPath,
            'favicon_path' => $this->faviconPath,
            'tax_id' => $this->taxId,
            'registration_number' => $this->registrationNumber,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'invoice_prefix' => $this->invoicePrefix,
            'default_language' => $this->defaultLanguage,
            'footer_note' => $this->footerNote,
            'enable_email_notifications' => filter_var($this->enableEmailNotifications, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'enable_sms_notifications' => filter_var($this->enableSmsNotifications, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'support_email' => $this->supportEmail,
            'support_phone' => $this->supportPhone,
        ]);
    }
    public function authorize()
    {
        return auth()->user()?->role?->name === 'admin';
    }

    public function rules()
    {
        return [
            'company_name' => 'sometimes|required|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'company_website' => 'nullable|url',
            'logo_path' => 'nullable|string|max:255',
            'favicon_path' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'invoice_prefix' => 'nullable|string|max:20',
            'default_language' => 'nullable|string|max:10',
            'footer_note' => 'nullable|string',
            'enable_email_notifications' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string|max:50',
        ];
    }
}