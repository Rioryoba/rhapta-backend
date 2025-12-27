<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name', 'company_email', 'company_phone', 'company_address', 'company_website',
        'logo_path', 'favicon_path', 'tax_id', 'registration_number', 'currency', 'timezone',
        'invoice_prefix', 'default_language', 'footer_note', 'enable_email_notifications',
        'enable_sms_notifications', 'support_email', 'support_phone'
    ];
}
