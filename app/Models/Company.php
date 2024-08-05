<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['company_name'];

    public static function getCompanyNameById($id)
    {
        $company = self::find($id);
        return $company ? $company->company_name : null;
    }
}
