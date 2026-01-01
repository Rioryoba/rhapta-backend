<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'companyName' => $this->company_name,
            'companyEmail' => $this->company_email,
            'companyPhone' => $this->company_phone,
            'companyAddress' => $this->company_address,
            'companyWebsite' => $this->company_website,
            'logoPath' => $this->logo_path,
            'faviconPath' => $this->favicon_path,
            'taxId' => $this->tax_id,
            'registrationNumber' => $this->registration_number,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'invoicePrefix' => $this->invoice_prefix,
            'defaultLanguage' => $this->default_language,
            'footerNote' => $this->footer_note,
            'enableEmailNotifications' => $this->enable_email_notifications,
            'enableSmsNotifications' => $this->enable_sms_notifications,
            'supportEmail' => $this->support_email,
            'supportPhone' => $this->support_phone,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
