<?php
namespace Headzoo\Web\Tools\Builders;
use Headzoo\Web\Tools\Utils;

/**
 * Builds raw http headers.
 * 
 * Used to convert an array of header values into a string of raw http headers.
 */
class Headers
    implements HeadersInterface
{
    /**
     * Whether the experimental "X-" prefix should be removed from header names
     * @var bool
     */
    private $stripX;

    /**
     * Constructor
     * 
     * @param bool $stripX Whether to remove the "X-" prefix from header names
     */
    public function __construct($stripX = self::DEFAULT_STRIP_X)
    {
        $this->setStripX($stripX);
    }

    /**
     * Returns whether the experimental "X-" prefix will be removed from header names
     * 
     * @return bool
     */
    public function getStripX()
    {
        return $this->stripX;
    }

    /**
     * Sets whether the experimental "X-" prefix should be removed from header names
     * 
     * @param  bool $stripX True to remove the prefix, false to leave it alone
     * @return $this
     */
    public function setStripX($stripX)
    {
        $this->stripX = $stripX;
        return $this;
    }
    
    /**
     * {@inheritDoc}
     */
    public function build(array $headers)
    {
        if (count($headers) > self::MAX_HEADERS) {
            throw new Exceptions\BuildException(
                sprintf(
                    "Number of header fields exceeds the %d max number.",
                    self::MAX_HEADERS
                )
            );
        }
        $headers = self::normalize($headers);
        
        return join(self::NEWLINE, $headers) . self::NEWLINE;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize(array $headers)
    {
        $raw = [];
        foreach($headers as $name => $value) {
            if (is_int($name)) {
                list($name, $value) = explode(":", $value, 2);
            }

            $name  = Utils::normalizeHeaderName($name, $this->stripX);
            $value = trim($value);
            $raw[] = "{$name}: {$value}";
        }
        
        return $raw;
    }
} 