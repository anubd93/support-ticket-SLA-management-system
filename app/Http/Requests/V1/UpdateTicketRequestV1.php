<?php

namespace App\Http\Requests\V1;

use Illuminate\Support\Arr;

class UpdateTicketRequestV1 extends StoreTicketRequestV1
{
    public function rules(): array
    {
        return Arr::except(parent::rules(), ['attachments', 'attachments.*']);
    }
}
