<?php declare(strict_types=1);

namespace Elgentos\Lightspeed\Block;

use Magento\Framework\View\Element\Template;

class ItemsWithPattern extends Template
{

    protected $_template = 'Elgentos_Lightspeed::items.phtml';

    /**
     * @var array
     */
    private $items;
    /**
     * @var string
     */
    private $pattern;

    /**
     * ItemsWithPattern constructor.
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pattern = $data['pattern'] ?? '%s';
    }

    /**
     * Add item
     *
     * @param string $value
     */
    public function addItem(string $value): void
    {
        $this->items[] = trim($value);
    }

    /**
     * Add multiple items
     *
     * @param array $items
     */
    public function addItems(array $items): void
    {
        array_walk($items, [$this, 'addItem']);
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function hasItems(): bool
    {
        return !empty($this->items);
    }

    /**
     * Remove item by value
     *
     * @param string $value
     */
    public function removeItem(string $value): void
    {
        $this->items = array_diff([$value], $this->items);
    }

    /**
     * Set pattern for rendering
     *
     * @param string $pattern
     */
    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    /**
     * Render items with pattern
     *
     * @param string $pattern
     * @return string
     */
    public function render(string $pattern = null): string
    {
        $pattern = $pattern ?? $this->pattern;

        return implode('', array_map(function(string $value) use ($pattern) {
            return sprintf($pattern, $value);
        }, $this->items));
    }

    /**
     * Only return if items are found
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (! $this->hasItems()) {
            return '';
        }

        return parent::_toHtml();
    }

}
