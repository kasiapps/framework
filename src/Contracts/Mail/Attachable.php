<?php

namespace Kasi\Contracts\Mail;

interface Attachable
{
    /**
     * Get an attachment instance for this entity.
     *
     * @return \Kasi\Mail\Attachment
     */
    public function toMailAttachment();
}
