<?php

namespace Core;

class Validator
{
    protected array $errors = [];

    /**
     * @param array $data
     * @param array $rules
     */
    public function validate( array $data, array $rules ): bool
    {
        $this->errors = [];

        foreach ( $rules as $field => $ruleString )
        {
            $rulesArray = explode( '|', $ruleString );
            $value      = $data[$field] ?? null;

            foreach ( $rulesArray as $rule )
            {
                $ruleName  = $rule;
                $ruleParam = null;

                if ( str_contains( $rule, ':' ) )
                {
                    [$ruleName, $ruleParam] = explode( ':', $rule, 2 );
                }

                $methodName = 'validate' . ucfirst( $ruleName );
                if ( method_exists( $this, $methodName ) )
                {
                    // Pass the full data array to allow for rules like 'confirmed'
                    $this->$methodName( $field, $value, $ruleParam, $data );
                }
            }
        }

        return empty( $this->errors );
    }

    /**
     * @return mixed
     */
    public function getErrors(): array {
        return $this->errors;
    }

    // --- EXISTING RULES ---

    /**
     * @param string $field
     * @param $value
     */
    protected function validateRequired( string $field, $value ): void
    {
        if ( empty( $value ) || ( is_array( $value ) && empty( $value['tmp_name'] ) ) )
        {
            $this->errors[$field][] = "The {$field} field is required.";
        }
    }

    /**
     * @param string $field
     * @param $value
     */
    protected function validateEmail( string $field, $value ): void
    {
        if ( !empty( $value ) && !filter_var( $value, FILTER_VALIDATE_EMAIL ) )
        {
            $this->errors[$field][] = "The {$field} must be a valid email address.";
        }
    }

    /**
     * @param string $field
     * @param $value
     * @param string $param
     */
    protected function validateMin( string $field, $value, ?string $param ): void
    {
        if ( !empty( $value ) && strlen( trim( $value ) ) < (int) $param )
        {
            $this->errors[$field][] = "The {$field} must be at least {$param} characters.";
        }
    }

    /**
     * @param string $field
     * @param $value
     * @param string $param
     */
    protected function validateMax( string $field, $value, ?string $param ): void
    {
        if ( !empty( $value ) && strlen( trim( $value ) ) > (int) $param )
        {
            $this->errors[$field][] = "The {$field} must not exceed {$param} characters.";
        }
    }

    // --- NEW VALIDATION RULES ---

    /**
     * @param string $field
     * @param $value
     */
    protected function validateUrl( string $field, $value ): void
    {
        if ( !empty( $value ) && !filter_var( $value, FILTER_VALIDATE_URL ) )
        {
            $this->errors[$field][] = "The {$field} must be a valid URL.";
        }
    }

    /**
     * @param string $field
     * @param $value
     * @param string $param
     * @return null
     */
    protected function validateDateFormat( string $field, $value, ?string $param ): void
    {
        if ( empty( $param ) )
        {
            return;
        }

        $date = \DateTime::createFromFormat( $param, $value );
        if ( $date === false || $date->format( $param ) !== $value )
        {
            $this->errors[$field][] = "The {$field} must be a valid date with the format: {$param}.";
        }
    }

    /**
     * @param string $field
     * @param $value
     */
    protected function validateNumeric( string $field, $value ): void
    {
        if ( !empty( $value ) && !is_numeric( $value ) )
        {
            $this->errors[$field][] = "The {$field} must only contain numbers.";
        }
    }

    /**
     * @param string $field
     * @param $value
     */
    protected function validateAlpha( string $field, $value ): void
    {
        if ( !empty( $value ) && !ctype_alpha( $value ) )
        {
            $this->errors[$field][] = "The {$field} must only contain letters.";
        }
    }

    /**
     * @param string $field
     * @param $value
     */
    protected function validateAlphaNum( string $field, $value ): void
    {
        if ( !empty( $value ) && !ctype_alnum( $value ) )
        {
            $this->errors[$field][] = "The {$field} must only contain letters and numbers.";
        }
    }

    /**
     * @param string $field
     * @param $value
     * @param string $param
     * @param array $data
     */
    protected function validateConfirmed( string $field, $value, ?string $param, array $data ): void
    {
        $confirmationField = $field . '_confirmation';
        if ( $value !== ( $data[$confirmationField] ?? null ) )
        {
            $this->errors[$field][] = "The {$field} confirmation does not match.";
        }
    }

    /**
     * @param string $field
     * @param $value
     * @param string $param
     * @return null
     */
    protected function validateIn( string $field, $value, ?string $param ): void
    {
        if ( empty( $param ) )
        {
            return;
        }

        $allowedValues = explode( ',', $param );
        if ( !empty( $value ) && !in_array( $value, $allowedValues ) )
        {
            $this->errors[$field][] = "The selected {$field} is invalid. Allowed values are: " . implode( ', ', $allowedValues ) . ".";
        }
    }

    /**
     * @param string $field
     * @param $value
     * @param string $param
     * @return null
     */
    protected function validateNotIn( string $field, $value, ?string $param ): void
    {
        if ( empty( $param ) )
        {
            return;
        }

        $disallowedValues = explode( ',', $param );
        if ( !empty( $value ) && in_array( $value, $disallowedValues ) )
        {
            $this->errors[$field][] = "The value for {$field} is not allowed.";
        }
    }

    /**
     * @param string $field
     * @param $value
     */
    protected function validateImage( string $field, $value ): void
    {
        if ( !empty( $value ) && is_array( $value ) && !empty( $value['tmp_name'] ) )
        {
            if ( $value['error'] !== UPLOAD_ERR_OK || !getimagesize( $value['tmp_name'] ) )
            {
                $this->errors[$field][] = "The {$field} must be a valid image file.";
            }
        }
    }

    /**
     * @param string $field
     * @param $value
     * @param string $param
     * @return null
     */
    protected function validateMimes( string $field, $value, ?string $param ): void
    {
        if ( empty( $param ) || !is_array( $value ) || empty( $value['tmp_name'] ) )
        {
            return;
        }

        $allowedMimes = explode( ',', $param );
        $fileMimeType = mime_content_type( $value['tmp_name'] );

        $allowedMimeTypes = [];
        foreach ( $allowedMimes as $ext )
        {
            $allowedMimeTypes[] = match ( strtolower( trim( $ext ) ) )
            {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'   => 'image/png',
                'gif'   => 'image/gif',
                'webp'  => 'image/webp',
                'pdf'   => 'application/pdf',
                'doc'   => 'application/msword',
                'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                default => 'application/octet-stream'
            };
        }

        if ( !in_array( $fileMimeType, $allowedMimeTypes ) )
            {
            $this->errors[$field][] = "The file type for {$field} is invalid. Allowed types are: {$param}.";
        }
    }
}
