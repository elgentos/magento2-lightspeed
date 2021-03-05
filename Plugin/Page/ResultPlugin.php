<?php declare(strict_types=1);

namespace Elgentos\Lightspeed\Plugin\Page;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Plugin for processing relocation of javascript
 */
class ResultPlugin
{
    const MAX_ITERATIONS = 100;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ResultPlugin constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @param ResponseInterface $response
     * @return ResultInterface
     */
    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result,
        ResponseInterface $response
    )
    {
        if (PHP_SAPI === 'cli' || $this->request->isXmlHttpRequest()) {
            return $result;
        }

        $html = $response->getBody();
        $scripts = [];

        $startTag = '<script';
        $endTag = '</script>';
        $endTagLength = strlen($endTag);

        $start =  $i = 0;
        while ($i++ < self::MAX_ITERATIONS && false !== ($start = stripos($html, $startTag, $start)) && false !== ($end = stripos($html, $endTag, $start))) {
            $len = $end + $endTagLength - $start;
            $script = substr($html, $start, $len);

            $html = str_replace($script, '', $html);
            $scripts[] = $this->minifyScripts($script);
        }

        if (empty($scripts)) {
            return $result;
        }

        $scripts = implode('', $scripts);

        $end = stripos($html, '</body>');
        if ($end !== false) {
            $html = substr($html, 0, $end) . $scripts . substr($html, $end);
        } else {
            $html .= $scripts;
        }

        $response->setBody($html);

        return $result;
    }

    /** JavaScript (JS) Minifier
	* Credits: https://gist.github.com/Rodrigo54/93169db48194d470188f, https://github.com/mecha-cms/extend.minify
	*/ 
	private function minifyScripts($input):string
	{
		if(trim($input) === "")
			return $input;

		return preg_replace(
			array(
				// Remove comment(s)
				'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
				// Remove white-space(s) outside the string and regex
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
				// Remove the last semicolon
				'#;+\}#',
				// Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
				'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
				// --ibid. From `foo['bar']` to `foo.bar`
				'#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
			),
			array(
				'$1',
				'$1$2',
				'}',
				'$1$3',
				'$1.$3'
			),
		$input);
	}
}
