<?php 
namespace Src;

class Error extends Template
{
    // What kind of error occurred? Enum, General exception, etc
    public $type;
    // Description of above error type; a configuration, environment, etc issue?
    public $category;
    // The name of class where failure occurred
    public $triggeredBy;
    // The value of the obj/var that is invalid
    public $value;
    // The specific setting or condition that caused failure
    public $object = '';
    // For enums, the valid cases that are allowed. 
    public $valid_options;

    public function display()
    {
        $this->render('error/exceptions.html.twig', [ 
            'message'       => self::getMessage(),
            'object'        => $this->object,
            'category'      => $this->category,
            'triggeredBy'   => $this->triggeredBy,
            'value'         => $this->value,
            'valid_options' => $this->valid_options,
        ]);
    }

    public function getMessage()
    {
        if( $this->type == 'Enum' )
        {
            $message = <<<EOT
            The configuration file contains the following invalid setting:
            > $this->object = "$this->value"

            Open the .env file and locate the '$this->object' setting. Assign it 
            one of the following valid options: 
            EOT;

            return $message;
        }
    }
}