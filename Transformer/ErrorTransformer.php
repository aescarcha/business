<?php
namespace Aescarcha\BusinessBundle\Transformer;

use Aescarcha\BusinessBundle\Entity\Business;
use League\Fractal;

/**
 * Transforms the errors from symfony to our API format
 */
class ErrorTransformer extends Fractal\TransformerAbstract
{
    public function transform( \Symfony\Component\Validator\ConstraintViolation $error )
    {
        return [
            'error' => [
                'type' => get_class($error),
                'code' => $error->getCode(),
                'property' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
                'doc_url' => '',
            ]
        ];
    }

}

