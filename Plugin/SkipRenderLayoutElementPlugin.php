<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */
declare(strict_types=1);

namespace Hryvinskyi\EsiPageLayout\Plugin;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Layout;
use Magento\PageCache\Model\Config;

/**
 * Skip rendering blocks with TTL when Varnish is enabled.
 *
 * When full page cache with Varnish is active and the page is cacheable,
 * blocks that have a TTL will be served via ESI includes instead.
 * This plugin returns empty string for those blocks to avoid rendering
 * them inline on the cacheable page.
 */
class SkipRenderLayoutElementPlugin
{
    /**
     * @param Config $config
     */
    public function __construct(private readonly Config $config)
    {
    }

    /**
     * Skip rendering blocks with TTL when Varnish is enabled.
     *
     * @param Layout $layout
     * @param callable $proceed
     * @param string $name
     * @return string|bool|null
     */
    public function aroundRenderNonCachedElement(
        Layout $layout,
        callable $proceed,
        $name
    ) {
        if (!$this->shouldSkipRendering($layout)) {
            return $proceed($name);
        }

        $block = $layout->getBlock($name);
        if ($block instanceof AbstractBlock && $block->getTtl() !== null) {
            return '';
        }

        return $proceed($name);
    }

    /**
     * Check if block rendering should be skipped.
     *
     * @param Layout $layout
     * @return bool
     */
    private function shouldSkipRendering(Layout $layout): bool
    {
        return $this->config->isEnabled()
            && $this->config->getType() === Config::VARNISH
            && $layout->isCacheable();
    }
}
