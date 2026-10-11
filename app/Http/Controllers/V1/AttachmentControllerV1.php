<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\AttachmentServiceV1;

class AttachmentControllerV1 extends Controller
{
    public function __construct(private readonly AttachmentServiceV1 $attachmentServiceV1) {}


}
