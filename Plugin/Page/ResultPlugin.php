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
            $scripts[] = $script;
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

}
