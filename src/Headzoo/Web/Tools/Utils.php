<?php
namespace Headzoo\Web\Tools;
use Headzoo\Utilities\Arrays;
use InvalidArgumentException;

/**
 * Contains static methods used by classes in this namespace.
 */
class Utils
{
    /**
     * List of header fields which have non-standard formatting
     * @var array
     */
    private static $headerSpecialCases = [
        "Content-MD5",
        "TE",
        "DNT",
        "ATT-DeviceId",
        "X-ATT-DeviceId",
        "ETag",
        "P3P",
        "WWW-Authenticate",
        "XSS-Protection",
        "WebKit-CSP",
        "X-WebKit-CSP",
        "UA-Compatible",
        "X-UA-Compatible"
    ];
    
    /**
     * Normalizes a header name
     * 
     * Changes the value of $headerName to the format "Camel-Case-String" from any other format. For example the
     * string "CONTENT_TYPE" becomes "Content-Type". Special cases are handled for header fields which typically
     * use non-standard formatting. For example the headers "XSS-Protection" and "ETag". The value "xss-protection"
     * is normalized to "XSS-Protection" rather than "Xss-Protection".
     * 
     * The experimental header prefix "X-" is removed when $stripX is true. Prefixes are never added to the field
     * names.
     * 
     * When $headerName is an array, each value in the array will be normalized.
     * 
     * Examples:
     * ```php
     * // The output from these method calls will be exactly "Content-Type".
     * echo Utils::normalizeHeaderName("content-type");
     * echo Utils::normalizeHeaderName("content_type");
     * echo Utils::normalizeHeaderName("CONTENT-TYPE");
     * echo Utils::normalizeHeaderName("CONTENT_TYPE");
     * echo Utils::normalizeHeaderName("content type");
     * echo Utils::normalizeHeaderName(" content-type ");
     * echo Utils::normalizeHeaderName("Content-Type: ");
     * 
     * // The output from these method calls will be exactly "XSS-Protection".
     * echo Utils::normalizeHeaderName("xss-protection");
     * echo Utils::normalizeHeaderName("XSS_PROTECTION");
     * echo Utils::normalizeHeaderName("X-XSS-Protection", true);
     * 
     * // This method call throws an exception because it's a full header, and not just a name.
     * echo Utils::normalizeHeaderName("content-type: text/html");
     * 
     * // This method call throws an exception because it contains special characters.
     * echo Utils::normalizeHeaderName("content*type");
     * 
     * // Using an array of header names.
     * $names = ["CONTENT_TYPE", "XSS_PROTECTION"];
     * $normal = Utils::normalizeHeaderName($names);
     * // Produces: ["Content-Type", "XSS-Protection"]
     * ```
     * 
     * @param  string|array $headerName The header name or array of header names
     * @param  bool         $stripX     Should X- prefixes be stripped?
     * @return string
     * @throws InvalidArgumentException When the header name cannot be normalized
     */
    public static function normalizeHeaderName($headerName, $stripX = false)
    {
        if (is_array($headerName)) {
            array_walk($headerName, function(&$name) use($stripX) {
                $name = self::normalizeHeaderName($name, $stripX);        
            });
        } else {
            $headerName = trim((string)$headerName, " \t:");
            if (count(explode(":", $headerName)) > 1 || preg_match("/[^\\w\\s-]/", $headerName)) {
                throw new InvalidArgumentException(
                    "String '{$headerName}' cannot be normalized because of bad formatting."
                );
            }
            
            $headerName = str_replace(["-", "_"], " ", $headerName);
            $headerName = ucwords(strtolower($headerName));
            $headerName = str_replace(" ", "-", $headerName);
            if ($stripX && substr($headerName, 0, 2) === "X-") {
                $headerName = substr($headerName, 2);
            }
            
            $index = Arrays::findString(self::$headerSpecialCases, $headerName);
            if (false !== $index) {
                $headerName = self::$headerSpecialCases[$index];
            }
        }
        
        return $headerName;
    }
} 