<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait Encryptable
{

    /**
     * Get an attribute from the model, decrypting if necessary.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->isEncryptable($key) && !is_null($value)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                // If decryption fails, return the original value
                // This handles cases where data wasn't encrypted or key changed
                return $value;
            }
        }

        return $value;
    }

    /**
     * Set a given attribute on the model, encrypting if necessary.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($this->isEncryptable($key) && !is_null($value)) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute should be encrypted.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEncryptable($key)
    {
        return in_array($key, $this->encryptable);
    }

    /**
     * Get the encrypted attributes for the model.
     *
     * @return array
     */
    public function getEncryptable()
    {
        return $this->encryptable;
    }
}