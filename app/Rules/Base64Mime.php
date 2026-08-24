<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates the JSON envelope the SPA posts for logo, avatar and receipt
 * uploads: an object carrying a file `name` plus a `data:` URI whose payload
 * is base64.
 *
 * Two checks run in sequence. First the declared side of the envelope (the
 * filename extension and the shape of the data URI); then the bytes are
 * decoded and sniffed. The sniff is deliberately allowed to clear a failure
 * raised by the declared side, so a payload whose real type is acceptable is
 * let through even when the filename disagrees.
 */
class Base64Mime implements ValidationRule
{
    private $attribute;

    private $extensions;

    /**
     * @param  array  $extensions  File extensions considered acceptable.
     * @return void
     */
    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->attribute = $attribute;
        $failed = false;

        try {
            $envelope = json_decode(trim($value));
            $name = ! empty($envelope->name) ? $envelope->name : '';
            $uri = ! empty($envelope->data) ? $envelope->data : '';
        } catch (\Exception $e) {
            $failed = true;
        }

        if (! in_array(pathinfo($name, PATHINFO_EXTENSION), $this->extensions)) {
            $failed = true;
        }

        if (! preg_match('/^data:\w+\/[\w\+]+;base64,[\w\+\=\/]+$/', $uri)) {
            $failed = true;
        }

        $segments = explode(',', $uri);

        if (! isset($segments[1]) || empty($segments[1])) {
            $failed = true;
        }

        try {
            $bytes = base64_decode($segments[1]);
            $handle = finfo_open();
            $sniffed = finfo_buffer($handle, $bytes, FILEINFO_EXTENSION);

            if ($sniffed === '???') {
                $failed = true;
            }

            // A sniff may answer with several equivalent extensions joined by
            // slashes, e.g. "jpeg/jpg/jpe/jfif"; any one of them matching is
            // enough to accept the upload.
            if (strpos($sniffed, '/')) {
                foreach (explode('/', $sniffed) as $candidate) {
                    if (in_array($candidate, $this->extensions)) {
                        $failed = false;
                    }
                }
            } else {
                if (in_array($sniffed, $this->extensions)) {
                    $failed = false;
                }
            }
        } catch (\Exception $e) {
            $failed = true;
        }

        if ($failed) {
            $fail('The '.$this->attribute.' must be a json with file of type: '.implode(', ', $this->extensions).' encoded in base64.');
        }
    }
}
