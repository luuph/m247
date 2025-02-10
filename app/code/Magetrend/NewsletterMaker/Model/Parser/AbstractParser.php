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

/**
 * Newsletter template parser class
 */
abstract class AbstractParser
{
    /**
     * Save block list
     *
     * @var array
     */
    private $blockList = [];

    /**
     * Parse newsletter html
     *
     * @param $html
     * @return $this
     */
    abstract public function parse($html);

    /**
     * Look up fo html and return css block
     *
     * @param $html
     * @return mixed
     */
    abstract public function parseCss($html);

    /**
     * Returns template block list
     *
     * @return array
     */
    public function getBlockList()
    {
        return $this->blockList;
    }

    /**
     * Set template block list
     *
     * @param $blockList
     * @return mixed
     */
    public function setBlockList($blockList)
    {
        return $this->blockList = $blockList;
    }

    /**
     * Returns html elements by attribute
     *
     * @param $html
     * @param $attribute
     * @return array
     */
    public function getContentByAttribute($html, $attribute)
    {
        if (strpos($html, $attribute) === false) {
            return [];
        }

        $html = explode($attribute, $html);
        $blockCount = count($html);

        for ($i = 1; $i < $blockCount; $i++) {
            $parseBlock = $html[$i];
            $blockBegin = explode('<', $html[$i-1]);
            $blockBegin = end($blockBegin);
            $tag = explode(' ', $blockBegin);
            $tag = $tag[0];

            $currentPos = 0;
            $blockLength = strlen($parseBlock);
            $openTagCount = 1;
            $closeTagCount = 0;

            $contetnInseide = '';
            while ($currentPos < $blockLength) {
                $openTagPos = strpos($parseBlock, '<'.$tag, $currentPos);
                $closeTagPos = strpos($parseBlock, '</'.$tag, $currentPos);

                if ($openTagPos !== false && $openTagPos < $closeTagPos) {
                    $openTagCount++;
                    $currentPos = $openTagPos+1;
                } else {
                    $closeTagCount++;
                    $currentPos = $closeTagPos+1;
                }

                if ($closeTagCount == $openTagCount) {
                    $contetnInseide = substr($parseBlock, 0, $currentPos-1);
                    break;
                }
            }

            $blockHtml = '<'.$blockBegin.$attribute.$contetnInseide.'</'.$tag.'>';
            $blockList[] = $blockHtml;
        }

        return $blockList;
    }
}
