/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',

], function ($) {
    'use strict';

    $.widget('mageplaza.mpAffiliateCopyAction', {
            options: {
                text:'',
                copyBtn: ''
            },
            _create:  function () {
                var text   = this.options.text,
                    copyBtn       = this.options.copyBtn,
                    self      = this;

                $(copyBtn).on('click', async function () {
                    try {
                        // await navigator.clipboard.writeText(text);
                        //copy text
                        var textarea = document.createElement('textarea');
                        textarea.value = text;
                        textarea.style.position = 'absolute';
                        textarea.style.left = '-9999px';
                        document.body.appendChild(textarea);
                        textarea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        //change icon
                        $( this ).html(` <path d="M12.0909 10.0918H19.5909V19.6373H12.0909V10.0918Z" fill="#333333"/>
                    <path d="M17.5455 6H9.36364C8.61364 6 8 6.61364 8 7.36364V16.9091H9.36364V7.36364H17.5455V6ZM19.5909 8.72727H12.0909C11.3409 8.72727 10.7273 9.34091 10.7273 10.0909V19.6364C10.7273 20.3864 11.3409 21 12.0909 21H19.5909C20.3409 21 20.9545 20.3864 20.9545 19.6364V10.0909C20.9545 9.34091 20.3409 8.72727 19.5909 8.72727ZM19.5909 19.6364H12.0909V10.0909H19.5909V19.6364Z" fill="#333333"/>`);
                        $( this ).parent().parent().find('.copied_text').attr('class', 'copied_text d-block');
                        setTimeout(() => {
                            $( this ).parent().parent().find('.copied_text').attr('class', 'copied_text d-none');
                            $( this ).html(`<path d="M17.5455 6H9.36364C8.61364 6 8 6.61364 8 7.36364V16.9091H9.36364V7.36364H17.5455V6ZM19.5909 8.72727H12.0909C11.3409 8.72727 10.7273 9.34091 10.7273 10.0909V19.6364C10.7273 20.3864 11.3409 21 12.0909 21H19.5909C20.3409 21 20.9545 20.3864 20.9545 19.6364V10.0909C20.9545 9.34091 20.3409 8.72727 19.5909 8.72727ZM19.5909 19.6364H12.0909V10.0909H19.5909V19.6364Z" fill="#AEAEAE"/>`);
                        }, 1000)
                    } catch (err) {
                        console.error('Failed to copy: ', err);
                    }
                });
            },

        }
    );

    return $.mageplaza.mpAffiliateCopyAction;
});
