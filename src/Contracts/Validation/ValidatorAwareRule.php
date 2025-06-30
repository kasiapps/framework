<?php

namespace Kasi\Contracts\Validation;

use Kasi\Validation\Validator;

interface ValidatorAwareRule
{
    /**
     * Set the current validator.
     *
     * @param  \Kasi\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator(Validator $validator);
}
