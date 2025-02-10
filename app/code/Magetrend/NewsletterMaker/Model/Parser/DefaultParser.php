<?php
/**
 * MB "Vienas bitas" (www.magetrend.com)
 *
 * @category  Magetrend Extensions for Magento 2
 * @package  Magetend/NewsletterMaker
 * @author   E. Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-newsletter-maker
 */

namespace Magetrend\NewsletterMaker\Model\Parser;

class DefaultParser extends \Magetrend\NewsletterMaker\Model\Parser\AbstractParser
{
    public function parse($html)
    {
        $blockList = $this->parseBlockList($html);
        $css = $this->parseCss($html);

        $this->setBlockList($blockList);
        return $this;
    }

    public function parseBlockList($html)
    {
        $blockList = $this->getContentByAttribute($html, 'data-repeatable');

        if (empty($blockList)) {
            return [];
        }

        $alreadyAdded = [];
        $filteredBlockList = [];
        foreach ($blockList as $key => $value) {
            $groupName = $this->getAttributeValue($value, 'data-repeatable');
            if (!isset($alreadyAdded[$groupName])) {
                $filteredBlockList[] = $value;
                $alreadyAdded[$groupName] = 1;
            }
        }

        return $filteredBlockList;
    }

    public function getAttributeValue($content, $attribute)
    {
        if (strpos($content, $attribute.'="') === false) {
            return '';
        }

        $content = explode($attribute.'="', $content);
        $content = explode('"', $content[1]);
        return $content[0];
    }

    public function parseCss($html)
    {
        if (strpos($html, 'data-css') === false) {
            return '';
        }

        $cssBlockList = $this->getContentByAttribute($html, 'data-css');

        if (empty($cssBlockList)) {
            return '';
        }

        $css = '';
        foreach ($cssBlockList as $cssBlock) {
            if (strpos($cssBlock, '<style') === false || strpos($cssBlock, '</style>') === false) {
                continue;
            }
            $tmpCss = explode('>', $cssBlock);
            unset($tmpCss[0]);
            $tmpCss = implode('>', $tmpCss);
            $css .= str_replace('</style>', "\n", $tmpCss);
        }

        return $css;
    }
}
