<?php

namespace App\Services\V1;

use App\Repositories\V1\AttachmentRepositoryV1;

class AttachmentServiceV1
{
    public function __construct(private readonly AttachmentRepositoryV1 $attachmentRepositoryV1
    ) {}


}
