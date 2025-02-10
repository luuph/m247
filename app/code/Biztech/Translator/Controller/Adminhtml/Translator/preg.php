<?php
$contentData='<div data-content-type="html" data-appearance="default" data-element="main">&lt;form action="#" method="post"&gt;
  &lt;fieldset&gt;
    &lt;label for="name"&gt;Name:&lt;/label&gt;
    &lt;input type="text" id="name" placeholder="Enter your 
full name" /&gt;

    &lt;label for="email"&gt;Email:&lt;/label&gt;
    &lt;input type="email" id="email" placeholder="Enter 
your email address" /&gt;

    &lt;label for="message"&gt;Message:&lt;/label&gt;
    &lt;textarea id="message" placeholder="What s on your 
mind?"&gt;&lt;/textarea&gt;

    &lt;input type="submit" value="Send message" /&gt;

  &lt;/fieldset&gt;
&lt;/form&gt;
</div>';

$skip_counce2 = preg_match_all('/&lt;([^&gt;]+)&gt;/i', $contentData, $skip);

print_r($skip);
