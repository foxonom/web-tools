<?php
namespace Headzoo\Web\Tools;
use Headzoo\Core\Validator;
use Headzoo\Core\ValidatorInterface;

/**
 * Represents an http request or response.
 */
abstract class AbstractHttp
{
    /**
     * The request/response values
     * @var array
     */
    protected $values = [];

    /**
     * List of required request/response values
     * @var array
     */
    protected $required = [];

    /**
     * Used to validate values
     * @var ValidatorInterface
     */
    protected $validator;
    
    /**
     * Constructor
     *
     * @param array              $values    The request/response values
     * @param ValidatorInterface $validator Object used to validate values
     */
    public function __construct(array $values, ValidatorInterface $validator = null)
    {
        $this->setValues($values);
        if (null !== $validator) {
            $this->setValidator($validator);
        }
    }

    /**
     * Returns the object which will be used to validate values
     * 
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new Validator();
        }
        return $this->validator;
    }

    /**
     * Sets the object which will be used to validate values
     * 
     * @param  ValidatorInterface $validator The validator object
     * @return $this
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Returns the request/response values as an array
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Sets the request/response values
     * 
     * @param  array $values The request/response values
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->getValidator()->validateRequired($values, $this->required);
        $this->values = array_merge($this->values, $values);
        
        return $this;
    }
} 